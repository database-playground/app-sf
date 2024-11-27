<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Tabs;

use App\Entity\ChallengeDto\FallableQueryResultDto;
use App\Entity\Question;
use App\Service\QuestionSqlRunnerService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

use function Symfony\Component\Translation\t;

#[AsTwigComponent]
final class AnswerQueryResult
{
    public function __construct(
        private readonly QuestionSqlRunnerService $questionDbRunnerService,
    ) {
    }

    /**
     * @var Question the question to present the answer
     */
    public Question $question;

    public function getAnswer(): FallableQueryResultDto
    {
        try {
            $resultDto = $this->questionDbRunnerService->getAnswerResult($this->question);

            return (new FallableQueryResultDto())->setResult($resultDto);
        } catch (\Throwable $e) {
            $errorMessage = t('challenge.errors.answer-query-failure', [
                '%error%' => $e->getMessage(),
            ]);

            return (new FallableQueryResultDto())->setErrorMessage($errorMessage);
        }
    }
}
