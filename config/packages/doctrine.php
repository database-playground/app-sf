<?php

declare(strict_types=1);

use Oro\ORM\Query\AST\Functions\Cast;
use Oro\ORM\Query\AST\Functions\DateTime\ConvertTz;
use Oro\ORM\Query\AST\Functions\Numeric\Pow;
use Oro\ORM\Query\AST\Functions\Numeric\Round;
use Oro\ORM\Query\AST\Functions\Numeric\Sign;
use Oro\ORM\Query\AST\Functions\Numeric\TimestampDiff;
use Oro\ORM\Query\AST\Functions\SimpleFunction;
use Oro\ORM\Query\AST\Functions\String\ConcatWs;
use Oro\ORM\Query\AST\Functions\String\DateFormat;
use Oro\ORM\Query\AST\Functions\String\GroupConcat;
use Oro\ORM\Query\AST\Functions\String\Replace;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\DoctrineConfig;
use Symfony\Config\FrameworkConfig;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function (ContainerConfigurator $containerConfigurator, DoctrineConfig $doctrineConfig, FrameworkConfig $frameworkConfig): void {
    $dbalConfig = $doctrineConfig->dbal();
    $dbalConfig
        ->connection('default')
        ->url(env('DATABASE_URL')->resolve())
        ->profilingCollectBacktrace(param('kernel.debug'))
        ->useSavepoints(true);

    $ormConfig = $doctrineConfig->orm();
    $ormConfig
        ->autoGenerateProxyClasses(true)
        ->enableLazyGhostObjects(true);

    $entityManager = $ormConfig->entityManager('default');
    $entityManager
        ->reportFieldsWhereDeclared(true)
        ->validateXmlMapping(true)
        ->namingStrategy('doctrine.orm.naming_strategy.underscore_number_aware')
        ->autoMapping(true)
        ->mapping('App', [
            'type' => 'attribute',
            'is_bundle' => false,
            'dir' => '%kernel.project_dir%/src/Entity',
            'prefix' => 'App\Entity',
            'alias' => 'App',
        ]);

    $ormConfig
        ->controllerResolver()
        ->autoMapping(false);

    $entityManager->dql()
        ->datetimeFunction('date', SimpleFunction::class)
        ->datetimeFunction('time', SimpleFunction::class)
        ->datetimeFunction('timestamp', SimpleFunction::class)
        ->datetimeFunction('convert_tz', ConvertTz::class)
        ->numericFunction('timestampdiff', TimestampDiff::class)
        ->numericFunction('dayofyear', SimpleFunction::class)
        ->numericFunction('dayofmonth', SimpleFunction::class)
        ->numericFunction('dayofweek', SimpleFunction::class)
        ->numericFunction('week', SimpleFunction::class)
        ->numericFunction('day', SimpleFunction::class)
        ->numericFunction('hour', SimpleFunction::class)
        ->numericFunction('minute', SimpleFunction::class)
        ->numericFunction('month', SimpleFunction::class)
        ->numericFunction('quarter', SimpleFunction::class)
        ->numericFunction('second', SimpleFunction::class)
        ->numericFunction('year', SimpleFunction::class)
        ->numericFunction('sign', Sign::class)
        ->numericFunction('pow', Pow::class)
        ->numericFunction('round', Round::class)
        ->numericFunction('ceil', SimpleFunction::class)
        ->stringFunction('md5', SimpleFunction::class)
        ->stringFunction('group_concat', GroupConcat::class)
        ->stringFunction('concat_ws', ConcatWs::class)
        ->stringFunction('cast', Cast::class)
        ->stringFunction('replace', Replace::class)
        ->stringFunction('date_format', DateFormat::class);

    if ('test' === $containerConfigurator->env()) {
        $dbalConfig
            ->connection('default')
            // "TEST_TOKEN" is typically set by ParaTest
            ->dbnameSuffix('_test.%env(default::TEST_TOKEN)%');
    }

    if ('prod' === $containerConfigurator->env()) {
        $systemCachePool = 'doctrine.system_cache_pool';
        $resultCachePool = 'doctrine.result_cache_pool';

        $ormConfig
            ->autoGenerateProxyClasses(false)
            ->proxyDir('%kernel.build_dir%/doctrine/orm/Proxies');

        $entityManager
            ->queryCacheDriver([
                'type' => 'pool',
                'pool' => $systemCachePool,
            ]);

        $entityManager
            ->resultCacheDriver([
                'type' => 'pool',
                'pool' => $resultCachePool,
            ]);

        $cache = $frameworkConfig->cache();
        $cache->pool($systemCachePool, [
            'adapter' => 'cache.system',
        ]);
        $cache->pool($resultCachePool, [
            'adapter' => 'cache.app',
        ]);
    }
};
