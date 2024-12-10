<?php

namespace App\Controller;

use App\Entity\Rubric;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/rubric', name: 'rubric_')]
class RubricController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function rubrics(EntityManagerInterface $entityManager): Response
    {

        try {
            $viewRubrics = $entityManager->getRepository(Rubric::class)->findBy(['parent' => null]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de charger les rubriques Veuillez réessayer plus tard');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('rubric/rubrics.html.twig', ['rubrics' => $viewRubrics]);
    }
}
