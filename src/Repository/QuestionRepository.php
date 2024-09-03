<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Question>
 */
class QuestionRepository extends ServiceEntityRepository
{
    static public int $PAGE_SIZE = 15;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    /**
     * Search for questions based on a query and page number.
     *
     * @param string|null $query The search query
     * @param int $page The page number
     * @param int|null $pageSize The number of items per page
     * @return array<Question> The list of questions for the given page
     */
    public function search(?string $query, int $page, ?int $pageSize): array
    {
        $pageSize = $pageSize ?? self::$PAGE_SIZE;
        $qb = $this->createQueryBuilder('q');

        if ($query) {
            $qb = $qb->andWhere('q.title LIKE :query')
                ->setParameter('query', "%$query%");
        }

        return $qb->orderBy('q.id')
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize)
            ->getQuery()->getResult();
    }

    /**
     * Calculate the total number of pages for a given query and page size.
     *
     * @param string|null $query The search query
     * @param int|null $pageSize The number of items per page
     * @return int The total number of pages
     */
    public function calculateTotalPages(?string $query, ?int $pageSize): int
    {
        // FIXME: This is a naive implementation, it should be optimized
        $pageSize = $pageSize ?? self::$PAGE_SIZE;

        $qb = $this->createQueryBuilder('q');
        $qb = $qb->select('COUNT(q.id)');

        if ($query) {
            $qb = $qb->andWhere('q.title LIKE :query')
                ->setParameter('query', "%$query%");
        }

        $questionsCount = $qb->getQuery()->getSingleScalarResult();

        return (int) ceil($questionsCount / $pageSize);
    }
}
