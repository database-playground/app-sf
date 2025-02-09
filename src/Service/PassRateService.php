<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Group;
use App\Entity\PassRate;
use App\Entity\Question;
use App\Repository\SolutionEventRepository;

/**
 * Get the pass rate of a question in the optimized matter.
 */
final readonly class PassRateService
{
    public function __construct(
        private SolutionEventRepository $solutionEventRepository,
    ) {}

    /**
     * Get the pass rate in this group of a question.
     *
     * @param Question   $question the question to calculate the pass rate
     * @param null|Group $group    the group to calculate the pass rate, null for no group
     *
     * @return PassRate the pass rate, see {@link PassRate} for details
     */
    public function getPassRate(Question $question, ?Group $group): PassRate
    {
        $attempts = $this->solutionEventRepository->getTotalAttempts($question, $group);

        return new PassRate($attempts);
    }
}
