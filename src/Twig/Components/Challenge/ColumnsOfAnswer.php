<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Entity\Question;
use App\Service\QuestionDbRunnerService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ColumnsOfAnswer
{
    use DefaultActionTrait;

    public function __construct(
        private readonly QuestionDbRunnerService $questionDbRunnerService,
    ) {
    }

    #[LiveProp]
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
            $answerResult = $answer->getResult();

            if (0 === \count($answerResult)) {
                return [];
            }

            return $answer->getResult()[0];
        } catch (\Throwable $e) {
            return ["⚠️ Invalid Question: {$e->getMessage()}"];
        }
    }
}
