<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\QueryExecuteException;
use App\Exception\SchemaExecuteException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

readonly class DbRunnerService
{
    protected DbRunner $dbRunner;

    public function __construct(protected CacheInterface $cacheDbrunner)
    {
        $this->dbRunner = new DbRunner();
    }

    /**
     * Run a query on the SQLite3 database, cached.
     *
     * @return array<array<string, mixed>>
     *
     * @throws InvalidArgumentException
     * @throws SchemaExecuteException
     * @throws QueryExecuteException
     */
    public function runQuery(string $schema, string $query): array
    {
        $schemaHash = $this->dbRunner->hashStatement($schema);
        $queryHash = $this->dbRunner->hashStatement($query);
        $hash = "dbrunner.$schemaHash.$queryHash";

        return $this->cacheDbrunner->get($hash, fn () => $this->dbRunner->runQuery($schema, $query));
    }
}
