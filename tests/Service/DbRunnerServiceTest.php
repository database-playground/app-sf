<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Exception\SchemaExecuteException;
use App\Service\DbRunner;
use App\Service\DbRunnerService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class DbRunnerServiceTest extends KernelTestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testCache(): void
    {
        $cache = new ArrayAdapter();
        $dbRunnerService = new TestableDbrunnerService($cache);

        $schema = "CREATE TABLE newsletter (id INTEGER PRIMARY KEY, content TEXT);
                   INSERT INTO newsletter (content) VALUES ('hello');";
        $query = 'SELECT * FROM newsletter';

        $result = $dbRunnerService->runQuery($schema, $query);
        self::assertEquals([['id' => 1, 'content' => 'hello']], $result);

        $hashedSchema = $dbRunnerService->getDbRunner()->hashStatement($schema);
        $hashedQuery = $dbRunnerService->getDbRunner()->hashStatement($query);
        self::assertTrue($cache->hasItem("dbrunner.$hashedSchema.$hashedQuery"));

        $result = $dbRunnerService->runQuery(
            "
                    -- normalization test
                    CREATE TABLE newsletter (id INTEGER PRIMARY KEY, content TEXT);
                    INSERT INTO newsletter (content) VALUES ('hello');",
            'SELECT * FROM newsletter'
        );
        self::assertEquals([['id' => 1, 'content' => 'hello']], $result);
        self::assertCount(1, $cache->getValues(), 'cache hit');

        $result = $dbRunnerService->runQuery(
            "
                    CREATE TABLE newsletter (id INTEGER PRIMARY KEY, content TEXT);
                    INSERT INTO newsletter (content) VALUES ('hello');",
            'SELECT * FROM newsletter -- normalization test'
        );
        self::assertEquals([['id' => 1, 'content' => 'hello']], $result);
        self::assertCount(1, $cache->getValues(), 'cache hit');

        $result = $dbRunnerService->runQuery(
            "
                    CREATE TABLE newsletter (id INTEGER PRIMARY KEY, content TEXT);
                    INSERT INTO newsletter (content) VALUES ('hello');",
            "SELECT * FROM newsletter WHERE content == 'hello'"
        );
        self::assertEquals([['id' => 1, 'content' => 'hello']], $result);
        self::assertCount(2, $cache->getValues(), 'cache not hit');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testCacheException(): void
    {
        $cache = new ArrayAdapter();
        $dbRunnerService = new TestableDbrunnerService($cache);

        $this->expectException(SchemaExecuteException::class);
        $this->expectExceptionMessageMatches('/syntax error/');
        $dbRunnerService->runQuery('ABCDABCBDABCDABCBDABCDABCBDABCDABCBD', 'SELECT * FROM newsletter');
    }
}

readonly class TestableDbrunnerService extends DbRunnerService
{
    public function getDbRunner(): DbRunner
    {
        return $this->dbRunner;
    }
}
