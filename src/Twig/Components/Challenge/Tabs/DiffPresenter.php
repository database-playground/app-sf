<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Tabs;

use App\Entity\Question;
use App\Entity\User;
use App\Repository\SolutionEventRepository;
use App\Service\QuestionDbRunnerService;
use jblond\Diff;
use jblond\Diff\Renderer\Html\SideBySide;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsLiveComponent]
final class DiffPresenter
{
    use DefaultActionTrait;

    public function __construct(
        private readonly QuestionDbRunnerService $questionDbRunnerService,
        private readonly SolutionEventRepository $solutionEventRepository,
        private readonly TranslatorInterface $translator,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[LiveProp]
    public Question $question;

    #[LiveProp]
    public User $user;

    #[LiveProp(writable: true)]
    public ?string $query = null;

    #[PostMount]
    public function postMount(): void
    {
        $this->query = $this->solutionEventRepository->getLatestQuery($this->question, $this->user)?->getQuery();
    }

    public function getAnswerResult(): ?string
    {
        try {
            $resultDto = $this->questionDbRunnerService->getAnswerResult($this->question);

            return $this->serializer->serialize($resultDto->getResult(), 'csv', [
                'csv_delimiter' => "\t",
                'csv_enclosure' => ' ',
            ]);
        } catch (\Throwable $e) {
            $this->logger->debug('Failed to get the answer result', [
                'exception' => $e,
            ]);

            return null;
        }
    }

    public function getUserResult(): ?string
    {
        if (null === $this->query) {
            return null;
        }

        try {
            $resultDto = $this->questionDbRunnerService->getQueryResult($this->question, $this->query);

            return $this->serializer->serialize($resultDto->getResult(), 'csv', [
                'csv_delimiter' => "\t",
                'csv_enclosure' => ' ',
            ]);
        } catch (\Throwable $e) {
            $this->logger->debug('Failed to get the user result', [
                'exception' => $e,
            ]);

            return null;
        }
    }

    /**
     * @return ?string The HTML string of the diff.
     *                 "" if the diff is empty.
     *                 Null if the diff cannot be calculated, for example, no results.
     */
    public function getDiff(): ?string
    {
        $leftQueryResult = $this->getUserResult();
        $rightQueryResult = $this->getAnswerResult();

        if (null === $leftQueryResult || null === $rightQueryResult) {
            return null;
        }

        $diff = new Diff(explode("\n", $leftQueryResult), explode("\n", $rightQueryResult));
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
