<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Instruction;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
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
    public function onHintReceived(LoggerInterface $logger, #[LiveArg] #[MapRequestPayload] HintPayload $hint): void
    {
        $logger->debug('Received hint', ['hint' => $hint]);
        $this->hint = $hint;
    }
}
