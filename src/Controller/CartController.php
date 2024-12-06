<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Product;

class CartController extends AbstractController
{
    private CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function viewCart(): Response
    {
        // Get the current cart
        $cart = $this->cartService->getCart();

        // Return the cart as JSON response
        return $this->json([
            'cart' => $cart,
        ]);
    }
}
