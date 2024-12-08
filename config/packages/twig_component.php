<?php

declare(strict_types=1);

use Symfony\Config\TwigComponentConfig;

return static function (TwigComponentConfig $twigComponentConfig): void {
    $twigComponentConfig
        ->anonymousTemplateDirectory('components/')
        ->defaults('App\Twig\Components\\', 'components/');
};
