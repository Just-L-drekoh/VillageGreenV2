<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Rubric;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{

    public function index(): Response
    {
        return $this->render('main/index.html.twig', []);
    }

    public function rubrics(EntityManagerInterface $entityManager): Response
    {

        try {
            $viewRubrics = $entityManager->getRepository(Rubric::class)->findBy(['parent' => null]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de charger les rubriques');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('main/rubrics.html.twig', ['rubrics' => $viewRubrics]);
    }

    public function products(EntityManagerInterface $entityManager): Response
    {

        try {
            $viewProducts = $entityManager->getRepository(Product::class)->findAll();
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de charger les produits');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('main/products.html.twig', ['products' => $viewProducts]);
    }

    public function productDetails(EntityManagerInterface $entityManager, string $slug): Response
    {

        try {
            $viewProductDetails = $entityManager->getRepository(Product::class)->findOneBy(['slug' => $slug]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de charger le produit');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('main/productDetails.html.twig', ['product' => $viewProductDetails]);
    }

    public function productsByRubric(EntityManagerInterface $entityManager, string $slug): Response
    {
        try {
            // Trouver la rubrique correspondante au slug
            $rubric = $entityManager->getRepository(Rubric::class)->findOneBy(['slug' => $slug]);

            if (!$rubric) {
                throw new \Exception('Rubrique introuvable');
            }

            // Récupérer les produits liés à cette rubrique
            $viewProductByRubric = $entityManager->getRepository(Product::class)->findBy(['rubric' => $rubric]);

            return $this->render('main/productsByRubric.html.twig', [
                'rubric' => $rubric,
                'products' => $viewProductByRubric
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de charger les instruments par rubrique');
            return $this->redirectToRoute('app_index');
        }
    }
}
