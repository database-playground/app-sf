<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Tabs;

use App\Entity\ChallengeDto\FallableQueryResultDto;
use App\Entity\Question;
use App\Entity\User;
use App\Repository\SolutionEventRepository;
use App\Service\DbRunnerComparer;
use App\Service\QuestionDbRunnerService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

use function Symfony\Component\Translation\t;

#[AsLiveComponent]
final class UserQueryResult
{
    use DefaultActionTrait;

    public function __construct(
        private readonly QuestionDbRunnerService $questionDbRunnerService,
        private readonly SolutionEventRepository $solutionEventRepository,
    ) {
    }

    /**
     * @var Question $question the question to present the answer
     */
    #[LiveProp]
    public Question $question;

    /**
     * @var User $user the user to get the latest query result from
     */
    #[LiveProp]
    public User $user;

    #[LiveProp(writable: true)]
    public ?string $query = null;

    #[PostMount]
    public function postMount(): void
    {
        $this->query = $this->solutionEventRepository->getLatestQuery($this->question, $this->user)?->getQuery();
    }

    public function getResult(): ?FallableQueryResultDto
    {
        if (null === $this->query) {
            return null;
        }

        try {
            $answerResultDto = $this->questionDbRunnerService->getAnswerResult($this->question);
        } catch (\Throwable $e) {
            $errorMessage = t('challenge.errors.answer-query-failure', [
                '%error%' => $e->getMessage(),
            ]);

            return (new FallableQueryResultDto())->setErrorMessage($errorMessage);
        }

        try {
            $resultDto = $this->questionDbRunnerService->getQueryResult($this->question, $this->query);
        } catch (\Throwable $e) {
            $errorMessage = t('challenge.errors.user-query-error', [
                '%error%' => $e->getMessage(),
            ]);

            return (new FallableQueryResultDto())->setErrorMessage($errorMessage);
        }

        // compare the result
        $compareResult = DbRunnerComparer::compare($answerResultDto, $resultDto);
        if ($compareResult->correct()) {
            return (new FallableQueryResultDto())->setResult($resultDto);
        }

        $errorMessage = t('challenge.errors.user-query-failure', [
            '%error%' => $compareResult->reason(),
        ]);

        return (new FallableQueryResultDto())->setResult($resultDto)->setErrorMessage($errorMessage);
    }

    #[LiveListener('app:challenge-executor:query-created')]
    public function onQueryUpdated(#[LiveArg] string $query): void
    {
        $this->query = $query;
    }
}
