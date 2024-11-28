<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressFormType;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfilController extends AbstractController
{
   
    public function profile(AddressRepository $addressRepository): Response
    {
        $user = $this->getUser();
        $address = $addressRepository->findBy(['user' => $user]);
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('profil/index.html.twig', [
            'controller_name' => 'ProfilController',
            'user' => $user,
            'address'=> $address
        ]);
    }

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
            return $this->redirectToRoute('app_profile');
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
    
            return $this->redirectToRoute('app_profile');
        }
    
        return $this->render('profil/addAddress.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
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
    
        return $this->render('profil/uptadeAddress.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    

    public function deleteAddress(Address $address, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($address);
        $entityManager->flush();
        return $this->redirectToRoute('app_profile');
    }
}
