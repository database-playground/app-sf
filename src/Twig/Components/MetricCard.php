<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class MetricCard
{
    /**
     * @var string The title of the card
     */
    public string $title;

    /**
     * @var string The property of the card
     *             Can be one of "neutral", "positive", or "negative"
     */
    public string $property = 'neutral';

    public function getPropertyClassName(): string
    {
        return match ($this->property) {
            'neutral' => 'metric-card--is-neutral',
            'positive' => 'metric-card--is-positive',
            'negative' => 'metric-card--is-negative',
            default => throw new \InvalidArgumentException("Invalid property: {$this->property}"),
        };
    }
}
