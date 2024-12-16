<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
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
    /**
     * Calcule les détails du produit pour le panier, y compris les taxes
     */
    private function calculateProductDetails(Product $product, int $quantity): array
    {

        $tax = $product->getTax();
        $taxRate = $tax ? $tax->getRate() : 0;

        $priceWithTax = $product->getPrice() * (1 + $taxRate / 100);

        $total = $priceWithTax * $quantity;

        $totalTaxes = ($priceWithTax - $product->getPrice()) * $quantity;

        return [
            'priceWithTax' => $priceWithTax,
            'total' => $total,
            'totalTaxes' => $totalTaxes
        ];
    }

    /**
     * Affiche le contenu du panier
     */
    #[Route('/', name: 'index')]
    public function viewCart(ProductRepository $productRepository, SessionInterface $session): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('warning', 'Vous devez vous connecter pour voir votre panier');
            return $this->redirectToRoute('app_login');
        }

        $panier = $session->get('panier', []);
        $dataProduct = [];
        $total = 0;
        $totalTaxes = 0;

        foreach ($panier as $id => $quantity) {
            $product = $productRepository->find($id);

            if (!$product) {
                continue;
            }

            // Calcul des détails du produit, incluant les taxes
            $productDetails = $this->calculateProductDetails($product, $quantity);

            // Ajout des totaux
            $total += $productDetails['total'];
            $totalTaxes += $productDetails['totalTaxes'];

            $dataProduct[] = [
                'product' => $product,
                'quantity' => $quantity,
                'priceWithTax' => $productDetails['priceWithTax'],
            ];
        }

        // Sauvegarde du total TTC dans la session
        $session->set('ttc', $total);

        return $this->render('cart/index.html.twig', [
            'products' => $dataProduct,
            'total' => $total,
            'totalTaxes' => $totalTaxes,
        ]);
    }

    /**
     * Ajoute un produit au panier
     */
    #[Route('/add/{id}', name: 'add')]
    public function add(?Product $product, SessionInterface $session): Response
    {
        if ($product === null) {
            $this->addFlash('error', 'Produit non trouvé');
            return $this->redirectToRoute('cart_index');
        }

        $id = $product->getId();
        $panier = $session->get('panier', []);

        $panier[$id] = ($panier[$id] ?? 0) + 1;

        $session->set('panier', $panier);

        return $this->redirectToRoute('cart_index');
    }

    /**
     * Supprime completement un produit du panier
     */
    #[Route('/allRemove/{id}', name: 'allRemove')]
    public function allRemove(Product $product, SessionInterface $session): Response
    {
        $id = $product->getId();
        $panier = $session->get('panier', []);

        unset($panier[$id]);

        $session->set('panier', $panier);

        return $this->redirectToRoute('cart_index');
    }

    /**
     * Retire une unité d'un produit du panier
     */
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

    /**
     * Passer une commande
     */
    #[Route('/order', name: 'order')]
    public function order(SessionInterface $session, EntityManagerInterface $entityManager): Response
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

        if (empty($paiement)) {
            $this->addFlash('warning', 'Aucun moyen de paiement sélectionné');
            return $this->redirectToRoute('cart_index');
        }

        $order = new Order();
        $order->setUser($user);
        $order->setRef('Com:' . uniqid() . mt_rand(100, 999));
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
                $this->addFlash('warning', "Le produit avec l'ID ($id) n'existe pas et a été ignoré.");
                continue;
            }

            $productDetails = $this->calculateProductDetails($product, $quantity);
            $totalAmount += $productDetails['total'];

            $orderDetail = new OrderDetails();
            $orderDetail->setOrder($order);
            $orderDetail->setProduct($product);
            $orderDetail->setQuantity($quantity);
            $orderDetail->setPrice($productDetails['priceWithTax']);

            $orderDetails[] = $orderDetail;
        }
        $order->setTotal($totalAmount);
        if (empty($orderDetails)) {
            $this->addFlash('warning', 'Aucun produit valide dans votre panier.');
            return $this->redirectToRoute('cart_index');
        }

        $entityManager->persist($order);
        foreach ($orderDetails as $orderDetail) {
            $entityManager->persist($orderDetail);
        }

        $entityManager->flush();

        $session->clear();

        $this->addFlash('success', 'Votre commande a été enregistrée avec succès');
        return $this->redirectToRoute('profile_index');
    }


    /**
     * Récapitulatif du panier
     */
    #[Route('/recap', name: 'recap')]
    public function recap(ProductRepository $productRepository, SessionInterface $session): Response
    {
        $user = $this->getUser();
        $address = $session->get('address');

        if (!$user) {
            $this->addFlash('warning', 'Vous devez vous connecter pour voir votre récapitulatif.');
            return $this->redirectToRoute('app_login');
        }

        $panier = $session->get('panier', []);
        $dataProduct = array_filter(array_map(function ($id) use ($productRepository, $panier) {
            $product = $productRepository->find($id);

            return $product ? [
                'product' => $product,
                'quantity' => $panier[$id],
                'label' => $product->getLabel(),
            ] : null;
        }, array_keys($panier)));

        return $this->render('cart/recap.html.twig', [
            'recap' => [
                'products' => $dataProduct,
                'addresses' => $address
            ],
        ]);
    }
}
