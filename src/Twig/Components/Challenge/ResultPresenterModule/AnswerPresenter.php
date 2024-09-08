<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\ResultPresenterModule;

use App\Twig\Components\Challenge\ErrorPayload;
use App\Twig\Components\Challenge\Payload;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class AnswerPresenter
{
    public ?Payload $payload = null;

    /**
     * @return array<string, array<string, mixed>>|null
     */
    public function getResult(): ?array
    {
        return $this->payload?->getResult();
    }

    public function getError(): ?ErrorPayload
    {
        return $this->payload?->getError();
    }

    public function isLoading(): bool
    {
        return $this->payload?->isLoading() ?? false;
    }
}
