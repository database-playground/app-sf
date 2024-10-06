<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Controller\Admin\FeedbackCrudController;
use App\Entity\Feedback;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEntityListener(event: Events::postPersist, method: 'onFeedbackCreated', entity: Feedback::class)]
final readonly class FeedbackCreatedListenerSubscriber
{
    public function __construct(
        private NotifierInterface $notifier,
        private TranslatorInterface $translator,
        private AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public function onFeedbackCreated(Feedback $feedback): void
    {
        // FIXME: only optimized for LINE Notify

        $notificationContent = $this->translator->trans('notification.on-feedback-created.content', [
            '%id%' => $feedback->getId(),
            '%account%' => $feedback->getSender()?->getUserIdentifier()
                ?? $this->translator->trans('notification.on-feedback-created.anonymous'),
            '%subject%' => $feedback->getTitle(),
            '%link%' => $this->adminUrlGenerator->unsetAll()
                ->setController(FeedbackCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($feedback->getId())
                ->generateUrl(),
        ]);

        $this->notifier->send((new Notification($notificationContent))->channels(['chat/linenotify']));
    }
}
