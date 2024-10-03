<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\LoginEvent;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function onSecurityAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        \assert($user instanceof User);

        $loginEvent = (new LoginEvent())
            ->setAccount($user);

        $this->entityManager->persist($loginEvent);
        $this->entityManager->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'security.authentication.success' => 'onSecurityAuthenticationSuccess',
        ];
    }
}
