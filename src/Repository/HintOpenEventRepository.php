<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\HintOpenEvent;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HintOpenEvent>
 */
class HintOpenEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HintOpenEvent::class);
    }

    /**
     * @return array<HintOpenEvent>
     */
    public function findByUser(User $user): array
    {
        return $this->findBy(['opener' => $user]);
    }
}
