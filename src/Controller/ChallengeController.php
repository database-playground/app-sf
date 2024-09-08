<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\QuestionRepository;
use App\Service\QuestionDbRunnerService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChallengeController extends AbstractController
{
    /**
     * @throws InvalidArgumentException
     */
    #[Route('/challenge/{id}', name: 'app_challenge')]
    public function index(
        QuestionRepository $questionRepository,
        QuestionDbRunnerService $questionDbRunnerService,
        int $id,
    ): Response {
        $question = $questionRepository->findById($id);
        if (!$question) {
            throw $this->createNotFoundException("找不到編號為 #$id 的問題");
        }

        return $this->render('challenge/index.html.twig', [
            'question' => $question,
            'limit' => $questionRepository->count(),
        ]);
    }
}
