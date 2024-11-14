<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Instruction;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Content
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?HintPayload $hint = null;

    #[LiveListener('app:challenge-hint')]
    public function onHintReceived(SerializerInterface $serializer, #[LiveArg] string $hint): void
    {
        $deserializedHint = $serializer->deserialize($hint, HintPayload::class, 'json');
        $this->hint = $deserializedHint;
    }
}
