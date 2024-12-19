<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Entity\Email as EmailEntity;
use App\Entity\EmailDeliveryEvent as EmailDeliveryEventEntity;
use App\Entity\EmailKind;
use App\Entity\User as UserEntity;
use App\EventSubscriber\EmailCreatedSubscriber;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Email;

/**
 * @internal
 *
 * @coversNothing
 */
final class EmailCreatedSubscriberTest extends TestCase
{
    public function testTransactionalEmail(): void
    {
        $logger = new Logger('test');

        $message = (new Email())
            ->subject('subject')
            ->text('body')
            ->html('<div>body</div')
            ->from('demo-dbplay@example.com')
            ->to('test@example.com')
        ;

        $headers = $message->getHeaders();
        $headers = EmailKind::Test->addToEmailHeader($headers);
        $message->setHeaders($headers);

        $envelope = Envelope::create($message);

        $userRepository = self::createMock(UserRepository::class);
        $userRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => 'test@example.com'])
            ->willReturn(new UserEntity())
        ;

        $invokedCount = self::exactly(2);

        /**
         * @var null|Email $emailInstance
         */
        $emailInstance = null;
        $entityManager = self::createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::exactly(2))
            ->method('persist')
            ->willReturnCallback(static function (mixed ...$parameters) use ($invokedCount, &$emailInstance): void {
                switch ($invokedCount->numberOfInvocations()) {
                    case 1:
                        $email = $parameters[0];
                        \assert($email instanceof EmailEntity);

                        self::assertSame('subject', $email->getSubject());
                        self::assertSame('body', $email->getTextContent());
                        self::assertSame('<div>body</div>', $email->getHtmlContent());
                        self::assertSame(EmailKind::Test, $email->getKind());

                        $emailInstance = $email;

                        break;

                    case 2:
                        $event = $parameters[0];
                        \assert($event instanceof EmailDeliveryEventEntity);

                        self::assertSame('test@example.com', $event->getToAddress());
                        self::assertSame($emailInstance, $event->getEmail());

                        break;
                }
            })
        ;

        $subscriber = new EmailCreatedSubscriber($logger, $userRepository, $entityManager);
        $dispatcher = new EventDispatcher();
        $event = new MessageEvent($message, $envelope, '');

        $dispatcher->addSubscriber($subscriber);
        $dispatcher->dispatch($event);
    }
}
