<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Question;
use App\Entity\QuestionDifficulty;
use App\Entity\SolutionEvent;
use App\Entity\SolutionEventStatus;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Repository\SolutionEventRepository;
use App\Repository\SolutionVideoEventRepository;

final class PointCalculationService
{
    public static int $BASE_SCORE = 500;

    // SolutionEvent
    public static int $SOLUTION_EVENT_EACH_EASY_POINT = 10;
    public static int $SOLUTION_EVENT_EACH_MEDIUM_POINT = 20;
    public static int $SOLUTION_EVENT_EACH_HARD_POINT = 30;

    // FirstSolver
    public static int $FIRST_SOLVER_POINT = 10;

    // SolutionVideoEvent
    public static int $SOLUTION_VIDEO_EACH_EVENT_EASY = 6;
    public static int $SOLUTION_VIDEO_EACH_EVENT_MEDIUM = 12;
    public static int $SOLUTION_VIDEO_EACH_EVENT_HARD = 18;

    public function __construct(
        private readonly SolutionEventRepository $solutionEventRepository,
        private readonly SolutionVideoEventRepository $solutionVideoEventRepository,
        private readonly QuestionRepository $questionRepository,
    ) {
    }

    public function calculate(User $user): int
    {
        return self::$BASE_SCORE
            + $this->calculateSolutionQuestionPoints($user)
            + $this->calculateFirstSolutionPoints($user)
            + $this->calculateSolutionVideoPoints($user);
    }

    /**
     * Calculate the total points of the solution events.
     *
     * 每位同學基本經驗值500點，成功解一題獲得經驗值增加。易:10點、 中:20點、難:30點
     *
     * @param User $user The user to calculate the points for
     *
     * @return int The total points of the user
     */
    protected function calculateSolutionQuestionPoints(User $user): int
    {
        $questions = $this->solutionEventRepository->listSolvedQuestions($user);

        return array_reduce($questions, fn (int $carry, Question $question) => $carry + match ($question->getDifficulty()) {
            QuestionDifficulty::Easy => self::$SOLUTION_EVENT_EACH_EASY_POINT,
            QuestionDifficulty::Medium => self::$SOLUTION_EVENT_EACH_MEDIUM_POINT,
            QuestionDifficulty::Hard => self::$SOLUTION_EVENT_EACH_HARD_POINT,
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

            $points += self::$FIRST_SOLVER_POINT;
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
                QuestionDifficulty::Easy => self::$SOLUTION_VIDEO_EACH_EVENT_EASY,
                QuestionDifficulty::Medium => self::$SOLUTION_VIDEO_EACH_EVENT_MEDIUM,
                QuestionDifficulty::Hard => self::$SOLUTION_VIDEO_EACH_EVENT_HARD,
                default => 0,
            };
        }

        return -array_sum($questionPointsPair);
    }
}
