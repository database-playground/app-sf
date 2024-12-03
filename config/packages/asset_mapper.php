<?php

declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $frameworkConfig): void {
    $frameworkConfig->assetMapper()
        ->path('assets/', '')
        ->path('vendor/twbs/bootstrap/', '')
        ->excludedPatterns([
            '*/assets/styles/_*.scss',
            '*/assets/styles/**/_*.scss',
        ]);
};
