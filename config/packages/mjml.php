<?php

declare(strict_types=1);
use Symfony\Config\MjmlConfig;

return static function (MjmlConfig $mjmlConfig): void {
    $mjmlConfig->options([
        'binary' => '%kernel.project_dir%/node_modules/.bin/mjml',
        'minify' => false,
    ]);
};
