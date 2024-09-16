<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Payload;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
