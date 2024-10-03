<?php

declare(strict_types=1);

namespace App\Twig\Components\Questions;

use App\Entity\Question;
use App\Entity\SolutionEvent;
use App\Entity\SolutionEventStatus;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Card
{
    public Question $question;

    /**
     * Get the pass rate of the question.
     *
     * @return float the pass rate of the question
     */
    public function getPassRate(): float
    {
        $totalAttemptCount = $this->getTotalAttemptCount();
        if (0 === $totalAttemptCount) {
            return 0;
        }

        return round($this->getTotalSolvedCount() / $totalAttemptCount * 100, 2);
    }

    /**
     * Get the pass rate level of the question.
     *
     * Low: 0% - 40%
     * Medium: 41 – 70%
     * High: 71% - 100%
     */
    public function getPassRateLevel(): string
    {
        $passRate = $this->getPassRate();

        return match (true) {
            $passRate <= 40 => 'low',
            $passRate <= 70 => 'medium',
            default => 'high',
        };
    }

    /**
     * Get the total number of attempts made on the question.
     *
     * @return int the total number of attempts made on the question
     */
    private function getTotalAttemptCount(): int
    {
        return $this->question->getSolutionEvents()->count();
    }

    /**
     * Get the total number of times the question has been solved.
     *
     * @return int the total number of times the question has been solved
     */
    private function getTotalSolvedCount(): int
    {
        return $this->question->getSolutionEvents()
            ->filter(
                fn (SolutionEvent $solutionEvent) => SolutionEventStatus::Passed === $solutionEvent->getStatus()
            )->count();
    }
}
