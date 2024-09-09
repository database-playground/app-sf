<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\SolutionEvent;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Repository\SolutionEventRepository;
use App\Service\PointCalculationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OverviewController extends AbstractController
{
    public function __construct(
        private PointCalculationService $pointCalculationService,
        private QuestionRepository $questionRepository,
        private SolutionEventRepository $solutionEventRepository,
        private Security $security,
    ) {
    }

    #[Route('/overview', name: 'app_overview')]
    public function index(): Response
    {
        return $this->render('overview/index.html.twig', [
            'points' => $this->getPoints(),
            'solved_questions' => $this->getSolvedQuestionsCount(),
            'events_count' => $this->getEventsCount(),
            'first_five_events' => $this->getFirstFiveEvents(),
            'questions_count' => $this->getQuestionsCount(),
        ]);
    }

    protected function getSolvedQuestionsCount(): int
    {
        $user = $this->security->getUser();
        \assert($user instanceof User);

        $solvedQuestions = $this->solutionEventRepository->listSolvedQuestions($user);

        return \count($solvedQuestions);
    }

    protected function getPoints(): int
    {
        return $this->pointCalculationService->calculate();
    }

    protected function getEventsCount(): int
    {
        $user = $this->security->getUser();
        \assert($user instanceof User);

        $allEvents = $this->solutionEventRepository->listAllEvents($user);

        return \count($allEvents);
    }

    protected function getQuestionsCount(): int
    {
        return $this->questionRepository->count();
    }

    /**
     * @return array<SolutionEvent>
     */
    protected function getFirstFiveEvents(): array
    {
        $user = $this->security->getUser();
        \assert($user instanceof User);

        return $this->solutionEventRepository->listAllEvents($user, limit: 5);
    }
}
