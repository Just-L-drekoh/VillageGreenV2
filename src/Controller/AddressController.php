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

        $existingShipping = $entityManager->getRepository(Address::class)->findOneBy([
            'type' => 'Livraison',
            'user' => $user
        ]);
        $existingBilling = $entityManager->getRepository(Address::class)->findOneBy([
            'type' => 'Facturation',
            'user' => $user
        ]);

        if ($existingShipping && $existingBilling) {
            $this->addFlash('warning', 'Vous avez déjà une adresse de livraison et une adresse de facturation.');
            return $this->redirectToRoute('profile_index');
        }

        $address = new Address();
        $address->setUser($user);
        $address->setDefault(true);
        $availableTypes = [];
        if (!$existingShipping) {
            $availableTypes['Livraison'] = 'Livraison';
        }
        if (!$existingBilling) {
            $availableTypes['Facturation'] = 'Facturation';
        }

        $form = $this->createForm(AddressFormType::class, $address, [
            'available_types' => $availableTypes,
            'csrf_protection' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($address);
            $entityManager->flush();

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
        $address = $entityManager->getRepository(Address::class)->find($address->getId());

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $addresstype = $address->getType();

        if ($address->getUser() !== $user) {
            return $this->redirectToRoute('app_profile');
        }

        $availableTypes = [];

        $availableTypes[$addresstype] = $addresstype;

        $form = $this->createForm(AddressFormType::class, $address, [

            'available_types' => $availableTypes,

            'csrf_protection' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($address);
            $entityManager->flush();

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('address/uptadeAddress.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function deleteAddress(Address $address, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($address);
        $entityManager->flush();
        return $this->redirectToRoute('app_profile');
    }
}
