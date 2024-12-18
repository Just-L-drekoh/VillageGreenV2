<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Service\SendEmailService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/contact', name: 'contact_')]
class ContactController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function contact(Request $request, SendEmailService $sendEmailService): Response
    {
        $form = $this->createForm(ContactType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash('success', 'Votre demande de contact a bien été envoyée. Vous avez reçu un email de confirmation.');
            //mail pour le client
            $sendEmailService->send(
                'no-reply@Village-green.fr',
                $form->get('email')->getData(),
                $form->get('subject')->getData(),
                'confirm_contact',
                ['form' => $form->getData()]
            );

            //mail pour l'admin
            $sendEmailService->send(
                $form->get('email')->getData(),
                'Service-Contact@Village-green.fr',
                $form->get('subject')->getData(),
                'contact_client',
                ['form' => $form->getData()]
            );
            return $this->redirectToRoute('VillageGreen_index');
        }



        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
