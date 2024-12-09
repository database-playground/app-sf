<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The level of a user.
 */
enum Level: string implements TranslatableInterface
{
    case Starter = 'starter';
    case Beginner = 'beginner';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';
    case Expert = 'expert';
    case Master = 'master';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('level.'.$this->value, locale: $locale);
    }

    public static function fromPercent(float $percent): self
    {
        return match (true) {
            $percent < 5 => self::Starter,
            $percent < 20 => self::Beginner,
            $percent < 40 => self::Intermediate,
            $percent < 65 => self::Advanced,
            $percent < 90 => self::Expert,
            default => self::Master,
        };
    }
}
