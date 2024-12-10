<?php

namespace App\Controller;

use App\Entity\Product;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function viewCart(ProductRepository $productRepository, SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);

        $dataProduct = [];
        $total = 0;

        foreach ($panier as $id => $quantity) {
            $product = $productRepository->find($id);

            if (!$product) {
                continue;
            }

            $dataProduct[] = [
                'product' => $product,
                'quantity' => $quantity
            ];

            $total += $product->getPrice() * $quantity;
        }

        return $this->render('cart/index.html.twig', [
            'products' => $dataProduct,
            'total' => $total
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
}
