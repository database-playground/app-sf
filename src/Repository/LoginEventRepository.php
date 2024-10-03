<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\LoginEvent;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LoginEvent>
 */
class LoginEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoginEvent::class);
    }

    public function getLoginCount(User $user): int
    {
        $loginCount = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.account = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        \assert(\is_int($loginCount));

        return $loginCount;
    }
}
