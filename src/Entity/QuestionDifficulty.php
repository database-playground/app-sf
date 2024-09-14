<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum QuestionDifficulty: string implements TranslatableInterface
{
    case Unspecified = 'UNSPECIFIED';
    case Easy = 'EASY';
    case Medium = 'MEDIUM';
    case Hard = 'HARD';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return match ($this) {
            self::Unspecified => $translator->trans('Unspecified', locale: $locale),
            self::Easy => $translator->trans('Easy', locale: $locale),
            self::Medium => $translator->trans('Medium', locale: $locale),
            self::Hard => $translator->trans('Hard', locale: $locale),
        };
    }

    public static function fromString(string $difficulty): self
    {
        return match ($difficulty) {
            self::Easy->value => self::Easy,
            self::Medium->value => self::Medium,
            self::Hard->value => self::Hard,
            default => self::Unspecified,
        };
    }
}
