<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Announcement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Announcement>
 */
class AnnouncementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Announcement::class);
    }

    /**
     * @return list<Announcement>
     */
    public function findAllPublished(): array
    {
        return $this->findBy(['published' => true], orderBy: [
            'createdAt' => 'DESC',
        ]);
    }
}
