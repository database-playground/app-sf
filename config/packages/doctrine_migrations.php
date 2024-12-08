<?php

declare(strict_types=1);

use Symfony\Config\DoctrineMigrationsConfig;

return static function (DoctrineMigrationsConfig $doctrineMigrationsConfig): void {
    $doctrineMigrationsConfig
        ->enableProfiler(false);

    $doctrineMigrationsConfig
        // namespace is arbitrary but should be different from App\Migrations
        // as migrations classes should NOT be autoloaded
        ->migrationsPath('DoctrineMigrations', '%kernel.project_dir%/migrations');
};
