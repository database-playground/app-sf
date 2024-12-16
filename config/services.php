<?php

declare(strict_types=1);

use App\Controller\Admin\EmailTemplateController;
use App\Service\EmailService;
use App\Service\EmailTemplateService;
use App\Service\PromptService;
use App\Service\SqlRunnerService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()
        ->set('app.sqlrunner_url', env('SQLRUNNER_URL'))
        ->set('app.redis_uri', env('REDIS_URI'))
        ->set('app.openai_api_key', env('OPENAI_API_KEY'))
        ->set('app.server-mail', env('SERVER_EMAIL'))
        ->set('app.mail.bcc-chunk', 10)
        ->set('app.features.hint', true)
        ->set('app.features.editable-profile', true)
        ->set('app.features.comment', true)
    ;

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
        ->arg('$apiKey', param('app.openai_api_key'));

    $services->set(SqlRunnerService::class)
        ->arg('$baseUrl', param('app.sqlrunner_url'));

    $services->set(EmailService::class)
        ->arg('$serverMail', param('app.server-mail'))
        ->arg('$chunkLimit', param('app.mail.bcc-chunk'));

    $services->set(EmailTemplateService::class)
        ->arg('$serverMail', param('app.server-mail'));

    $services->set(EmailTemplateController::class)
        ->arg('$projectDir', param('kernel.project_dir'));
};
