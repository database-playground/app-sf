<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\DebugConfig;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return static function (ContainerConfigurator $containerConfigurator, DebugConfig $debugConfig): void {
    if ('dev' === $containerConfigurator->env()) {
        // Forwards VarDumper Data clones to a centralized server allowing to inspect dumps on CLI or in your browser.
        // See the "server:dump" command to start a new server.
        $debugConfig->dumpDestination('tcp://'.env('VAR_DUMPER_SERVER'));
    }
};
