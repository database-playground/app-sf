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
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;

#[AsLiveComponent]
final class FilterableSection
{
    use DefaultActionTrait;

    static public int $PAGE_SIZE = 15;

    public string $title = "題庫一覽";

    #[LiveProp(writable: true, url: new UrlMapping(as: 'q'))]
    public string $query = '';

    #[LiveProp(writable: true, url: new UrlMapping(as: 'p'))]
    public int $currentPage = 1;

    public function __construct(
        public QuestionRepository $questionRepository,
        public CacheService $cacheService,
        public LoggerInterface $logger,
    ) {}

    /**
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function getQuestions(): array
    {
        $cache = $this->cacheService->getItem("questions.{$this->query}.p{$this->currentPage}l".self::$PAGE_SIZE);
        if (!$cache->isHit()) {
            $this->cacheService->markQuestionCache($cache);
            $cache->set(
                $this->questionRepository->search(
                    query: $this->query,
                    page: $this->currentPage,
                    pageSize: self::$PAGE_SIZE,
                )
            );
            $this->cacheService->save($cache);
        }

        return $cache->get();
    }
}
