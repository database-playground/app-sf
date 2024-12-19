<?php

declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function (FrameworkConfig $frameworkConfig): void {
    $frameworkConfig->cache()
        ->prefixSeed('database_playground/app')
        ->app('cache.adapter.redis_tag_aware')
        ->defaultRedisProvider(param('app.redis_uri'))
    ;
};
