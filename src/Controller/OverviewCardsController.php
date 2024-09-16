<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Level;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Repository\SolutionEventRepository;
use App\Service\PointCalculationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/overview/cards', name: 'app_overview_cards_')]
class OverviewCardsController extends AbstractController
{
    /**
     * Retrieve the card showing the experience point (XP).
     */
    #[Route('/points', name: 'points')]
    public function points(
        #[CurrentUser] User $user,
        PointCalculationService $pointCalculationService,
    ): Response {
        $points = $pointCalculationService->calculate($user);

        return $this->render('overview/cards/points.html.twig', [
            'points' => $points,
        ]);
    }

    /**
     * Retrieve the card showing the level.
     */
    #[Route('/level', name: 'level')]
    public function level(
        #[CurrentUser] User $user,
        QuestionRepository $questionRepository,
        SolutionEventRepository $solutionEventRepository,
    ): Response {
        $solvedQuestions = $solutionEventRepository->findSolvedQuestions($user);
        $totalQuestions = $questionRepository->count();

        $solvedQuestionPercent = \count($solvedQuestions) / $totalQuestions;
        $levelIndex = ceil(\count(Level::cases()) * $solvedQuestionPercent);

        $level = Level::cases()[$levelIndex];

        return $this->render('overview/cards/level.html.twig', [
            'level' => $level,
            'rawLevelIndex' => $levelIndex,
        ]);
    }

    /**
     * Retrieve the card showing the total number of questions solved.
     */
    #[Route('/questions/solved', name: 'solved_questions')]
    public function solvedQuestions(
        #[CurrentUser] User $user,
        SolutionEventRepository $solutionEventRepository,
    ): Response {
        $solvedQuestions = $solutionEventRepository->findSolvedQuestions($user);

        return $this->render('overview/cards/solved_questions.html.twig', [
            'questions' => \count($solvedQuestions),
        ]);
    }

    /**
     * Retrieve the card showing the total number of questions.
     */
    #[Route('/questions/count', name: 'questions_count')]
    public function questionCount(
        QuestionRepository $questionRepository,
    ): Response {
        return $this->render('overview/cards/questions_count.html.twig', [
            'questions' => $questionRepository->count(),
        ]);
    }

    /**
     * Retrieve the card showing the total number of solution events,
     * which means, the total number of times the user has solved a question.
     */
    #[Route('/events/count', name: 'events_count')]
    public function eventsCount(
        #[CurrentUser] User $user,
        SolutionEventRepository $solutionEventRepository,
    ): Response {
        $events = $solutionEventRepository->findUserEvents($user);

        return $this->render('overview/cards/events_count.html.twig', [
            'events_count' => \count($events),
        ]);
    }

    /**
     * Retrieve the timeline showing the history of the last 5 solution events.
     */
    #[Route('/events/history', name: 'events_history')]
    public function eventHistory(
        #[CurrentUser] User $user,
        SolutionEventRepository $solutionEventRepository,
    ): Response {
        $events = $solutionEventRepository->findUserEvents($user, limit: 5);

        return $this->render('overview/cards/events_history.html.twig', [
            'events' => $events,
        ]);
    }
}
