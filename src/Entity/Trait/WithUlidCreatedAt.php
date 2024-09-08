<?php

declare(strict_types=1);

namespace App\Entity\Trait;

trait WithUlidCreatedAt
{
    /**
     * Get the created at date and time of the solution event.
     *
     * @return ?\DateTimeImmutable The parsed DateTime object
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->id?->getDateTime();
    }
}
