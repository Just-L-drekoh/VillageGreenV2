<?php

namespace App\Controller;

use App\Entity\Rubric;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/product', name: 'product_')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function products(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {

        try {
            $data = $entityManager->getRepository(Product::class)->findAll();
            $viewProducts = $paginator->paginate($data, $request->query->getInt('page', 1), 12);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de charger les produits, Veuillez réessayer plus tard');
            return $this->redirectToRoute('VillageGreen_index');
        }

        return $this->render('product/products.html.twig', ['products' => $viewProducts]);
    }
    #[Route('/{slug}', name: 'details')]
    public function productDetails(EntityManagerInterface $entityManager, string $slug): Response
    {

        try {
            $viewProductDetails = $entityManager->getRepository(Product::class)->findOneBy(['slug' => $slug]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de charger le produit, Veuillez réessayer plus tard');
            return $this->redirectToRoute('VillageGreen_index');
        }

        return $this->render('product/productDetails.html.twig', ['product' => $viewProductDetails]);
    }
    #[Route('/rubric/{slug}', name: 'productsByRubric')]
    public function productsByRubric(EntityManagerInterface $entityManager, string $slug): Response
    {
        try {
            $rubric = $entityManager->getRepository(Rubric::class)->findOneBy(['slug' => $slug]);

            if (!$rubric) {
                throw new \Exception('Rubrique introuvable');
            }

            $viewProductByRubric = $entityManager->getRepository(Product::class)->findBy(['rubric' => $rubric]);

            return $this->render('product/productsByRubric.html.twig', [
                'rubric' => $rubric,
                'products' => $viewProductByRubric
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de charger les instruments par rubrique');
            return $this->redirectToRoute('VillageGreen_index');
        }
    }
}
