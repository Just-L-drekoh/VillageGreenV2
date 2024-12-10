<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\UserFormType;
use App\Form\AddressFormType;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/profile', name: 'profile_')]
class ProfilController extends AbstractController
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }
    #[Route('/', name: 'index')]
    public function profile(AddressRepository $addressRepository): Response
    {
        $user = $this->getUser();

        // Vérification si l'utilisateur est connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        try {

            if (!$this->validator->validate($user)) {
                throw new \RuntimeException('Utilisateur non valide');
            }
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('VillageGreen_index');
        }

        // Affichage du profil en cas de succès
        return $this->render('profil/index.html.twig', [
            'controller_name' => 'ProfilController',
            'user' => $user
        ]);
    }

    #[Route('/update', name: 'update')]
    public function updateProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(UserFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profil/updateProfile.html.twig', [
            'controller_name' => 'ProfilController',
            'form' => $form->createView()
        ]);
    }
}
