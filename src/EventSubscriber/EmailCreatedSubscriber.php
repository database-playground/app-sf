<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Email as EmailEntity;
use App\Entity\EmailDeliveryEvent;
use App\Entity\EmailKind;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as EmailMessage;

final readonly class EmailCreatedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function onMessageEvent(MessageEvent $event): void
    {
        $message = $event->getMessage();
        if (!($message instanceof EmailMessage)) {
            $this->logger->warning('The message is not an instance of Email.', [
                'message' => $message,
            ]);

            return;
        }

        $subject = $message->getSubject();
        if (!\is_string($subject)) {
            $this->logger->warning('The message does not have a valid subject.', [
                'message' => $message,
                'subject' => $subject,
            ]);

            return;
        }

        $textBody = $message->getTextBody();
        if (!\is_string($textBody)) {
            $this->logger->warning('The message does not have an valid text body.', [
                'message' => $message,
                'body' => $textBody,
            ]);

            return;
        }

        $htmlBody = $message->getHtmlBody();
        if (!\is_string($htmlBody)) {
            $this->logger->warning('The message does not have an valid HTML body.', [
                'message' => $message,
                'body' => $htmlBody,
            ]);

            return;
        }

        try {
            $kind = EmailKind::fromEmailHeader($message->getHeaders());
        } catch (\InvalidArgumentException $exception) {
            $this->logger->warning('The message does not have a valid email kind.', [
                'message' => $message,
                'exception' => $exception,
            ]);

            return;
        }

        $email = (new EmailEntity())
            ->setSubject($subject)
            ->setTextContent($textBody)
            ->setHtmlContent($htmlBody)
            ->setKind($kind);
        $this->entityManager->persist($email);

        /**
         * @var list<Address> $recipients
         */
        $recipients = [
            ...$message->getTo(),
            ...$message->getCc(),
            ...$message->getBcc(),
        ];

        foreach ($recipients as $recipient) {
            $emailDeliveryEvent = (new EmailDeliveryEvent())
                ->setToAddress($recipient->getAddress())
                ->setEmail($email);

            $user = $this->userRepository->findOneBy([
                'email' => $recipient->getAddress(),
            ]);
            if (null !== $user) {
                $emailDeliveryEvent->setToUser($user);
            }

            $this->entityManager->persist($emailDeliveryEvent);
        }

        $this->entityManager->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::class => 'onMessageEvent',
        ];
    }
}
