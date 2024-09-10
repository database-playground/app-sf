<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Question;
use App\Entity\User;
use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ChallengeController extends AbstractController
{
    #[Route('/challenge/{id}', name: 'app_challenge')]
    public function index(
        #[CurrentUser] User $user,
        Question $question,
        QuestionRepository $questionRepository,
    ): Response {
        return $this->render('challenge/index.html.twig', [
            'user' => $user,
            'question' => $question,
            'limit' => $questionRepository->count(),
        ]);
    }
}
