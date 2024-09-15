<?php

declare(strict_types=1);

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class QuotedListExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
    }

    /**
     * @param array<string> $value
     */
    public function joinToQuoted(array $value, string $separator = ', '): string
    {
        return '<code>'.implode("</code>$separator<code>", $value).'</code>';
    }
}
