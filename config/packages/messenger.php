<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Config\Framework\Messenger\RoutingConfig;
use Symfony\Config\Framework\Messenger\TransportConfig;
use Symfony\Config\FrameworkConfig;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return static function (ContainerConfigurator $containerConfigurator, FrameworkConfig $frameworkConfig): void {
    $messenger = $frameworkConfig->messenger();

    $messenger->failureTransport('failed');

    $asyncTransport = $messenger->transport('async');
    assert($asyncTransport instanceof TransportConfig);
    $asyncTransport->dsn(env('MESSENGER_TRANSPORT_DSN'));
    $asyncTransport->retryStrategy()
        ->maxRetries(3)
        ->multiplier(2);

    $failedTransport = $messenger->transport('failed');
    assert($failedTransport instanceof TransportConfig);
    $failedTransport->dsn('doctrine://default?queue_name=failed');

    $messenger->bus('messenger.bus.default');
    $messenger->defaultBus('messenger.bus.default');

    $sendEmailMessageRoutingConfig = $messenger->routing(SendEmailMessage::class);
    assert($sendEmailMessageRoutingConfig instanceof RoutingConfig);
    $sendEmailMessageRoutingConfig->senders(['async']);

    $notifierRoutingConfig = $messenger->routing(ChatMessage::class);
    assert($notifierRoutingConfig instanceof RoutingConfig);
    $notifierRoutingConfig->senders(['async']);
};
