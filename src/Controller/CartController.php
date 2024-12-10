<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;

use App\Form\OrderType;
use App\Entity\OrderDetails;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function viewCart(ProductRepository $productRepository, SessionInterface $session, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('warning', 'Vous devez vous connecter pour voir votre panier');

            return $this->redirectToRoute('app_login');
        };
        $panier = $session->get('panier', []);

        $dataProduct = [];
        $total = 0;
        $totalTaxes = 0;

        $form = $this->createForm(OrderType::class);
        $form->handleRequest($request);


        $paiement = $form->get('paiement')->getData();

        $session->set('paiement', $paiement);

        foreach ($panier as $id => $quantity) {
            $product = $productRepository->find($id);

            if (!$product) {
                continue;
            }

            $tax = $product->getTax();
            $taxRate = $tax->getRate();

            $priceWithTax = $product->getPrice() * (1 + $taxRate / 100);
            $total += $priceWithTax * $quantity;
            $totalTaxes += ($priceWithTax - $product->getPrice()) * $quantity;

            $dataProduct[] = [
                'product' => $product,
                'quantity' => $quantity,
                'priceWithTax' => $priceWithTax,
                'paiement' => $paiement,



            ];
        }
        dump($dataProduct);

        return $this->render('cart/index.html.twig', [
            'products' => $dataProduct,
            'total' => $total,
            'totalTaxes' => $totalTaxes,
            'form' => $form->createView(),
            'session' => $session

        ]);
    }

    #[Route('/add/{id}', name: 'add')]
    public function add(Product $product, SessionInterface $session): Response
    {
        $id = $product->getId();
        $panier = $session->get('panier', []);

        if (!empty($panier[$product->getId()])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }


        $session->set('panier', $panier);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/allRemove/{id}', name: 'allRemove')]
    public function allRemove(Product $product, SessionInterface $session): Response
    {
        $id = $product->getId();

        $panier = $session->get('panier', []);


        unset($panier[$id]);

        $session->set('panier', $panier);

        return $this->redirectToRoute('cart_index');
    }


    #[Route('/remove/{id}', name: 'remove')]
    public function remove(Product $product, SessionInterface $session): Response
    {
        $id = $product->getId();

        $panier = $session->get('panier', []);

        if (!empty($panier[$id])) {
            if ($panier[$id] > 1) {
                $panier[$id]--;
            } else {
                unset($panier[$id]);
            }
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/order', name: 'order')]
    public function order(SessionInterface $session, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('warning', 'Vous devez vous connecter pour passer une commande');
            return $this->redirectToRoute('app_login');
        }

        $panier = $session->get('panier', []);

        if (empty($panier)) {
            $this->addFlash('warning', 'Votre panier est vide');
            return $this->redirectToRoute('cart_index');
        }

        $paiement = $session->get('paiement');

        // Création d'une nouvelle commande
        $order = new Order();
        $order->setUser($user);
        $order->setRef('Com:' . mt_rand(100000, 999999));
        $order->setPaymentMethod($paiement);
        $order->setType('commande');
        $order->setPaymentDate(new \DateTimeImmutable());
        $order->setPaymentStatus('En Cours de traitement');
        $order->setDate(new \DateTimeImmutable());
        $order->setStatus('En Attente');

        $totalAmount = 0;
        $orderDetails = [];

        foreach ($panier as $id => $quantity) {
            $product = $entityManager->getRepository(Product::class)->find($id);

            if (!$product) {
                $this->addFlash('warning', "Le produit avec l'ID $id n'existe pas et a été ignoré.");
                continue;
            }

            $tax = $product->getTax();
            $taxRate = $tax ? $tax->getRate() : 0;

            $priceWithTax = $product->getPrice() * (1 + $taxRate / 100);
            $totalAmount += $priceWithTax * $quantity;

            // Création des détails de commande pour chaque produit
            $orderDetail = new OrderDetails();
            $orderDetail->setOrder($order);
            $orderDetail->setProduct($product);
            $orderDetail->setQuantity($quantity);
            $orderDetail->setPrice($priceWithTax);

            $orderDetails[] = $orderDetail;
        }

        if (empty($orderDetails)) {
            $this->addFlash('warning', "Aucun produit valide dans votre panier.");
            return $this->redirectToRoute('cart_index');
        }

        // Définir le montant total de la commande
        $order->setTotal($totalAmount);

        // Sauvegarder la commande et ses détails
        $entityManager->persist($order);
        foreach ($orderDetails as $orderDetail) {
            $entityManager->persist($orderDetail);
        }

        $entityManager->flush();

        // Vider le panier
        $session->remove('panier');

        $this->addFlash('success', 'Votre commande a été enregistrée avec succès');
        return $this->redirectToRoute('profile_index');
    }
}
