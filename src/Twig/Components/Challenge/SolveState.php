<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum SolveState: string implements TranslatableInterface
{
    case Solved = 'solved';
    case Failed = 'failed';
    case NotSolved = 'not-solved';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans("challenge.solve-state.$this->value", locale: $locale);
    }
}
