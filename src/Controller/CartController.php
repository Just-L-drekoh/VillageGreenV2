<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\OrderDetails;
use App\Service\OrderService;
use App\Service\SendEmailService;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderService $orderService,
        private SendEmailService $sendEmailService
    ) {}

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
                        'total' => $productDetails['total'],
                        'totalTaxes' => $productDetails['totalTaxes'],
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

    #[Route('/allRemove/{id}', name: 'allRemove')]
    public function allRemove(Product $product, SessionInterface $session): Response
    {
        try {
            $panier = $session->get('panier', []);
            $id = $product->getId();

            if (!empty($panier[$id])) {
                unset($panier[$id]);
            }

            $session->set('panier', $panier);

            return $this->redirectToRoute('cart_index');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->redirectToRoute('cart_index');
        }
    }


    #[Route('/order', name: 'order')]
    public function order(SessionInterface $session): Response
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
                return $this->redirectToRoute('validation_cart_paiement');
            }

            $order = $this->orderService->createOrder($this->getUser(), $panier, $paiement);
            $orderDetails = $this->entityManager->getRepository(OrderDetails::class)->findBy(['order' => $order]);

            $this->sendEmailService->send(
                'no-reply@village-green.fr',
                $session->get('user')->getEmail(),
                'Votre commande sur le site Village Green',
                'recap',
                [
                    'order' => $order,
                    'orderDetails' => $orderDetails
                ]
            );

            $session->clear();
            $this->addFlash('success', 'Votre commande a été enregistrée avec succès.');
            return $this->redirectToRoute('profile_index');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('cart_index');
        }
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
                        'total' => $productDetails['total'],
                        'totalTaxes' => $productDetails['totalTaxes'],
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue.');
            return $this->redirectToRoute('cart_index');
        }

        return $this->render('cart/recap.html.twig', [
            'recap' => [
                'products' => $dataProduct,
                'addresses' => $address,
                'total' => $total,
                'totalTaxes' => $totalTaxes,
            ],

        ]);
    }
}
