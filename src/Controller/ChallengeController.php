<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Question;
use App\Entity\SolutionVideoEvent;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
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

    #[Route('/challenge/{id}/solution-video', name: 'app_challenge_solution_video', methods: ['GET'])]
    public function solution_video(
        Question $question,
        EntityManagerInterface $entityManager,
        #[CurrentUser] User $user,
        #[MapQueryParameter] string $csrf,
    ): Response {
        if (!$this->isCsrfTokenValid('challenge-solution', $csrf)) {
            throw $this->createAccessDeniedException('Invalid path to open solution.');
        }

        $solutionVideo = $question->getSolutionVideo();
        if (!$solutionVideo) {
            throw $this->createNotFoundException('There is no solution video for this question.');
        }

        $event = (new SolutionVideoEvent())
            ->setQuestion($question)
            ->setOpener($user);
        $entityManager->persist($event);
        $entityManager->flush();

        return $this->redirect($solutionVideo, Response::HTTP_FOUND);
    }

    #[Route('/challenge/{id}/comment', name: 'app_challenge_comment')]
    public function comment(
        #[CurrentUser] User $user,
        Question $question,
        CommentRepository $commentRepository,
    ): Response {
        $comments = $commentRepository->findQuestionComments($question);

        return $this->render('challenge/comment.html.twig', [
            'user' => $user,
            'comments' => $comments,
        ]);
    }
}
