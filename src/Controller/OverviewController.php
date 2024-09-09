<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\SolutionEventStatus;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Repository\SolutionEventRepository;
use App\Service\PointCalculationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OverviewController extends AbstractController
{
    #[Route('/overview', name: 'app_overview')]
    public function index(
        PointCalculationService $pointCalculationService,
        QuestionRepository $questionRepository,
        SolutionEventRepository $solutionEventRepository,
        Security $security,
    ): Response {
        return $this->render('overview/index.html.twig', [
            'points' => $pointCalculationService->calculate(),
            'solved_questions' => $this->getSolvedQuestions($solutionEventRepository, $security),
            'events' => $this->getTotalEventsOfUser($solutionEventRepository, $security),
            'questions_count' => $this->getAllQuestionsCount($questionRepository),
        ]);
    }

    protected function getSolvedQuestions(
        SolutionEventRepository $solutionEventRepository,
        Security $security,
    ): int {
        $user = $security->getUser();
        \assert($user instanceof User);

        $solvedQuestions = $solutionEventRepository->listQuestionsWithStatus($user, SolutionEventStatus::Passed);

        return \count($solvedQuestions);
    }

    protected function getPoints(PointCalculationService $pointCalculationService): int
    {
        return $pointCalculationService->calculate();
    }

    protected function getTotalEventsOfUser(
        SolutionEventRepository $solutionEventRepository,
        Security $security,
    ): int {
        $user = $security->getUser();
        \assert($user instanceof User);

        $allEvents = $solutionEventRepository->listAllEventsOfUser($user);

        return \count($allEvents);
    }

    protected function getAllQuestionsCount(QuestionRepository $questionRepository): int
    {
        return $questionRepository->count();
    }
}
