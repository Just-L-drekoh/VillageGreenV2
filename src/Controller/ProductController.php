<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Rubric;
use App\Repository\ProductRepository;
use App\Repository\RubricRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/product', name: 'product_')]
class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private RubricRepository $rubricRepository,
        private PaginatorInterface $paginator
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $productsQuery = $this->productRepository->findAll();

        $paginatedProducts = $this->paginator->paginate(
            $productsQuery,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('product/products.html.twig', [
            'products' => $paginatedProducts,
        ]);
    }

    #[Route('/{slug}', name: 'details', methods: ['GET'])]
    public function details(string $slug): Response
    {
        $product = $this->productRepository->findOneBy(['slug' => $slug]);

        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        return $this->render('product/product_details.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/rubric/{slug}', name: 'by_rubric', methods: ['GET'])]
    public function byRubric(string $slug): Response
    {
        $rubric = $this->rubricRepository->findOneBy(['slug' => $slug]);

        if (!$rubric) {
            throw $this->createNotFoundException('Rubrique introuvable.');
        }

        $productsByRubric = $this->productRepository->findBy(['rubric' => $rubric]);

        return $this->render('product/products_by_rubric.html.twig', [
            'rubric' => $rubric,
            'products' => $productsByRubric,
        ]);
    }
}
