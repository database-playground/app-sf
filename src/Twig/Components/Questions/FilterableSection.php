<?php

declare(strict_types=1);

namespace App\Twig\Components\Questions;

use App\Repository\QuestionRepository;
use App\Service\CacheService;
use Monolog\Logger;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
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
        public QuestionRepository $questionRepository,
        public CacheService $cacheService,
        public LoggerInterface $logger,
    ) {
        $this->pageSize = QuestionRepository::$PAGE_SIZE;
    }

    /**
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function getQuestions(): array
    {
        $cache = $this->cacheService->getItem("questions.{$this->query}.p{$this->currentPage}");
        if (!$cache->isHit()) {
            $this->cacheService->markQuestionCache($cache);
            $cache->set(
                $this->questionRepository->search(
                    query: $this->query,
                    page: $this->currentPage,
                    pageSize: $this->pageSize,
                )
            );
            $this->cacheService->save($cache);
        }

        return $cache->get();
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function hasMore(): bool
    {
        $cache = $this->cacheService->getItem("questions.{$this->query}.pages");
        if (!$cache->isHit()) {
            $this->cacheService->markQuestionCache($cache);
            $cache->set($this->questionRepository->pages(query: $this->query, pageSize: $this->pageSize));
            $this->cacheService->save($cache);
        }

        return $this->currentPage < $cache->get();
    }

    #[LiveAction]
    public function nextPage(): void
    {
        $this->currentPage++;
    }
}
