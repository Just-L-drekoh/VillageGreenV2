<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\UserFormType;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/profile', name: 'profile_')]
class ProfilController extends AbstractController
{
    public function __construct(private ValidatorInterface $validator) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
                return $this->redirectToRoute('app_login');
            }

            $errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                $this->addFlash('error', 'Votre profil utilisateur est invalide. Veuillez contacter un administrateur.');
                return $this->redirectToRoute('villageGreen_index');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur s\'est produite lors de la recuperation de votre profil.');
            return $this->redirectToRoute('villageGreen_index');
        }
        return $this->render('profil/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/update', name: 'update', methods: ['GET', 'POST'])]
    public function update(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                $this->addFlash('error', 'Vous devez être connecté pour modifier votre profil.');
                return $this->redirectToRoute('app_login');
            }

            $form = $this->createForm(UserFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
                    return $this->redirectToRoute('profile_index');
                } catch (\Exception $exception) {
                    $this->addFlash('error', 'Une erreur s\'est produite lors de la mise à jour de votre profil.');
                }
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur s\'est produite lors de la mise à jour de votre profil.');
            return $this->redirectToRoute('villageGreen_index');
        }
        return $this->render('profil/update_profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
