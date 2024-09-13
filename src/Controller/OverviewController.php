<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\SolutionEvent;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Repository\SolutionEventRepository;
use App\Service\PointCalculationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class OverviewController extends AbstractController
{
    public function __construct(
        private readonly PointCalculationService $pointCalculationService,
        private readonly QuestionRepository $questionRepository,
        private readonly SolutionEventRepository $solutionEventRepository,
    ) {
    }

    #[Route('/overview', name: 'app_overview')]
    public function index(#[CurrentUser] User $user): Response
    {
        return $this->render('overview/index.html.twig', [
            'points' => $this->getPoints($user),
            'solved_questions' => $this->getSolvedQuestionsCount($user),
            'events_count' => $this->getEventsCount($user),
            'first_five_events' => $this->getFirstFiveEvents($user),
            'questions_count' => $this->getQuestionsCount(),
        ]);
    }

    protected function getSolvedQuestionsCount(User $user): int
    {
        $solvedQuestions = $this->solutionEventRepository->findSolvedQuestions($user);

        return \count($solvedQuestions);
    }

    protected function getPoints(User $user): int
    {
        return $this->pointCalculationService->calculate($user);
    }

    protected function getEventsCount(User $user): int
    {
        $allEvents = $this->solutionEventRepository->findUserEvents($user);

        return \count($allEvents);
    }

    protected function getQuestionsCount(): int
    {
        return $this->questionRepository->count();
    }

    /**
     * @return array<SolutionEvent>
     */
    protected function getFirstFiveEvents(User $user): array
    {
        return $this->solutionEventRepository->findUserEvents($user, limit: 5);
    }
}
