<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\Framework\RouterConfig;
use Symfony\Config\FrameworkConfig;

return static function (ContainerConfigurator $containerConfigurator, FrameworkConfig $frameworkConfig): void {
    $routerConfig = $frameworkConfig->router();
    assert($routerConfig instanceof RouterConfig);  // workaround for PHPStan support

    $routerConfig->strictRequirements(true);

    if ('prod' === $containerConfigurator->env()) {
        $routerConfig->strictRequirements(null);
    }
};
