<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Question;
use App\Entity\SolutionVideoEvent;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SolutionVideoEvent>
 */
class SolutionVideoEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SolutionVideoEvent::class);
    }

    /**
     * Find all events this user has triggered.
     *
     * @return SolutionVideoEvent[]
     */
    public function findByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('sve')
            ->distinct()
            ->where('sve.opener = :user')
            ->setParameter('user', $user);

        /**
         * @var SolutionVideoEvent[] $query
         */
        $query = $qb->getQuery()->getResult();

        return $query;
    }

    /**
     * Check if the user has triggered the event.
     */
    public function hasTriggered(User $user, Question $question): bool
    {
        return $this->count([
            'opener' => $user,
            'question' => $question,
        ]) > 0;
    }
}
