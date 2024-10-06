<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum FeedbackType: string implements TranslatableInterface
{
    case Bugs = 'bugs';
    case Improvements = 'improvements';
    case Others = 'others';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return match ($this) {
            self::Bugs => $translator->trans('feedback.type.bugs', locale: $locale),
            self::Improvements => $translator->trans('feedback.type.improvements', locale: $locale),
            self::Others => $translator->trans('feedback.type.others', locale: $locale),
        };
    }
}
