<?php

declare(strict_types=1);

namespace App\Twig\Components\Questions;

use App\Repository\QuestionRepository;
use App\Service\CacheService;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
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

    #[LiveProp(writable: true)]
    public int $currentPage = 1;

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
        return $this->cachePool->get("questions.{$this->query}.page-{$this->currentPage}", function ($item) {
            $item->tag("questions");

            $result = $this->questionRepository->search(
                query: $this->query,
                page: $this->currentPage,
                pageSize: $this->pageSize,
            );
            $item->set($result);

            return $result;
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function getTotalPages(): int
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
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function hasMore(): bool
    {
        return $this->currentPage < $this->getTotalPages();
    }

    #[LiveAction]
    public function nextPage(): void
    {
        $this->currentPage++;
    }
}
