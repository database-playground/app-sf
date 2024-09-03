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
    public static int $PAGE_SIZE = 15;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    /**
     * Search for questions based on a query and page number.
     *
     * @param string|null $query    The search query
     * @param string|null $type     The question type
     * @param int         $page     The page number
     * @param int|null    $pageSize The number of items per page
     *
     * @return array<Question> The list of questions for the given page
     */
    public function search(?string $query, ?string $type, int $page, ?int $pageSize): array
    {
        $pageSize ??= self::$PAGE_SIZE;
        $qb = $this->createQueryBuilder('q');

        if ($query) {
            $qb = $qb->andWhere('q.title LIKE :query')
                ->setParameter('query', "%$query%");
        }

        if ($type) {
            $qb = $qb->andWhere('q.type = :type')
                ->setParameter('type', $type);
        }

        return $qb->orderBy('q.id')
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize)
            ->getQuery()->getResult();
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
        $pageSize ??= self::$PAGE_SIZE;

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

        return (int) ceil($questionsCount / $pageSize);
    }

    /**
     * Get the list of types.
     *
     * @return array<string>
     */
    public function listTypes(): array
    {
        $qb = $this->createQueryBuilder('q');
        $qb = $qb->select('q.type')
            ->distinct();

        return $qb->getQuery()->getSingleColumnResult();
    }
}
