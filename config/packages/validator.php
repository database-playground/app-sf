<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\FrameworkConfig;

return static function (ContainerConfigurator $containerConfigurator, FrameworkConfig $frameworkConfig): void {
    $notCompromisedPassword = $frameworkConfig->validation()->notCompromisedPassword();
    $notCompromisedPassword->enabled('test' !== $containerConfigurator->env());
};
