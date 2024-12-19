<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Tabs;

use App\Entity\Question;
use App\Entity\User;
use App\Repository\SolutionEventRepository;
use App\Service\QuestionSqlRunnerService;
use jblond\Diff;
use jblond\Diff\Renderer\Html\SideBySide;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsLiveComponent]
final class DiffPresenter
{
    use DefaultActionTrait;

    #[LiveProp]
    public Question $question;

    #[LiveProp]
    public User $user;

    #[LiveProp(writable: true)]
    public ?string $query = null;

    public function __construct(
        private readonly QuestionSqlRunnerService $questionSqlRunnerService,
        private readonly SolutionEventRepository $solutionEventRepository,
        private readonly TranslatorInterface $translator,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
    ) {}

    #[PostMount]
    public function postMount(): void
    {
        $this->query = $this->solutionEventRepository->getLatestQuery($this->question, $this->user)?->getQuery();
    }

    public function getAnswerResult(): ?string
    {
        try {
            $resultDto = $this->questionSqlRunnerService->getAnswerResult($this->question);

            $columnsAndRows = [$resultDto->getColumns(), ...$resultDto->getRows()];

            return $this->serializer->serialize($columnsAndRows, 'csv', [
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
            $resultDto = $this->questionSqlRunnerService->getQueryResult($this->question, $this->query);

            $columnsAndRows = [$resultDto->getColumns(), ...$resultDto->getRows()];

            return $this->serializer->serialize($columnsAndRows, 'csv', [
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
            'title1' => $this->translator->trans('diff.yours'),
            'title2' => $this->translator->trans('diff.answer'),
        ]);

        $result = $diff->render($renderer);
        if (null === $result || false === $result) {
            return '';
        }

        \assert(\is_string($result));

        return $result;
    }

    #[LiveListener('app:challenge-executor:query-created')]
    public function onQueryUpdated(#[LiveArg] string $query): void
    {
        $this->query = $query;
    }
}
