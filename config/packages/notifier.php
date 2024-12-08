<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\FrameworkConfig;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return static function (ContainerConfigurator $containerConfigurator, FrameworkConfig $frameworkConfig): void {
    $notifierConfig = $frameworkConfig->notifier();

    $notifierConfig->chatterTransport('linenotify', env('LINE_NOTIFY_DSN'));
    $notifierConfig->adminRecipient()->email('dbplay@pan93.com');
};
