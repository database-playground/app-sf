<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\ResultPresenterModule;

use App\Twig\Components\Challenge\Payload;
use App\Utils\TablePrinter;
use jblond\Diff;
use jblond\Diff\Renderer\Html\SideBySide;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class DiffPresenter
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?Payload $userPayload;

    #[LiveProp]
    public Payload $answerPayload;

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @return ?string The HTML string of the diff.
     *                 Null if the diff cannot be calculated, for example, no results.
     */
    public function getDiff(): ?string
    {
        if (!$this->answerPayload->result || !$this->userPayload?->result) {
            return null;
        }

        $left = TablePrinter::toStringTable($this->answerPayload->result->queryResult);
        $right = TablePrinter::toStringTable($this->userPayload->result->queryResult);

        $diff = new Diff(explode("\n", $left), explode("\n", $right));
        $renderer = new SideBySide([
            'title1' => $this->translator->trans('diff.answer'),
            'title2' => $this->translator->trans('diff.yours'),
        ]);

        $result = $diff->render($renderer);
        \assert(\is_string($result));

        return $result;
    }
}
