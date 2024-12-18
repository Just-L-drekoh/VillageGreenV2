<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\OrderDetails;
use App\Repository\ProductRepository;
use App\Service\SendEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    private function calculateProductDetails(Product $product, int $quantity): array
    {
        $taxRate = $product->getTax()?->getRate() ?? 0;
        $priceWithTax = $product->getPrice() * (1 + $taxRate / 100);
        $total = $priceWithTax * $quantity;
        $totalTaxes = ($priceWithTax - $product->getPrice()) * $quantity;

        return [
            'priceWithTax' => $priceWithTax,
            'total' => $total,
            'totalTaxes' => $totalTaxes,
        ];
    }

    #[Route('/', name: 'index')]
    public function viewCart(ProductRepository $productRepository, SessionInterface $session): Response
    {
        try {
            if (!$this->getUser()) {
                $this->addFlash('warning', 'Vous devez vous connecter pour voir votre panier.');
                return $this->redirectToRoute('app_login');
            }

            $panier = $session->get('panier', []);
            $dataProduct = [];
            $total = 0;
            $totalTaxes = 0;

            foreach ($panier as $id => $quantity) {
                $product = $productRepository->find($id);
                if ($product) {
                    $productDetails = $this->calculateProductDetails($product, $quantity);
                    $total += $productDetails['total'];
                    $totalTaxes += $productDetails['totalTaxes'];

                    $dataProduct[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                        'priceWithTax' => $productDetails['priceWithTax'],
                    ];
                }
            }

            $session->set('ttc', $total);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->redirectToRoute('cart_index');
        }
        return $this->render('cart/index.html.twig', [
            'products' => $dataProduct,
            'total' => $total,
            'totalTaxes' => $totalTaxes,
        ]);
    }

    #[Route('/add/{id}', name: 'add')]
    public function add(Product $product = null, SessionInterface $session): Response
    {
        try {
            if (!$product) {
                $this->addFlash('error', 'Produit non trouvé.');
                return $this->redirectToRoute('cart_index');
            }

            $panier = $session->get('panier', []);
            $panier[$product->getId()] = ($panier[$product->getId()] ?? 0) + 1;

            $session->set('panier', $panier);

            return $this->redirectToRoute('cart_index');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->redirectToRoute('cart_index');
        }
    }

    #[Route('/allRemove/{id}', name: 'allRemove')]
    public function allRemove(Product $product, SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);
        unset($panier[$product->getId()]);
        $session->set('panier', $panier);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/remove/{id}', name: 'remove')]
    public function remove(Product $product, SessionInterface $session): Response
    {
        try {
            $panier = $session->get('panier', []);
            $id = $product->getId();

            if (!empty($panier[$id])) {
                $panier[$id]--;
                if ($panier[$id] <= 0) {
                    unset($panier[$id]);
                }
            }

            $session->set('panier', $panier);

            return $this->redirectToRoute('cart_index');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->redirectToRoute('cart_index');
        }
    }

    #[Route('/order', name: 'order')]
    public function order(SessionInterface $session, EntityManagerInterface $entityManager, SendEmailService $mail): Response
    {
        try {
            if (!$this->getUser()) {
                $this->addFlash('warning', 'Vous devez vous connecter pour passer une commande.');
                return $this->redirectToRoute('app_login');
            }

            $panier = $session->get('panier', []);
            if (empty($panier)) {
                $this->addFlash('warning', 'Votre panier est vide.');
                return $this->redirectToRoute('cart_index');
            }

            $paiement = $session->get('paiement');
            if (empty($paiement)) {
                $this->addFlash('warning', 'Aucun moyen de paiement sélectionné.');
                return $this->redirectToRoute('cart_index');
            }

            $order = (new Order())
                ->setUser($this->getUser())
                ->setRef('Com:' . uniqid() . mt_rand(100, 999))
                ->setPaymentMethod($paiement)
                ->setType('commande')
                ->setPaymentDate(new \DateTimeImmutable())
                ->setPaymentStatus('En Cours de traitement')
                ->setDate(new \DateTimeImmutable())
                ->setStatus('En Attente');

            $totalAmount = 0;
            $orderDetails = [];

            foreach ($panier as $id => $quantity) {
                $product = $entityManager->getRepository(Product::class)->find($id);
                if ($product) {
                    $productDetails = $this->calculateProductDetails($product, $quantity);
                    $totalAmount += $productDetails['total'];

                    $orderDetail = (new OrderDetails())
                        ->setOrder($order)
                        ->setProduct($product)
                        ->setQuantity($quantity)
                        ->setPrice($productDetails['priceWithTax']);

                    $orderDetails[] = $orderDetail;
                    $entityManager->persist($orderDetail);
                } else {
                    $this->addFlash('warning', "Le produit avec l'ID ($id) n'existe pas et a été ignoré.");
                }
            }

            if (empty($orderDetails)) {
                $this->addFlash('warning', 'Aucun produit valide dans votre panier.');
                return $this->redirectToRoute('cart_index');
            }

            $order->setTotal($totalAmount);
            $entityManager->persist($order);
            $entityManager->flush();

            $mail->send(
                'no-reply@village-green.fr',
                $session->get('user')->getEmail(),
                'Votre commande sur le site Village Green',
                'recap',
                [
                    'order' => $order,
                    'orderDetails' => $orderDetails,
                ]
            );

            $session->clear();
            $this->addFlash('success', 'Votre commande a été enregistrée avec succès.');

            return $this->redirectToRoute('profile_index');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->redirectToRoute('cart_index');
        }
    }


    #[Route('/ChoiceMultipleDeliveryByCart', name: 'Choice_Multiple_Delivery_By_Cart')]
    public function ChoiceMultipleDeliveryByCart(SessionInterface $session, ProductRepository $productRepository, Request $request): Response
    {
        return $this->render('delivery/Choice_multiple_delivery.html.twig', []);
    }




    #[Route('/recap', name: 'recap')]
    public function recap(ProductRepository $productRepository, SessionInterface $session): Response
    {
        try {
            if (!$this->getUser()) {
                $this->addFlash('warning', 'Vous devez vous connecter pour voir votre récapitulatif.');
                return $this->redirectToRoute('app_login');
            }

            $panier = $session->get('panier', []);
            $address = $session->get('address');

            $dataProduct = array_filter(array_map(function ($id) use ($productRepository, $panier) {
                $product = $productRepository->find($id);
                return $product ? [
                    'product' => $product,
                    'quantity' => $panier[$id],
                    'label' => $product->getLabel(),
                ] : null;
            }, array_keys($panier)));
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->redirectToRoute('cart_index');
        }
        return $this->render('cart/recap.html.twig', [
            'recap' => [
                'products' => $dataProduct,
                'addresses' => $address,
            ],
        ]);
    }
}
