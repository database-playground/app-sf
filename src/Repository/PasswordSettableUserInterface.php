<?php

declare(strict_types=1);

namespace App\Repository;

interface PasswordSettableUserInterface
{
    public function setPassword(string $password): void;
}
