<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\ResultPresenterModule;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Table
{
    /**
     * @var array<array<string, mixed>>
     */
    public array $result;

    /**
     * @return array<string>
     */
    public function getRows(): array
    {
        if (!$this->result) {
            return [];
        }

        return array_keys($this->result[0]);
    }
}
