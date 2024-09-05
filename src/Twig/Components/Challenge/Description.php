<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Entity\Question;
use App\Service\QuestionDbRunnerService;
use Psr\Cache\InvalidArgumentException;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Description
{
    public function __construct(
        protected QuestionDbRunnerService $questionDbRunnerService,
    ) {
    }

    public Question $question;

    /**
     * Get the columns of the answer.
     *
     * @return array<string> the columns of the answer
     *
     * @throws InvalidArgumentException
     */
    public function getColumnsOfAnswer(): array
    {
        $answer = $this->questionDbRunnerService->getAnswerResult($this->question);

        // check if we have at least one row
        if (empty($answer)) {
            return [];
        }

        return array_keys($answer[0]);
    }
}
