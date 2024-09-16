<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Payload;

use Symfony\UX\LiveComponent\Attribute\LiveProp;

class ErrorPayload
{
    #[LiveProp]
    public ErrorProperty $property;

    #[LiveProp]
    public string $message;
}
