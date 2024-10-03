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
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/overview/cards', name: 'app_overview_cards_')]
class OverviewCardsController extends AbstractController
{
    /**
     * The primary color.
     *
     * You should get it from `app.scss`.
     */
    private static string $primaryColor = '#4154f1';

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

    /**
     * Retrieve the daily bar chart of the solution events.
     *
     * It shows the number of solution events in the last 7 days.
     */
    #[Route('/events/daily-chart', name: 'events_daily_chart')]
    public function eventDailyChart(
        SolutionEventRepository $solutionEventRepository,
        ChartBuilderInterface $chartBuilder,
        TranslatorInterface $translator,
    ): Response {
        $startedAt = new \DateTimeImmutable('-7 days');

        $eventsQuery = $solutionEventRepository->createQueryBuilder('e')
            ->select('DATE(e.createdAt) as date, COUNT(e.id) as count')
            ->where('e.createdAt >= :date')
            ->orderBy('date', 'ASC')
            ->groupBy('date')
            ->setParameter('date', $startedAt)
            ->getQuery();

        /**
         * @var array<array{date: string, count: int}> $events
         */
        $events = $eventsQuery->getResult();

        // fill 0 if there is no event in a day
        for ($i = 0; $i < 7; ++$i) {
            $date = $startedAt->add(new \DateInterval("P{$i}D"))->format('Y-m-d');
            if (isset($events[$i]) && $events[$i]['date'] === $date) {
                continue;
            }

            array_splice($events, $i, 0, [['date' => $date, 'count' => 0]]);
        }

        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => array_map(fn ($event) => $event['date'], $events),
            'datasets' => [
                [
                    'label' => $translator->trans('charts.event_daily_chart'),
                    'backgroundColor' => self::$primaryColor,
                    'data' => array_map(fn ($event) => $event['count'], $events),
                ],
            ],
        ]);
        $chart->setOptions([
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'min' => 0,
                ],
            ],
        ]);

        return $this->render('overview/cards/events_daily_chart.html.twig', [
            'chart' => $chart,
        ]);
    }
}
