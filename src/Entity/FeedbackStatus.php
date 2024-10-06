<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum FeedbackStatus: string implements TranslatableInterface
{
    case Backlog = 'backlog';
    case New = 'new';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return match ($this) {
            self::Backlog => $translator->trans('feedback.status.backlog', locale: $locale),
            self::New => $translator->trans('feedback.status.new', locale: $locale),
            self::InProgress => $translator->trans('feedback.status.in_progress', locale: $locale),
            self::Resolved => $translator->trans('feedback.status.resolved', locale: $locale),
            self::Closed => $translator->trans('feedback.status.closed', locale: $locale),
        };
    }
}
