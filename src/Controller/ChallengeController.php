<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Question;
use App\Entity\SolutionVideoEvent;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Service\DbRunnerService;
use App\Service\PromptService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

    /**
     * Get the hint of the query.
     *
     * It is a test-only API and is only available to administrator.
     * FIXME: remove it once the hint is fully implemented in front-end.
     */
    #[Route('/challenge/{id}/hint', name: 'app_challenge_hint', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function hint(
        Question $question,
        #[MapQueryParameter] string $query,
        DbRunnerService $dbRunnerService,
        PromptService $promptService,
    ): Response {
        $schema = $question->getSchema()?->getSchema() ?? '';
        $answer = $question->getAnswer();

        try {
            $result = $dbRunnerService->runQuery($schema, $query);
            $answerResult = $dbRunnerService->runQuery($schema, $answer);

            if ($result != $answerResult) {
                $hint = $promptService->hint($query, 'Different output', $answer);

                return $this->json(['hint' => $hint]);
            }
        } catch (\Exception $e) {
            $answer = $question->getAnswer();

            $hint = $promptService->hint($query, $e->getMessage(), $answer);

            return $this->json(['hint' => $hint]);
        }

        return $this->json(['hint' => '']);
    }
}
