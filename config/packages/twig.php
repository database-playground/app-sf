<?php

declare(strict_types=1);

use Symfony\Config\TwigConfig;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function (TwigConfig $twigConfig): void {
    $twigConfig
        ->fileNamePattern('*.twig')
        ->formThemes(['bootstrap_5_layout.html.twig'])
        ->global('umami_domain', env('UMAMI_DOMAIN'))
        ->global('umami_website_id', env('UMAMI_WEBSITE_ID'))
        ->strictVariables(true)
        ->global('appfeatures.hint', param('app.features.hint'))
        ->global('appfeatures.comment', param('app.features.comment'));
};
