<?php

namespace App\Controller;

use App\Form\BankCartType;
use App\Form\OrderType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;


#[Route('/cart/validation-paiement', name: 'validation_cart_')]
class PaiementAddressController extends AbstractController
{
    #[Route('/address', name: 'address')]
    public function ChoicesAddress(SessionInterface $session): Response
    {
        try {
            $cart = $session->get('panier', []);

            if (empty($cart)) {
                $this->addFlash('warning', 'Votre panier est vide');
                return $this->redirectToRoute('cart_index');
            }

            $user = $this->getUser();
            if ($user) {
                $cart['user'] = $user;
            } else {
                $this->addFlash('error', 'Utilisateur non authentifié.');
                return $this->redirectToRoute('app_login');
            }
        } catch (\Exception $e) {
            $cart = [];
        }

        return $this->render('paiement_address/ChoiceAddress.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/paiement', name: 'paiement')]
    public function Paiement(SessionInterface $session, Request $request): Response
    {

        $user = $this->getUser();

        $formPaiementMethod = $this->createForm(OrderType::class, null, [
            'user' => $user
        ]);

        $formPaiementMethod->handleRequest($request);

        if ($formPaiementMethod->isSubmitted() && $formPaiementMethod->isValid()) {
            $paiement = $formPaiementMethod->get('paiement')->getData();
            $session->set('paiement', $paiement);
        }

        $formBankCart = $this->createForm(BankCartType::class);

        $formBankCart->handleRequest($request);

        if ($formBankCart->isSubmitted() && $formBankCart->isValid()) {
            $dataBankCart = $formBankCart->getData();

            $number = $formBankCart->get('number')->getData();

            $hashedNumber = hash('sha256', $number);

            $session->set('BankCart', [
                'name' => $dataBankCart['name'],
                'number' => $hashedNumber,
                'cvv' => $dataBankCart['cvv'],
                'date' => $dataBankCart['date'],
            ]);
        }
        dump($session);

        return $this->render('paiement_address/ChoicePaiement.html.twig', [
            'formPaiementMethod' => $formPaiementMethod->createView(),
            'formBankCart' => $formBankCart->createView(),

        ]);
    }
}
