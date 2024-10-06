<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Group;
use App\Entity\Question;
use App\Entity\QuestionDifficulty;
use App\Entity\SolutionEvent;
use App\Entity\SolutionEventStatus;
use App\Entity\User;
use App\Repository\HintOpenEventRepository;
use App\Repository\SolutionEventRepository;
use App\Repository\SolutionVideoEventRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class PointCalculationService
{
    public const int base = 500;

    // SolutionEvent
    public const int solutionEventEasyPoint = 10;
    public const int solutionEventMediumPoint = 20;
    public const int solutionEventHardPoint = 30;

    // FirstSolver
    public const int firstSolverPoint = 10;

    // SolutionVideoEvent
    public const int solutionVideoEventEasyPoint = 6;
    public const int solutionVideoEventMediumPoint = 12;
    public const int solutionVideoEventHardPoint = 18;

    // HintOpenEvent
    public const int hintOpenEventPoint = 2;

    // Weekly Question XP (#33)
    public const int weeklyMinSolvedQuestionPoint = 5;
    public const int weeklyPerQuestionXpPoint = 4;

    public function __construct(
        private readonly SolutionEventRepository $solutionEventRepository,
        private readonly SolutionVideoEventRepository $solutionVideoEventRepository,
        private readonly HintOpenEventRepository $hintOpenEventRepository,
        private readonly TagAwareCacheInterface $cache,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function calculate(User $user): int
    {
        return self::base
            + $this->calculateSolutionQuestionPoints($user)
            + $this->calculateFirstSolutionPoints($user)
            + $this->calculateSolutionVideoPoints($user)
            + $this->calculateHintOpenPoints($user)
            + $this->calculateWeeklySolvedPunishPoints($user)
        ;
    }

    /**
     * Calculate the total points of the solution events.
     *
     * Successfully solving a problem increases experience points.
     * Easy: 10 points, Medium: 20 points, Hard: 30 points.
     *
     * @param User $user The user to calculate the points for
     *
     * @return int The total points of the user
     */
    protected function calculateSolutionQuestionPoints(User $user): int
    {
        $questions = $this->solutionEventRepository->findSolvedQuestions($user);

        return array_reduce($questions, fn (int $carry, Question $question) => $carry + match ($question->getDifficulty()) {
            QuestionDifficulty::Easy => self::solutionEventEasyPoint,
            QuestionDifficulty::Medium => self::solutionEventMediumPoint,
            QuestionDifficulty::Hard => self::solutionEventHardPoint,
            default => 0,
        }, 0);
    }

    /**
     * Calculate the points if the user is the first solver of a question.
     *
     * The first person to solve the problem gets 10 points.
     *
     * @throws InvalidArgumentException
     */
    protected function calculateFirstSolutionPoints(User $user): int
    {
        // select the question this user has ever solved
        $qb = $this->solutionEventRepository->createQueryBuilder('e')
            ->select('q')
            ->from(Question::class, 'q')
            ->where('e.question = q.id')
            ->andWhere('e.status = :status')
            ->andWhere('e.submitter = :submitter')
            ->setParameter('status', SolutionEventStatus::Passed)
            ->setParameter('submitter', $user);

        /**
         * @var Question[] $questions
         */
        $questions = $qb->getQuery()->getResult();

        // check if the user is the first solver of each question
        $points = 0;

        foreach ($questions as $question) {
            $firstSolver = $this->listFirstSolversOfQuestion($question, $user->getGroup());
            if (null !== $firstSolver && $firstSolver === $user->getId()) {
                $points += self::firstSolverPoint;
            }
        }

        return $points;
    }

    /**
     * List and cache the first solvers of each question.
     *
     * @param Question   $question the question to get the first solver
     * @param Group|null $group    the solver group (null = no group)
     *
     * @returns int|null the first solver ID of the question
     *
     * @throws InvalidArgumentException
     */
    protected function listFirstSolversOfQuestion(Question $question, ?Group $group): ?int
    {
        $groupId = null !== $group ? "{$group->getId()}" : '-none';

        return $this->cache->get(
            "question.q{$question->getId()}.g$groupId.first-solver",
            function (ItemInterface $item) use ($group, $question) {
                $item->tag(['question', 'first-solver', 'group']);

                $solutionEvent = $question
                    ->getSolutionEvents()
                    ->filter(fn (SolutionEvent $event) => $group === $event->getSubmitter()->getGroup())
                    ->findFirst(fn ($_, SolutionEvent $event) => SolutionEventStatus::Passed === $event->getStatus());

                return $solutionEvent?->getSubmitter()?->getId();
            }
        );
    }

    /**
     * Calculate the points of the solution video events.
     *
     * Each student will lose experience points for watching a solution video.
     * Easy: 6 points, Medium: 12 points, Hard: 18 points.
     */
    protected function calculateSolutionVideoPoints(User $user): int
    {
        /**
         * @var Question[] $questions
         */
        $questions = $this->solutionVideoEventRepository->createQueryBuilder('sve')
            ->from(Question::class, 'q')
            ->select('q')
            ->where('sve.opener = :user')
            ->andWhere('sve.question = q.id')
            ->groupBy('q.id')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $questionPointsPair = [];

        foreach ($questions as $question) {
            $questionPointsPair[$question->getId()] = match ($question->getDifficulty()) {
                QuestionDifficulty::Easy => self::solutionVideoEventEasyPoint,
                QuestionDifficulty::Medium => self::solutionVideoEventMediumPoint,
                QuestionDifficulty::Hard => self::solutionVideoEventHardPoint,
                default => 0,
            };
        }

        return -array_sum($questionPointsPair);
    }

    protected function calculateHintOpenPoints(User $user): int
    {
        $hintOpenEvents = $this->hintOpenEventRepository->findByUser($user);

        return -1 * \count($hintOpenEvents) * self::hintOpenEventPoint;
    }

    /**
     * Calculate the weekly solved question punish points.
     *
     * You need to solve at least 5 problems each week.
     * For each problem you fall short, you will lose 20 experience points.
     *
     * @param User $user The user to calculate the points for
     *
     * @return int The punish points, negative value
     */
    protected function calculateWeeklySolvedPunishPoints(User $user): int
    {
        $weeklyMinSolvedQuestion = self::weeklyMinSolvedQuestionPoint;
        $weeklyPerQuestionXP = self::weeklyPerQuestionXpPoint;

        // Current date and week
        $currentDate = new \DateTime();

        // Fetch the first attempt
        /**
         * @var array{firstAttemptDate: string|null} $firstAttempt
         */
        $firstAttempt = $this->solutionEventRepository->createQueryBuilder('e')
            ->select('MIN(e.createdAt) AS firstAttemptDate')
            ->where('e.submitter = :submitter')
            ->setParameter('submitter', $user)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $firstAttempt['firstAttemptDate']) {
            return 0;
        }

        // Ensure startDate is set to the start of the ISO week
        $startDate = (new \DateTime($firstAttempt['firstAttemptDate']))
            ->setTime(0, 0)
            ->modify('Monday this week');

        // Prepare the query to fetch counts per week
        $qb = $this->solutionEventRepository->createQueryBuilder('e')
            ->select('YEAR(e.createdAt) AS year', 'WEEK(e.createdAt) AS week', 'COUNT(e.id) AS cnt')
            ->where('e.submitter = :submitter')
            ->andWhere('e.status = :status')
            ->andWhere('e.createdAt BETWEEN :start AND :end')
            ->groupBy('year', 'week')
            ->setParameter('submitter', $user)
            ->setParameter('status', SolutionEventStatus::Passed)
            ->setParameter('start', $startDate)
            ->setParameter('end', $currentDate)
        ;

        /**
         * @var array<array{year: int, week: int, cnt: int}> $result
         */
        $result = $qb->getQuery()->getResult();

        // Index the result by "year-week"
        $resultIndexed = [];
        foreach ($result as $row) {
            $key = \sprintf('%d-%02d', $row['year'], $row['week']);
            $resultIndexed[$key] = $row['cnt'];
        }

        // Initialize punish points
        $punishPoints = 0;

        // Initialize date iteration
        $iterDate = clone $startDate;

        while ($iterDate < $currentDate) {
            $year = (int) $iterDate->format('o');
            $week = (int) $iterDate->format('W');
            $key = \sprintf('%d-%02d', $year, $week);
            $cnt = $resultIndexed[$key] ?? 0;

            if ($cnt < $weeklyMinSolvedQuestion) {
                $punishPoints += $weeklyPerQuestionXP * ($weeklyMinSolvedQuestion - $cnt);
            }

            // Move to next week
            $iterDate->modify('+1 week');
        }

        return -$punishPoints;
    }
}
