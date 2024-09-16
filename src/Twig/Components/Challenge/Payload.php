<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use Symfony\UX\LiveComponent\Attribute\LiveProp;

class Payload
{
    #[LiveProp]
    public ?Payload\ResultPayload $result = null;

    #[LiveProp]
    public ?Payload\ErrorPayload $error = null;

    #[LiveProp]
    public bool $loading = false;

    public static function loading(): self
    {
        $self = new self();
        $self->loading = true;

        return $self;
    }

    /**
     * @param array<array<string, mixed>> $result
     */
    public static function fromResult(array $result, bool $same = false, bool $answer = false): self
    {
        $self = new self();
        $self->result = new Payload\ResultPayload();
        $self->result->queryResult = $result;
        $self->result->same = $same;
        $self->result->answer = $answer;

        return $self;
    }

    public static function fromError(Payload\ErrorProperty $property, string $message): self
    {
        $self = new self();
        $self->error = new Payload\ErrorPayload();
        $self->error->property = $property;
        $self->error->message = $message;

        return $self;
    }

    public function getResult(): ?Payload\ResultPayload
    {
        return $this->result;
    }

    public function getError(): ?Payload\ErrorPayload
    {
        return $this->error;
    }

    public function isLoading(): bool
    {
        return $this->loading;
    }
}
