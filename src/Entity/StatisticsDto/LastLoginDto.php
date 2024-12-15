<?php

declare(strict_types=1);

namespace App\Entity\StatisticsDto;

use App\Entity\User;

class LastLoginDto
{
    private User $user;
    private ?\DateTimeImmutable $lastLoginAt = null;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the last login time.
     */
    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): static
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    /**
     * Get the interval of the last login time and now.
     */
    public function getRecency(): ?\DateInterval
    {
        return $this->lastLoginAt?->diff(new \DateTimeImmutable());
    }
}
