<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\ResultPresenterModule;

use App\Twig\Components\Challenge\ErrorPayload;
use App\Twig\Components\Challenge\Payload;
use App\Twig\Components\Challenge\ResultPayload;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class AnswerPresenter
{
    /**
     * @param string $key The key of this presenter.
     *
     * Workaround for the morphing issue:
     * https://symfony.com/bundles/ux-live-component/current/index.html#key-prop
     */
    public string $key;

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
