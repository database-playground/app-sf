<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/../vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__.'/../.env');

$appEnv = $_SERVER['APP_ENV'];
assert(is_string($appEnv), 'APP_ENV should be specified and must be a string.');

$appDebug = $_SERVER['APP_DEBUG'] ?? 'false';
assert(is_string($appDebug));

$kernel = new Kernel($appEnv, (bool) $appDebug);

$kernel->boot();

return $kernel->getContainer()->get('doctrine')->getManager();
