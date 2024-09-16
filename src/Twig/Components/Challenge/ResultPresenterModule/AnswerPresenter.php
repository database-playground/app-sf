<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\ResultPresenterModule;

use App\Twig\Components\Challenge\Payload;
use App\Twig\Components\Challenge\Payload\ErrorPayload;
use App\Twig\Components\Challenge\Payload\ResultPayload;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class AnswerPresenter
{
    public ?Payload $payload = null;

    public function getResult(): ?ResultPayload
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
