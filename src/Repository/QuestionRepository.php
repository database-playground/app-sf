<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Meilisearch\Bundle\SearchService;

/**
 * @extends ServiceEntityRepository<Question>
 */
class QuestionRepository extends ServiceEntityRepository
{
    public const int pageSize = 12;

    public function __construct(
        ManagerRegistry $registry,
        private readonly SearchService $searchService,
    ) {
        parent::__construct($registry, Question::class);
    }

    public function getNextPage(int $page): ?int
    {
        try {
            $result = $this->createQueryBuilder('q')
                ->select('q')
                ->where('q.id > :page')
                ->orderBy('q.id', 'ASC')
                ->setParameter('page', $page)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult()
            ;

            \assert(\is_int($result) || null === $result, 'result should be an integer or null');

            return $result;
        } catch (NoResultException) {
            return null;
        }
    }

    public function getPreviousPage(int $page): ?int
    {
        try {
            $result = $this->createQueryBuilder('q')
                ->select('q.id')
                ->where('q.id < :page')
                ->orderBy('q.id', 'DESC')
                ->setParameter('page', $page)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult()
            ;

            \assert(\is_int($result) || null === $result, 'result should be an integer or null');

            return $result;
        } catch (NoResultException) {
            return null;
        }
    }

    /**
     * Search for questions based on a query and page number.
     *
     * @param null|string $query    The search query
     * @param null|string $type     The question type
     * @param int         $page     The page number
     * @param null|int    $pageSize The number of items per page
     *
     * @return Question[] The list of questions for the given page
     */
    public function search(?string $query, ?string $type, int $page, ?int $pageSize = null): array
    {
        $filters = [];
        if (null !== $type && '' !== $type) {
            $escapedType = addslashes($type);
            $filters[] = "type = \"{$escapedType}\"";
        }

        return $this->searchService->search($this->getEntityManager(), Question::class, $query ?? '', [
            'limit' => $pageSize ?? self::pageSize,
            'offset' => ($page - 1) * ($pageSize ?? self::pageSize),
            'filter' => $filters,
            'sort' => ['id:asc'],
        ]);
    }

    /**
     * Reindex all the questions.
     */
    public function reindex(SearchService $searchService): void
    {
        $searchService->clear(Question::class);
        $searchService->index($this->getEntityManager(), $this->findAll());
    }

    /**
     * Count the total search results based on a query and type.
     *
     * @param null|string $query The search query
     * @param null|string $type  The question type
     *
     * @return int The total result count
     */
    public function countSearchResults(?string $query, ?string $type): int
    {
        $filters = [];
        if (null !== $type && '' !== $type) {
            $escapedType = addslashes($type);
            $filters[] = "type = \"{$escapedType}\"";
        }

        $result = $this->searchService->rawSearch(Question::class, $query ?? '', [
            'filter' => $filters,
            'attributesToRetrieve' => ['estimatedTotalHits'],
        ]);

        \assert(isset($result['estimatedTotalHits']) && \is_int($result['estimatedTotalHits']), 'estimatedTotalHits should be set and must be a integer');

        return $result['estimatedTotalHits'];
    }

    /**
     * Get the list of types.
     *
     * @return string[]
     */
    public function listTypes(): array
    {
        $result = $this->createQueryBuilder('q')
            ->select('q.type')
            ->distinct()
            ->getQuery()
            ->getSingleColumnResult()
        ;

        return array_map(static function (mixed $s) {
            \assert(\is_string($s), 'types should be a string');

            return $s;
        }, $result);
    }
}
