<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Tabs;

use App\Entity\ChallengeDto\FallableSqlRunnerResult;
use App\Entity\Question;
use App\Service\QuestionSqlRunnerService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

use function Symfony\Component\Translation\t;

#[AsTwigComponent]
final class AnswerQueryResult
{
    public function __construct(
        private readonly QuestionSqlRunnerService $questionSqlRunnerService,
    ) {
    }

    /**
     * @var Question the question to present the answer
     */
    public Question $question;

    public function getAnswer(): FallableSqlRunnerResult
    {
        try {
            $resultDto = $this->questionSqlRunnerService->getAnswerResult($this->question);

            return (new FallableSqlRunnerResult())->setResult($resultDto);
        } catch (\Throwable $e) {
            $errorMessage = t('challenge.errors.answer-query-failure', [
                '%error%' => $e->getMessage(),
            ]);

            return (new FallableSqlRunnerResult())->setErrorMessage($errorMessage);
        }
    }
}
