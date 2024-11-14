<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Tabs;

use App\Entity\ChallengeDto\FallableQueryResultDto;
use App\Entity\Question;
use App\Service\QuestionDbRunnerService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

use function Symfony\Component\Translation\t;

#[AsLiveComponent]
final class AnswerQueryResult
{
    use DefaultActionTrait;

    public function __construct(
        private readonly QuestionDbRunnerService $questionDbRunnerService,
    ) {
    }

    /**
     * @var Question $question the question to present the answer
     */
    #[LiveProp]
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
