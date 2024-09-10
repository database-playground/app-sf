<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

class Payload
{
    #[LiveProp]
    public ?ResultPayload $result = null;

    #[LiveProp]
    public ?ErrorPayload $error = null;

    #[LiveProp]
    public bool $loading = false;

    public static function loading(): self
    {
        $self = new self();
        $self->loading = true;

        return $self;
    }

    /**
     * @param array<array<string, mixed>> $result
     */
    public static function fromResult(array $result, bool $same = false, bool $answer = false): self
    {
        $self = new self();
        $self->result = new ResultPayload();
        $self->result->queryResult = $result;
        $self->result->same = $same;
        $self->result->answer = $answer;

        return $self;
    }

    public static function fromError(ErrorProperty $property, string $message): self
    {
        $self = new self();
        $self->error = new ErrorPayload();
        $self->error->property = $property;
        $self->error->message = $message;

        return $self;
    }

    public function getResult(): ?ResultPayload
    {
        return $this->result;
    }

    public function getError(): ?ErrorPayload
    {
        return $this->error;
    }

    public function isLoading(): bool
    {
        return $this->loading;
    }
}

class ResultPayload
{
    /**
     * The result of the query.
     *
     * @var array<string, array<string, mixed>> $queryResult
     */
    #[LiveProp]
    public array $queryResult;

    /**
     * Indicate if this is same as the answer.
     */
    #[LiveProp]
    public bool $same;

    /**
     * Indicate if this is the answer.
     */
    #[LiveProp]
    public bool $answer;
}

class ErrorPayload
{
    #[LiveProp]
    public ErrorProperty $property;

    #[LiveProp]
    public string $message;
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
            self::USER_ERROR => $translator->trans('challenge.error-type.user', locale: $locale),
            self::SERVER_ERROR => $translator->trans('challenge.error-type.server', locale: $locale),
        };
    }
}
