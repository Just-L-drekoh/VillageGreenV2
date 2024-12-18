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
    private ProductRepository $productRepository;
    private RubricRepository $rubricRepository;
    private PaginatorInterface $paginator;

    public function __construct(
        ProductRepository $productRepository,
        RubricRepository $rubricRepository,
        PaginatorInterface $paginator
    ) {
        $this->productRepository = $productRepository;
        $this->rubricRepository = $rubricRepository;
        $this->paginator = $paginator;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        try {
            $productsQuery = $this->productRepository->findAll();
            $paginatedProducts = $this->paginator->paginate(
                $productsQuery,
                $request->query->getInt('page', 1),
                12
            );
        } catch (\Exception $exception) {
            $this->addFlash('error', 'Impossible de charger les produits. Veuillez réessayer plus tard.');
            return $this->redirectToRoute('VillageGreen_index');
        }

        return $this->render('product/products.html.twig', [
            'products' => $paginatedProducts,
        ]);
    }

    #[Route('/{slug}', name: 'details', methods: ['GET'])]
    public function details(string $slug): Response
    {
        try {
            $product = $this->productRepository->findOneBy(['slug' => $slug]);

            if (!$product) {
                throw $this->createNotFoundException('Produit introuvable.');
            }
        } catch (\Exception $exception) {
            $this->addFlash('error', 'Impossible de charger le produit. Veuillez réessayer plus tard.');
            return $this->redirectToRoute('VillageGreen_index');
        }

        return $this->render('product/product_details.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/rubric/{slug}', name: 'by_rubric', methods: ['GET'])]
    public function byRubric(string $slug): Response
    {
        try {
            $rubric = $this->rubricRepository->findOneBy(['slug' => $slug]);

            if (!$rubric) {
                throw $this->createNotFoundException('Rubrique introuvable.');
            }

            $productsByRubric = $this->productRepository->findBy(['rubric' => $rubric]);
        } catch (\Exception $exception) {
            $this->addFlash('error', 'Impossible de charger les produits par rubrique.');
            return $this->redirectToRoute('VillageGreen_index');
        }

        return $this->render('product/products_by_rubric.html.twig', [
            'rubric' => $rubric,
            'products' => $productsByRubric,
        ]);
    }
}
