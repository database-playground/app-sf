<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The page size of the exercises page.
 */
const PAGE_SIZE = 15;

class ExercisesController extends AbstractController
{
    #[Route('/exercises', name: 'app_exercises')]
    public function index(Request $request, QuestionRepository $questionRepository): Response
    {
        $page = intval($request->get("p", 0)) ?: 1;
        $questions = $questionRepository->findBy(
            criteria: [],
            orderBy: ['id' => 'ASC'],
            limit: PAGE_SIZE,
            offset: ($page - 1) * PAGE_SIZE);

        return $this->render('exercises/index.html.twig', [
            "questions" => $questions,
        ]);
    }
}
