<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EmailEvent;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailEvent>
 */
class EmailEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailEvent::class);
    }

    /**
     * Find the email target to the user.
     *
     * @param User $user The user to find the email target
     *
     * @return list<EmailEvent>
     */
    public function findBySendTarget(User $user): array
    {
        return $this->findBy([
            'toUser' => $user,
        ], orderBy: [
            'createdAt' => 'DESC',
        ]);
    }
}
