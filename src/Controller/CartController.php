<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\CartService;

class CartController extends AbstractController
{




    public function viewCart(): Response
    {

        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez vous connecter pour acceder au panier');
            return $this->redirectToRoute('app_login');
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous devez vous connecter en tant que client pour acceder au panier');
            return $this->redirectToRoute('app_index');
        }


        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
        ]);
    }
}
