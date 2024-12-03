<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
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
    /* @phpstan-ignore-next-line argument.type https://github.com/symfony/symfony/issues/18988 */
    $asyncTransport->options([
        'use_notify' => true,
        'check_delayed_interval' => 60000,
    ]);
    $asyncTransport->retryStrategy()
        ->maxRetries(3)
        ->multiplier(2);

    $failedTransport = $messenger->transport('failed');
    assert($failedTransport instanceof TransportConfig);
    $failedTransport->dsn('doctrine://default?queue_name=failed');

    $messenger->bus('messenger.bus.default');
    $messenger->defaultBus('messenger.bus.default');

    $routingConfig = $messenger->routing(SendEmailMessage::class);
    assert($routingConfig instanceof RoutingConfig);
    $routingConfig->senders(['async']);
};
