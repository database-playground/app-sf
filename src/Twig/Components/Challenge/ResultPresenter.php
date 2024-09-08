<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

require_once __DIR__.'/EventConstant.php';

use App\Entity\Question;
use App\Service\QuestionDbRunnerService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ResultPresenter
{
    use DefaultActionTrait;

    /**
     * The available tabs.
     *
     * @var string[]
     */
    public array $tabs = [
        'result', 'answer',
    ];

    /**
     * @var Question $question the question to present the answer
     */
    #[LiveProp]
    public Question $question;

    /**
     * @var string $currentTab the current active tab
     */
    #[LiveProp(writable: true)]
    public string $currentTab = 'result';

    #[LiveProp]
    public ?Payload $userPayload;

    public function __construct(
        public QuestionDbRunnerService $questionDbRunnerService,
    ) {
        $this->userPayload = null;
    }

    /**
     * Get the wrapped payload of the answer.
     */
    public function getAnswerPayload(): ?Payload
    {
        try {
            $answer = $this->questionDbRunnerService->getAnswerResult($this->question);
        } catch (\Throwable $e) {
            return Payload::fromError(ErrorProperty::fromCode(500), $e->getMessage());
        }

        return Payload::fromResult($answer, answer: true);
    }

    /**
     * Trigger when the query is pending.
     */
    #[LiveListener(QueryPendingEvent)]
    public function onQueryPending(): void
    {
        $this->userPayload = Payload::loading();
    }

    /**
     * Trigger when the query is completed.
     *
     * @param array<array<string, mixed>> $result
     */
    #[LiveListener(QueryCompletedEvent)]
    public function onQueryCompleted(#[LiveArg] array $result, #[LiveArg] bool $same): void
    {
        $this->userPayload = Payload::fromResult($result, same: $same);
    }

    /**
     * Trigger when the query is failed.
     *
     * @param string $error
     * @param int    $code
     */
    #[LiveListener(QueryFailedEvent)]
    public function onQueryFailed(#[LiveArg] string $error, #[LiveArg] int $code): void
    {
        $this->userPayload = Payload::fromError(ErrorProperty::fromCode($code), $error);
    }
}
