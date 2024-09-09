<?php

declare(strict_types=1);

namespace App\Twig\Components\Questions;

use App\Repository\QuestionRepository;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;

#[AsLiveComponent]
final class FilterableSection
{
    use DefaultActionTrait;

    public readonly int $pageSize;

    public string $title = '題庫一覽';

    #[LiveProp(writable: true, url: new UrlMapping(as: 'q'))]
    public string $query = '';

    #[LiveProp(writable: true, url: new UrlMapping(as: 'p'))]
    private int $currentPage = 1;

    #[LiveProp(writable: true, url: new UrlMapping(as: 'type'))]
    public string $type = '';

    public function __construct(
        public QuestionRepository $questionRepository,
        public LoggerInterface $logger,
    ) {
        $this->pageSize = QuestionRepository::$PAGE_SIZE;
    }

    /**
     * @return array<\App\Entity\Question>
     *
     * @throws InvalidArgumentException
     */
    public function getQuestions(): array
    {
        return $this->questionRepository->search(
            query: $this->query,
            type: $this->type,
            page: $this->getCurrentPage(),
            pageSize: $this->pageSize,
        );
    }

    /**
     * @return array<string>
     *
     * @throws InvalidArgumentException
     */
    public function getTypesList(): array
    {
        return $this->questionRepository->listTypes();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getTotalPages(): int
    {
        return $this->questionRepository->calculateTotalPages(
            query: $this->query,
            type: $this->type,
            pageSize: $this->pageSize,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getCurrentPage(): int
    {
        return max(min($this->currentPage, $this->getTotalPages()), 1);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setCurrentPage(int $page): void
    {
        $this->currentPage = max(min($page, $this->getTotalPages()), 1);
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function hasNext(): bool
    {
        return $this->getCurrentPage() < $this->getTotalPages();
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function hasPrevious(): bool
    {
        return $this->getCurrentPage() > 1;
    }

    /**
     * @throws InvalidArgumentException
     */
    #[LiveAction]
    public function nextPage(): void
    {
        $this->setCurrentPage($this->getCurrentPage() + 1);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[LiveAction]
    public function previousPage(): void
    {
        $this->setCurrentPage($this->getCurrentPage() - 1);
    }

    #[LiveAction]
    public function setTypeFilter(#[LiveArg] ?string $type): void
    {
        $this->type = $type ?? '';
    }
}
