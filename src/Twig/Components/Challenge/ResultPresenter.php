<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

require_once __DIR__.'/EventConstant.php';

use Psr\Log\LoggerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ResultPresenter
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?Payload $userPayload;

    public function __construct(
        public LoggerInterface $logger,
    ) {
        $this->userPayload = null;
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
    public function onQueryCompleted(#[LiveArg] array $result): void
    {
        $this->userPayload = Payload::fromResult($result);
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
