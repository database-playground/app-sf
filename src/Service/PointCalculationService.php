<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Question;
use App\Entity\QuestionDifficulty;
use App\Entity\SolutionEvent;
use App\Entity\SolutionEventStatus;
use App\Entity\User;
use App\Repository\HintOpenEventRepository;
use App\Repository\QuestionRepository;
use App\Repository\SolutionEventRepository;
use App\Repository\SolutionVideoEventRepository;

final class PointCalculationService
{
    public static int $base = 500;

    // SolutionEvent
    public static int $solutionEventEasyPoint = 10;
    public static int $solutionEventMediumEvent = 20;
    public static int $solutionEventHardEvent = 30;

    // FirstSolver
    public static int $firstSolverPoint = 10;

    // SolutionVideoEvent
    public static int $solutionVideoEventEasy = 6;
    public static int $solutionVideoEventMedium = 12;
    public static int $solutionVideoEventHard = 18;

    // HintOpenEvent
    public static int $hintOpenEventPoint = 2;

    public function __construct(
        private readonly SolutionEventRepository $solutionEventRepository,
        private readonly SolutionVideoEventRepository $solutionVideoEventRepository,
        private readonly QuestionRepository $questionRepository,
        private readonly HintOpenEventRepository $hintOpenEventRepository,
    ) {
    }

    public function calculate(User $user): int
    {
        return self::$base
            + $this->calculateSolutionQuestionPoints($user)
            + $this->calculateFirstSolutionPoints($user)
            + $this->calculateSolutionVideoPoints($user)
            + $this->calculateHintOpenPoints($user);
    }

    /**
     * Calculate the total points of the solution events.
     *
     * 每位同學基本經驗值500點，成功解一題獲得經驗值增加。易:10點、中:20點、難:30點
     *
     * @param User $user The user to calculate the points for
     *
     * @return int The total points of the user
     */
    protected function calculateSolutionQuestionPoints(User $user): int
    {
        $questions = $this->solutionEventRepository->findSolvedQuestions($user);

        return array_reduce($questions, fn (int $carry, Question $question) => $carry + match ($question->getDifficulty()) {
            QuestionDifficulty::Easy => self::$solutionEventEasyPoint,
            QuestionDifficulty::Medium => self::$solutionEventMediumEvent,
            QuestionDifficulty::Hard => self::$solutionEventHardEvent,
            default => 0,
        }, 0);
    }

    /**
     * Calculate the points if the user is the first solver of a question.
     *
     * 第一位解題成功者加10點。
     */
    protected function calculateFirstSolutionPoints(User $user): int
    {
        $questions = $this->questionRepository->findAll();

        $points = 0;

        // list the first solver of each question
        foreach ($questions as $question) {
            $solutionEvent = $question
                ->getSolutionEvents()
                ->findFirst(fn ($_, SolutionEvent $event) => SolutionEventStatus::Passed === $event->getStatus());

            if (!$solutionEvent || $solutionEvent->getSubmitter() !== $user) {
                continue;
            }

            $points += self::$firstSolverPoint;
        }

        return $points;
    }

    protected function calculateSolutionVideoPoints(User $user): int
    {
        $solutionVideoEvents = $this->solutionVideoEventRepository->findByUser($user);

        $questionPointsPair = [];
        foreach ($solutionVideoEvents as $event) {
            $question = $event->getQuestion();
            if (!$question) {
                continue;
            }

            $questionPointsPair[$question->getId()] = match ($question->getDifficulty()) {
                QuestionDifficulty::Easy => self::$solutionVideoEventEasy,
                QuestionDifficulty::Medium => self::$solutionVideoEventMedium,
                QuestionDifficulty::Hard => self::$solutionVideoEventHard,
                default => 0,
            };
        }

        return -array_sum($questionPointsPair);
    }

    protected function calculateHintOpenPoints(User $user): int
    {
        $hintOpenEvents = $this->hintOpenEventRepository->findByUser($user);

        return -1 * \count($hintOpenEvents) * self::$hintOpenEventPoint;
    }
}
