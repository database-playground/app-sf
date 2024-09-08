<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

class Payload
{
    /**
     * @var array<array<string, mixed>>|null $result
     */
    #[LiveProp]
    public ?array $result = null;

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
    public static function fromResult(array $result): self
    {
        $self = new self();
        $self->result = $result;

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

    /**
     * @return array<array<string, mixed>>|null
     */
    public function getResult(): ?array
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
            self::USER_ERROR => $translator->trans('challenge.error-type.user', [], null, $locale),
            self::SERVER_ERROR => $translator->trans('challenge.error-type.server', [], null, $locale),
        };
    }
}
