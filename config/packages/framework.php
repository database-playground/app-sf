<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\Framework\SessionConfig;
use Symfony\Config\FrameworkConfig;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return static function (ContainerConfigurator $containerConfigurator, FrameworkConfig $frameworkConfig): void {
    $sessionConfig = $frameworkConfig->session();
    assert($sessionConfig instanceof SessionConfig);

    $frameworkConfig->secret(env('APP_SECRET'));

    // Note that the session will be started ONLY if you read or write from it.
    $sessionConfig->handlerId(env('REDIS_URI'));

    // proxy configuration for Zeabur
    $frameworkConfig->trustedProxies('private_ranges');
    $frameworkConfig->trustedHeaders([
        'x-forwarded-for',
        'x-forwarded-host',
        'x-forwarded-proto',
        'x-forwarded-port',
        'x-forwarded-prefix',
    ]);

    if ('test' === $containerConfigurator->env()) {
        $frameworkConfig->test(true);
        $sessionConfig->storageFactoryId('session.storage.factory.mock_file');
    }
};
