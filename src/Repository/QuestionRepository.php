<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Meilisearch\Bundle\SearchService;

/**
 * @extends ServiceEntityRepository<Question>
 */
class QuestionRepository extends ServiceEntityRepository
{
    public static int $pageSize = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function getNextPage(int $page): ?int
    {
        try {
            /**
             * @var int $id
             */
            $id = $this->createQueryBuilder('q')
                ->select('q.id')
                ->where('q.id > :page')
                ->orderBy('q.id', 'ASC')
                ->setParameter('page', $page)
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

            return $id;
        } catch (NoResultException) {
            return null;
        }
    }

    public function getPreviousPage(int $page): ?int
    {
        try {
            /**
             * @var int $id
             */
            $id = $this->createQueryBuilder('q')
                ->select('q.id')
                ->where('q.id < :page')
                ->orderBy('q.id', 'DESC')
                ->setParameter('page', $page)
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult();

            return $id;
        } catch (NoResultException) {
            return null;
        }
    }

    /**
     * Search for questions based on a query and page number.
     *
     * @param string|null $query    The search query
     * @param string|null $type     The question type
     * @param int         $page     The page number
     * @param int|null    $pageSize The number of items per page
     *
     * @return Question[] The list of questions for the given page
     */
    public function search(SearchService $searchService, ?string $query, ?string $type, int $page, ?int $pageSize): array
    {
        $filters = [];
        if ($type) {
            $escapedType = addslashes($type);
            $filters[] = "type = \"{$escapedType}\"";
        }

        return $searchService->search($this->getEntityManager(), Question::class, $query ?: '', [
            'limit' => $pageSize ?? self::$pageSize,
            'page' => $page,
            'filter' => $filters,
            'sort' => ['id:asc'],
        ]);
    }

    /**
     * Reindex all the questions.
     */
    public function reindex(SearchService $searchService): void
    {
        $searchService->index($this->getEntityManager(), $this->findAll());
    }

    /**
     * Calculate the total number of pages for a given query and page size.
     *
     * @param string|null $query    The search query
     * @param string|null $type     The question type
     * @param int|null    $pageSize The number of items per page
     *
     * @return int The total number of pages
     */
    public function calculateTotalPages(?string $query, ?string $type, ?int $pageSize): int
    {
        // FIXME: This is a naive implementation, it should be optimized
        $pageSize ??= self::$pageSize;

        $qb = $this->createQueryBuilder('q');
        $qb = $qb->select('COUNT(q.id)');

        if ($query) {
            $qb = $qb->andWhere('q.title LIKE :query')
                ->setParameter('query', "%$query%");
        }

        if ($type) {
            $qb = $qb->andWhere('q.type = :type')
                ->setParameter('type', $type);
        }

        $questionsCount = $qb->getQuery()->getSingleScalarResult();
        \assert(\is_int($questionsCount), 'The questions count should be an integer.');

        return (int) ceil($questionsCount / $pageSize);
    }

    /**
     * Get the list of types.
     *
     * @return string[]
     */
    public function listTypes(): array
    {
        $qb = $this->createQueryBuilder('q');
        $qb = $qb->select('q.type')
            ->distinct();

        /** @var string[] $result */
        $result = $qb->getQuery()->getSingleColumnResult();

        return $result;
    }
}
