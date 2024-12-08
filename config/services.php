<?php

declare(strict_types=1);

use App\Service\PromptService;
use App\Service\SqlRunnerService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()
        ->set('app.sqlrunner_url', env('SQLRUNNER_URL'))
        ->set('app.redis_uri', env('REDIS_URI'))
        ->set('app.openai_api_key', env('OPENAI_API_KEY'))
        ->set('app.features.hint', true)
        ->set('app.features.editable-profile', true)
        ->set('app.features.comment', true);

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\', __DIR__.'/../src/')
        ->exclude([
            __DIR__.'/../src/DependencyInjection/',
            __DIR__.'/../src/Entity/',
            __DIR__.'/../src/Kernel.php',
            __DIR__.'/../src/Service/Processes/',
            __DIR__.'/../src/Service/Types/',
            __DIR__.'/../src/Twig/Components/Challenge/EventConstant.php',
        ]);

    $services->set(PromptService::class)
        ->arg('$apiKey', '%app.openai_api_key%');

    $services->set(SqlRunnerService::class)
        ->arg('$baseUrl', '%app.sqlrunner_url%');
};
