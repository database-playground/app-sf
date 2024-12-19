<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Feedback;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEntityListener(event: Events::postPersist, method: 'onFeedbackCreated', entity: Feedback::class)]
final readonly class FeedbackCreatedListenerSubscriber
{
    public function __construct(
        private NotifierInterface $notifier,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function onFeedbackCreated(Feedback $feedback): void
    {
        // FIXME: only optimized for LINE Notify

        $notificationContent = $this->translator->trans('notification.on-feedback-created.content', [
            '%id%' => $feedback->getId(),
            '%account%' => $feedback->getSender()?->getUserIdentifier()
                ?? $this->translator->trans('notification.on-feedback-created.anonymous'),
            '%subject%' => $feedback->getTitle(),
            '%link%' => $this->urlGenerator->generate('admin_feedback_detail', [
                'entityId' => $feedback->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        $this->notifier->send((new Notification($notificationContent))->channels(['chat/linenotify']));
    }
}
