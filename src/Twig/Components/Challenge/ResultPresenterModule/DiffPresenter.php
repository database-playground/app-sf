<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\ResultPresenterModule;

use App\Twig\Components\Challenge\Payload;
use App\Utils\TablePrinter;
use jblond\Diff;
use jblond\Diff\Renderer\Html\SideBySide;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class DiffPresenter
{
    public ?Payload $userPayload;
    public Payload $answerPayload;

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @return ?string The HTML string of the diff.
     *                 "" if the diff is empty.
     *                 Null if the diff cannot be calculated, for example, no results.
     */
    public function getDiff(): ?string
    {
        $leftQueryResult = $this->answerPayload->getResult()?->getQueryResult();
        $rightQueryResult = $this->userPayload?->getResult()?->getQueryResult();

        if (!$leftQueryResult || !$rightQueryResult) {
            return null;
        }

        $left = TablePrinter::toStringTable($leftQueryResult);
        $right = TablePrinter::toStringTable($rightQueryResult);

        $diff = new Diff(explode("\n", $left), explode("\n", $right));
        $renderer = new SideBySide([
            'title1' => $this->translator->trans('diff.answer'),
            'title2' => $this->translator->trans('diff.yours'),
        ]);

        $result = $diff->render($renderer);
        if (!$result) {
            return '';
        }

        \assert(\is_string($result));

        return $result;
    }
}
