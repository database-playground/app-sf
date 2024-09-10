<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum SolutionEventStatus: string implements TranslatableInterface
{
    case Unspecified = 'UNSPECIFIED';
    case Failed = 'FAILED';
    case Passed = 'PASSED';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return match ($this) {
            self::Unspecified => $translator->trans('solution_event_status.unspecified', locale: $locale),
            self::Failed => $translator->trans('solution_event_status.failed', locale: $locale),
            self::Passed => $translator->trans('solution_event_status.passed', locale: $locale),
        };
    }
}
