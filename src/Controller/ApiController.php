<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function SearchProducts(Request $request, ProductRepository $productRepository): Response
    {
        try {
            $query = $request->query->get('q', '');
            $products = $productRepository->searchByLabel($query);

            return $this->json($products, 200, [], ['groups' => 'product:read']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Impossible de récupérer les produits'], 400);
        }
    }
}
