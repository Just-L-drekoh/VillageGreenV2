<?php

namespace App\Controller;

use App\Entity\Rubric;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class MainController extends AbstractController
{
    #Cette fonction affichera la page d'accueil de l'application Village Green
    #il affiche les rubriques et les produits les plus populaires
    public function index(): Response
    {
        return $this->render('main/index.html.twig', []);
    }

    #Cette fonction affichera toutes les rubriques de l'application Village Green
    #il affiche les rubriques et les sous rubriques correspondantes a cette meme rubrique
    public function rubrics(EntityManagerInterface $entityManager): Response
    {

        try {
            $viewRubrics = $entityManager->getRepository(Rubric::class)->findBy(['parent' => null]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de charger les rubriques');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('main/rubric/rubrics.html.twig', ['rubrics' => $viewRubrics]);
    }

    #Cette fonction affichera tous les produits par rubrique/sousRubrique
    #il affiche les produits par rubrique/sousRubrique par rapport au slug de la rubrique
    public function productsByRubric(EntityManagerInterface $entityManager, string $slug): Response
    {
        try {
            #Recherche de la rubrique
            $rubric = $entityManager->getRepository(Rubric::class)->findOneBy(['slug' => $slug]);

            if (!$rubric) {
                throw new \Exception('Rubrique introuvable');
            }

            #Recherche des produits liée a la sous rubrique
            $viewProductByRubric = $entityManager->getRepository(Product::class)->findBy(['rubric' => $rubric]);

            return $this->render('main/rubric/productsByRubric.html.twig', [
                'rubric' => $rubric,
                'products' => $viewProductByRubric
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de charger les instruments par rubrique');
            return $this->redirectToRoute('app_index');
        }
    }

    #Cette fonction affichera tous les produits
    #il affiche tous les produits
    public function products(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {

        try {
            $data = $entityManager->getRepository(Product::class)->findAll();
            $viewProducts = $paginator->paginate($data, $request->query->getInt('page', 1), 12);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de charger les produits');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('main/product/products.html.twig', ['products' => $viewProducts]);
    }


    #Cette fonction affichera le detail du produit
    #il affiche le detail du produit par rapport au slug
    public function productDetails(EntityManagerInterface $entityManager, string $slug): Response
    {

        try {
            $viewProductDetails = $entityManager->getRepository(Product::class)->findOneBy(['slug' => $slug]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de charger le produit');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('main/product/productDetails.html.twig', ['product' => $viewProductDetails]);
    }

    #[Route('/api/products', name: 'api_search', methods: ['GET'])]
    public function search(Request $request, ProductRepository $productRepository): Response
    {
        $query = $request->query->get('q', ''); // Récupère le paramètre de recherche 'q'
        $products = $productRepository->createQueryBuilder('p')
            ->where('p.label LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        return $this->json($products, 200, [], ['groups' => 'product:read']);
    }
}
