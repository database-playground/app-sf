<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ChallengeDto\QueryResultDto;
use App\Exception\QueryExecuteException;
use App\Exception\ResourceException;
use App\Exception\SchemaExecuteException;
use App\Exception\TimedOutException;
use App\Service\Types\DbRunnerProcessPayload;
use App\Service\Types\ProcessError;
use Doctrine\SqlFormatter\SqlFormatter;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * A class to run queries on a SQLite3 database.
 */
final readonly class DbRunner
{
    private SqlFormatter $formatter;

    /**
     * @param float $timeout The timeout in seconds. Default is 60 seconds.
     */
    public function __construct(
        protected float $timeout = 60,
    ) {
        $this->formatter = new SqlFormatter();
    }

    /**
     * Hash the given SQL statement to a hex string.
     *
     * Useful for caching. Normalization is applied.
     *
     * @param string $sql the SQL to hash
     *
     * @return string the hashed SHA-256 hex string
     */
    public function hashStatement(string $sql): string
    {
        return hash('sha3-256', $this->formatter->compress($sql));
    }

    /**
     * Run the query with SQLite3.
     *
     * For example:
     *
     * <code>
     *     $dbRunner = new DbRunner();
     *     $schema = "CREATE TABLE students (id INTEGER PRIMARY KEY, name TEXT)";
     *     $query = "SELECT * FROM students";
     *     $result = $dbRunner->runQuery($schema, $query);
     *     // $result is an array with the result of the query.
     *     // Example: [["id" => 1, "name" => "John"]]
     * </code>
     *
     * @param string $schema the schema to create the database
     * @param string $query  the query to run
     *
     * @return QueryResultDto the result of the query
     *
     * @throws SchemaExecuteException if the schema could not be executed
     * @throws QueryExecuteException  if the query could not be executed
     * @throws ResourceException      if the resource is exhausted (exit code = 255)
     * @throws \Throwable             if the unexpected error is received
     */
    public function runQuery(string $schema, string $query): QueryResultDto
    {
        // Use a process to prevent the SQLite3 extension from crashing the PHP process.
        // For example, CTE queries and randomblob can crash the PHP process.
        // See the test cases for more details.
        //
        // We don't yield over the result; instead, we store in the memory.
        // Our PHP process has a hard limit of the memory usage,
        // and we can crash it as early as possible when receiving a big result.

        $process = new Process(['php', __DIR__.'/Processes/dbrunner_process.php']);
        $process->setTimeout($this->timeout);
        $process->setInput(serialize(new DbRunnerProcessPayload($schema, $query)));

        try {
            $process->mustRun();

            $output = $process->getOutput();
            $outputDeserialized = unserialize($output, [
                'allowed_classes' => [
                    QueryResultDto::class,
                ],
            ]);

            if (!$outputDeserialized instanceof QueryResultDto) {
                throw new \RuntimeException("unexpected output: $output");
            }

            return $outputDeserialized;
        } catch (ProcessFailedException) {
            $exitCode = $process->getExitCode();

            if (255 === $exitCode) {
                throw new ResourceException();
            }

            if (1 === $exitCode) {
                $output = $process->getErrorOutput();
                $outputDeserialized = unserialize($output, [
                    'allowed_classes' => true,
                ]);

                if (!($outputDeserialized instanceof ProcessError)) {
                    $o = json_encode($output);
                    throw new \RuntimeException("Unexpected data received (exit code 1): $o");
                }

                $outputDeserialized->rethrow();
            }

            throw new \RuntimeException("Unexpected exit code: $exitCode");
        } catch (ProcessTimedOutException) {
            throw new TimedOutException($this->timeout);
        }
    }
}
