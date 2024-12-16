<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/address', name: 'address_')]
class AddressController extends AbstractController
{
    #[Route('/add', name: 'add')]
    public function addAddress(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $existingAddresses = $entityManager->getRepository(Address::class)->findBy(['user' => $user]);
        $availableTypes = $this->getAvailableTypes($existingAddresses);

        if (empty($availableTypes)) {
            $this->addFlash('warning', 'Vous avez déjà une adresse de livraison et une adresse de facturation.');
            return $this->redirectToRoute('profile_index');
        }

        $address = new Address();
        $address->setUser($user)
            ->setDefault(true);

        $form = $this->createForm(AddressFormType::class, $address, [
            'available_types' => $availableTypes,
            'csrf_protection' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($address);
            $entityManager->flush();

            $this->addFlash('success', 'Adresse ajoutée avec succès.');
            return $this->redirectToRoute('profile_index');
        }

        return $this->render('address/addAddress.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/update/{id}', name: 'update')]
    public function updateAddress(Request $request, Address $address, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($address->getUser() !== $user) {
            return $this->redirectToRoute('app_profile');
        }

        $existingAddresses = $entityManager->getRepository(Address::class)->findBy(['user' => $user]);
        $usedTypes = array_map(fn($addr) => $addr->getType(), $existingAddresses);

        $availableTypes = $this->getAvailableTypes($existingAddresses, $address->getType());

        $form = $this->createForm(AddressFormType::class, $address, [
            'available_types' => $availableTypes,
            'csrf_protection' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($address);
            $entityManager->flush();

            $this->addFlash('success', 'Adresse mise à jour avec succès.');
            return $this->redirectToRoute('profile_index');
        }

        return $this->render('address/updateAddress.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function getAvailableTypes(array $existingAddresses, string $currentType = null): array
    {
        $types = ['Livraison', 'Facturation'];
        $usedTypes = array_map(fn($address) => $address->getType(), $existingAddresses);

        if ($currentType && !in_array($currentType, $usedTypes)) {
            $usedTypes[] = $currentType;
        }

        $availableTypes = array_diff($types, $usedTypes);

        return array_combine($availableTypes, $availableTypes);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function deleteAddress(Address $address, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($address);
        $entityManager->flush();

        return $this->redirectToRoute('profile_index');
    }
}
