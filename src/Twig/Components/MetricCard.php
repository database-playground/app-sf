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

    /**
     * @var string|null The value of the growth.
     *                  If null, the growth will not be displayed.
     */
    public ?string $growth = null;
}
