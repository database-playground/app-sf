<?php

declare(strict_types=1);

namespace App\Twig\Components\Questions;

use App\Repository\QuestionRepository;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
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

    readonly public int $pageSize;

    public string $title = "題庫一覽";

    #[LiveProp(writable: true, url: new UrlMapping(as: 'q'))]
    public string $query = '';

    #[LiveProp(writable: true, url: new UrlMapping(as: 'p'))]
    private int $currentPage = 1;

    public function __construct(
        public QuestionRepository     $questionRepository,
        public TagAwareCacheInterface $cachePool,
        public LoggerInterface        $logger,
    )
    {
        $this->pageSize = QuestionRepository::$PAGE_SIZE;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getQuestions(): array
    {
        $currentPage = $this->getCurrentPage();

        return $this->cachePool->get("questions.{$this->query}.page-{$currentPage}", function ($item) use ($currentPage) {
            $item->tag("questions");

            $result = $this->questionRepository->search(
                query: $this->query,
                page: $currentPage,
                pageSize: $this->pageSize,
            );
            $item->set($result);

            return $result;
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getTotalPages(): int
    {
        return $this->cachePool->get("questions.{$this->query}.pages", function ($item) {
            $item->tag("questions");

            $result = $this->questionRepository->pages(
                query: $this->query,
                pageSize: $this->pageSize,
            );
            $item->set($result);

            return $result;
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getCurrentPage(): int
    {
        return min($this->currentPage, $this->getTotalPages());
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
}
