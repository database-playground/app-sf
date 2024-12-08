<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\Framework\ProfilerConfig;
use Symfony\Config\FrameworkConfig;
use Symfony\Config\WebProfilerConfig;

return static function (
    ContainerConfigurator $containerConfigurator,
    WebProfilerConfig $webProfilerConfig,
    FrameworkConfig $frameworkConfig,
): void {
    $frameworkProfilerConfig = $frameworkConfig->profiler();
    assert($frameworkProfilerConfig instanceof ProfilerConfig);

    switch ($containerConfigurator->env()) {
        case 'dev':
            $webProfilerConfig->toolbar(true);
            $webProfilerConfig->interceptRedirects(false);
            $frameworkProfilerConfig
                ->onlyExceptions(false)
                ->collectSerializerData(true);
            break;
        case 'test':
            $webProfilerConfig->toolbar(false);
            $webProfilerConfig->interceptRedirects(false);
            $frameworkProfilerConfig->collect(false);
            break;
    }
};
