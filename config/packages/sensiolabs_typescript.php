<?php

declare(strict_types=1);

use Symfony\Config\SensiolabsTypescriptConfig;

return static function (SensiolabsTypescriptConfig $config): void {
    $config->swcBinary('node_modules/.bin/swc');
    $config->sourceDir([
        '%kernel.project_dir%/assets/app',
        '%kernel.project_dir%/assets/controllers',
    ]);
};
