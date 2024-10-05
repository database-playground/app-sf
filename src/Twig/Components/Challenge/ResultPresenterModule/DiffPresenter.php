<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\ResultPresenterModule;

use App\Twig\Components\Challenge\Payload;
use jblond\Diff;
use jblond\Diff\Renderer\Html\SideBySide;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class DiffPresenter
{
    public ?Payload $userPayload;
    public Payload $answerPayload;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly SerializerInterface $serializer,
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

        if (null === $leftQueryResult || null === $rightQueryResult) {
            return null;
        }

        $left = $this->serializer->serialize($leftQueryResult, 'csv', [
            'csv_delimiter' => "\t",
            'csv_enclosure' => '',
        ]);
        $right = $this->serializer->serialize($rightQueryResult, 'csv', [
            'csv_delimiter' => "\t",
            'csv_enclosure' => '',
        ]);

        $diff = new Diff(explode("\n", $left), explode("\n", $right));
        $renderer = new SideBySide([
            'title1' => $this->translator->trans('diff.answer'),
            'title2' => $this->translator->trans('diff.yours'),
        ]);

        $result = $diff->render($renderer);
        if (null === $result || false === $result) {
            return '';
        }

        \assert(\is_string($result));

        return $result;
    }
}
