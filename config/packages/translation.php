<?php

declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $config): void {
    $config
        ->defaultLocale('zh_TW')
        ->enabledLocales(['zh_TW']);

    $config
        ->translator()
        ->defaultPath('%kernel.project_dir%/translations')
        ->fallbacks(['zh_TW']);
};
