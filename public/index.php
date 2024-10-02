<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $appEnv = $context['APP_ENV'];
    assert(is_string($appEnv), "APP_ENV should be specified and must be a string.");

    $appDebug = $context['APP_DEBUG'] ?? 'false';
    assert(is_string($appDebug));

    return new Kernel($appEnv, (bool) $appDebug);
};
