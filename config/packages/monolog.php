<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\MonologConfig;

return static function (ContainerConfigurator $containerConfigurator, MonologConfig $monologConfig): void {
    // Deprecations are logged in the dedicated "deprecation" channel when it exists
    $monologConfig->channels(['deprecation']);

    if ('dev' === $containerConfigurator->env()) {
        $monologConfig->handler('main')
            ->type('stream')
            ->path('%kernel.logs_dir%/%kernel.environment%.log')
            ->level('debug')
            ->channels()
            ->elements(['!event'])
        ;
        $monologConfig->handler('console')
            ->type('console')
            ->processPsr3Messages(false)
            ->channels()
            ->elements(['!event', '!doctrine', '!console'])
        ;
    }

    if ('test' === $containerConfigurator->env()) {
        $monologConfig->handler('main')
            ->type('fingers_crossed')
            ->actionLevel('error')
            ->handler('nested')
            ->excludedHttpCode(404)
            ->excludedHttpCode(405)
            ->channels()
            ->elements(['!event'])
        ;
        $monologConfig->handler('nested')
            ->type('stream')
            ->path('%kernel.logs_dir%/%kernel.environment%.log')
            ->level('debug')
        ;
    }

    if ('prod' === $containerConfigurator->env()) {
        $monologConfig->handler('main')
            ->type('fingers_crossed')
            ->actionLevel('error')
            ->handler('nested')
            ->excludedHttpCode(404)
            ->excludedHttpCode(405)
            // How many messages should be saved? Prevent memory leaks
            ->bufferSize(50)
        ;
        $monologConfig->handler('nested')
            ->type('stream')
            ->path('php://stderr')
            ->level('debug')
            ->formatter('monolog.formatter.json')
        ;
        $monologConfig->handler('console')
            ->type('console')
            ->processPsr3Messages(false)
            ->channels()
            ->elements(['!event', '!doctrine'])
        ;
        $monologConfig->handler('deprecation')
            ->type('stream')
            ->path('php://stderr')
            ->formatter('monolog.formatter.json')
            ->channels()
            ->elements(['deprecation'])
        ;
    }
};
