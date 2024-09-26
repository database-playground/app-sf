<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Instruction;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Content
{
    use DefaultActionTrait;

    #[LiveProp(updateFromParent: true)]
    public ?HintPayload $hint;
}
