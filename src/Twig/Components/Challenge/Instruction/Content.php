<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Instruction;

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
    public ?string $type = null;

    #[LiveProp(writable: true)]
    public ?string $hint = null;

    #[LiveListener('app:challenge-hint')]
    public function onHintReceived(#[LiveArg] string $type, #[LiveArg] string $hint): void
    {
        $this->type = $type;
        $this->hint = $hint;
    }
}
