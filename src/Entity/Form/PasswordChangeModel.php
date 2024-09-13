<?php

declare(strict_types=1);

namespace App\Entity\Form;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The plain old PHP object for the change password form.
 */
class PasswordChangeModel
{
    #[SecurityAssert\UserPassword]
    private string $oldPassword;
    #[Assert\NotBlank]
    #[Assert\PasswordStrength]
    private string $newPassword;
    #[Assert\EqualTo(propertyPath: 'newPassword', message: '兩次密碼必須相同。')]
    private string $confirmPassword;

    public function getOldPassword(): string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(string $oldPassword): void
    {
        $this->oldPassword = $oldPassword;
    }

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): void
    {
        $this->newPassword = $newPassword;
    }

    public function getConfirmPassword(): string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(string $confirmPassword): void
    {
        $this->confirmPassword = $confirmPassword;
    }

    public function getHashedPassword(UserPasswordHasherInterface $passwordHasher, PasswordAuthenticatedUserInterface $userInterface): string
    {
        return $passwordHasher->hashPassword($userInterface, $this->newPassword);
    }
}
