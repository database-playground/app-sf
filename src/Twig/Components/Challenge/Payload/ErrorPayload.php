<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Payload;

class ErrorPayload
{
    private ErrorProperty $property;
    private string $message;

    public function getProperty(): ErrorProperty
    {
        return $this->property;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setProperty(ErrorProperty $property): self
    {
        $this->property = $property;

        return $this;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
