<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum EmailKind: string implements TranslatableInterface
{
    case Transactional = 'transactional';
    case Marketing = 'marketing';
    case Test = 'test';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return match ($this) {
            self::Transactional => $translator->trans('email-kind.transactional', locale: $locale),
            self::Marketing => $translator->trans('email-kind.marketing', locale: $locale),
            self::Test => $translator->trans('email-kind.test', locale: $locale),
        };
    }
}
