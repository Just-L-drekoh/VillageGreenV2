<?php

namespace App\Controller;

use App\Entity\Rubric;
use App\Repository\RubricRepository;
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
}
