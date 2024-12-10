<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    public function SearchProducts(Request $request, ProductRepository $productRepository): Response
    {
        $query = $request->query->get('q', ''); // Récupère le paramètre de recherche 'q'
        $products = $productRepository->searchByLabel($query);

        return $this->json($products, 200, [], ['groups' => 'product:read']);
    }
}
