<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Twig\Runtime\QuotedListExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class QuotedListExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'joinToQuoted',
                [QuotedListExtensionRuntime::class, 'joinToQuoted'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'joinToQuoted',
                [QuotedListExtensionRuntime::class, 'joinToQuoted'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
