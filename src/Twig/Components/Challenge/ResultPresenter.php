<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

require_once __DIR__.'/EventConstant.php';

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ResultPresenter
{
    use DefaultActionTrait;

    protected ResultPayload|ErrorPayload|null $payload = null;

    public function getResult(): ?ResultPayload
    {
        return $this->payload instanceof ResultPayload ? $this->payload : null;
    }

    /**
     * Rows of the result.
     *
     * You should check if there is a result (with {@see getResult()})
     * before calling this method, since it treats `null` as an empty result.
     *
     * @return array<string>
     */
    public function getRows(): array
    {
        $result = $this->getResult()?->result ?? null;
        if (!$result) {
            return [];
        }

        return array_keys($result[0]);
    }

    /**
     * Columns of the result.
     *
     * You should check if there is a result (with {@see getResult()})
     * before calling this method, since it treats `null` as an empty result.
     *
     * @return array<array<string, mixed>>
     */
    public function getColumns(): array
    {
        return $this->getResult()?->result ?? [];
    }

    public function getError(): ?ErrorPayload
    {
        return $this->payload instanceof ErrorPayload ? $this->payload : null;
    }

    /**
     * Trigger when the query is pending.
     */
    #[LiveListener(QueryPendingEvent)]
    public function onQueryPending(): void
    {
        $this->payload = null;
    }

    /**
     * Trigger when the query is completed.
     *
     * @param array<array<string, mixed>> $result
     */
    #[LiveListener(QueryCompletedEvent)]
    public function onQueryCompleted(#[LiveArg] array $result): void
    {
        $this->payload = new ResultPayload($result);
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
        $this->payload = new ErrorPayload(ErrorProperty::fromCode($code), $error);
    }
}

readonly class ResultPayload
{
    /**
     * @var array<array<string, mixed>>
     */
    public array $result;

    /**
     * @param array<array<string, mixed>> $result
     */
    public function __construct(array $result)
    {
        $this->result = $result;
    }
}

readonly class ErrorPayload
{
    public ErrorProperty $property;
    public string $message;

    public function __construct(ErrorProperty $property, string $message)
    {
        $this->property = $property;
        $this->message = $message;
    }
}

enum ErrorProperty: int implements TranslatableInterface
{
    case USER_ERROR = 400;
    case SERVER_ERROR = 500;

    public static function fromCode(int $code): self
    {
        return match ($code) {
            400 => self::USER_ERROR,
            500 => self::SERVER_ERROR,
            default => throw new \InvalidArgumentException("Unknown error code: $code"),
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return match ($this) {
            self::USER_ERROR => $translator->trans('challenge.error-type.user', [], null, $locale),
            self::SERVER_ERROR => $translator->trans('challenge.error-type.server', [], null, $locale),
        };
    }
}
