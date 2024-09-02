<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExercisesController extends AbstractController
{
    #[Route('/exercises', name: 'app_exercises')]
    public function index(Request $request, QuestionRepository $questionRepository): Response
    {
        $page = intval($request->get("p", 0)) ?: 1;
        $query = $request->get("q", "");

        $questions = $questionRepository->getPaginatedResult($query, $page);

        return $this->render('exercises/index.html.twig', [
            "questions" => $questions,
        ]);
    }
}
