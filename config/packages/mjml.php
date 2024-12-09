<?php

declare(strict_types=1);

return static function (Symfony\Config\MjmlConfig $mjmlConfig): void {
    $mjmlConfig->options([
        'binary' => '%kernel.project_dir%/node_modules/.bin/mjml',
        'minify' => false,
    ]);
};
