<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/address', name: 'address_')]
class AddressController extends AbstractController
{
    private function checkUser(): Response|null
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour gérer vos adresses.');
            return $this->redirectToRoute('app_login');
        }
        return null;
    }

    private function getAvailableTypes(array $existingAddresses, string $currentType = null): array
    {
        $types = ['Livraison', 'Facturation'];
        $usedTypes = array_map(fn($address) => $address->getType(), $existingAddresses);

        if ($currentType) {
            $usedTypes = array_diff($usedTypes, [$currentType]);
        }

        $availableTypes = array_diff($types, $usedTypes);

        if ($currentType) {
            $availableTypes[] = $currentType;
        }

        return array_combine($availableTypes, $availableTypes);
    }

    #[Route('/add', name: 'add')]
    public function addAddress(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($response = $this->checkUser()) {
            return $response;
        }

        $user = $this->getUser();
        $existingAddresses = $entityManager->getRepository(Address::class)->findBy(['user' => $user]);
        $availableTypes = $this->getAvailableTypes($existingAddresses);

        if (empty($availableTypes)) {
            $this->addFlash('warning', 'Vous avez déjà une adresse de livraison et une adresse de facturation.');
            return $this->redirectToRoute('profile_index');
        }

        $address = (new Address())->setUser($user)->setDefault(true);
        $form = $this->createForm(AddressFormType::class, $address, ['available_types' => $availableTypes]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($address);
            $entityManager->flush();

            $this->addFlash('success', 'Adresse ajoutée avec succès.');
            return $this->redirectToRoute('profile_index');
        }

        return $this->render('address/add_address.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/update/{id}', name: 'update')]
    public function updateAddress(Request $request, Address $address, EntityManagerInterface $entityManager): Response
    {
        if ($response = $this->checkUser()) {
            return $response;
        }

        if ($address->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'avez pas la permission de modifier cette adresse.');
            return $this->redirectToRoute('profile_index');
        }

        $existingAddresses = $entityManager->getRepository(Address::class)->findBy(['user' => $this->getUser()]);
        $availableTypes = $this->getAvailableTypes($existingAddresses, $address->getType());

        $form = $this->createForm(AddressFormType::class, $address, ['available_types' => $availableTypes]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Adresse mise à jour avec succès.');
            return $this->redirectToRoute('profile_index');
        }

        return $this->render('address/update_address.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/delete/{id}', name: 'delete')]
    public function deleteAddress(Address $address, EntityManagerInterface $entityManager): Response
    {
        if ($response = $this->checkUser()) {
            return $response;
        }

        if ($address->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'avez pas la permission de supprimer cette adresse.');
            return $this->redirectToRoute('profile_index');
        }

        $entityManager->remove($address);
        $entityManager->flush();

        $this->addFlash('success', 'Adresse supprimée avec succès.');
        return $this->redirectToRoute('profile_index');
    }
}
