<?php

declare(strict_types=1);

namespace App\Service\Types;

use App\Entity\SolutionEvent;
use App\Entity\SolutionEventStatus;

/**
 * The pass rate of a question.
 *
 * - `total`: the total number of attempts
 * - `passed`: the number of successful attempts
 * - `passRate`: the pass rate of the question in percentage
 * - `level`: the level of the pass rate, can be 'low', 'medium', or 'high'
 */
readonly class PassRate
{
    /**
     * @var int the total number of attempts
     */
    public int $total;

    /**
     * @var int the number of successful attempts
     */
    public int $passed;

    /**
     * @param SolutionEvent[] $attempts
     */
    public function __construct(
        array $attempts,
    ) {
        $this->total = \count($attempts);
        $this->passed = \count(array_filter($attempts, fn (SolutionEvent $event) => SolutionEventStatus::Passed === $event->getStatus()));
    }

    /**
     * Calculate the pass rate of a question.
     *
     * @return float the pass rate of the question in percentage
     */
    public function getPassRate(): float
    {
        if (0 === $this->total) {
            return 0;
        }

        return round($this->passed / $this->total * 100, 2);
    }

    /**
     * @return string the level of the pass rate, can be 'low', 'medium', or 'high'
     */
    public function getLevel(): string
    {
        $passRate = $this->getPassRate();

        return match (true) {
            $passRate <= 40 => 'low',
            $passRate <= 70 => 'medium',
            default => 'high',
        };
    }
}
