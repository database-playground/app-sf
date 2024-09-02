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

    public function pages(?string $query, ?int $pageSize): int
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
