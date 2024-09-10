<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Entity\Question;
use App\Service\QuestionDbRunnerService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Description
{
    public function __construct(
        private readonly QuestionDbRunnerService $questionDbRunnerService,
    ) {
    }

    public Question $question;

    /**
     * Get the columns of the answer.
     *
     * @return string[] the columns of the answer
     */
    public function getColumnsOfAnswer(): array
    {
        try {
            $answer = $this->questionDbRunnerService->getAnswerResult($this->question);

            // check if we have at least one row
            if (empty($answer)) {
                return [];
            }

            return array_keys($answer[0]);
        } catch (\Throwable $e) {
            return ["⚠️ Invalid Question: {$e->getMessage()}"];
        }
    }
}
