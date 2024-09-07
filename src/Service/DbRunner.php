<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\QueryExecuteException;
use App\Exception\ResourceException;
use App\Exception\SchemaExecuteException;
use App\Exception\TimedOutException;
use Doctrine\SqlFormatter\SqlFormatter;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * A class to run queries on a SQLite3 database.
 */
readonly class DbRunner
{
    private SqlFormatter $formatter;

    /**
     * @param float $timeout The timeout in seconds. Default is 3 seconds.
     */
    public function __construct(
        protected float $timeout = 3,
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
     * @return array<array<string, mixed>> the result of the query
     *
     * @throws SchemaExecuteException if the schema could not be executed
     * @throws QueryExecuteException  if the query could not be executed
     * @throws ResourceException      if the resource is exhausted (exit code = 255)
     * @throws \RuntimeException      if the unexpected error is received
     */
    public function runQuery(string $schema, string $query): array
    {
        // Use a process to prevent the SQLite3 extension from crashing the PHP process.
        // For example, CTE queries and randomblob can crash the PHP process.
        // See the test cases for more details.
        //
        // We don't yield over the result; instead, we store in the memory.
        // Our PHP process has a hard limit of the memory usage,
        // and we can crash it as early as possible when receiving a big result.

        $process = new Process(['php', __DIR__.'/Processes/DbRunnerProcess.php']);
        $process->setTimeout($this->timeout);
        $process->setInput(serialize([
            'schema' => $schema,
            'query' => $query,
        ]));

        try {
            $process->mustRun();

            $output = $process->getOutput();
            $outputUnserialized = unserialize($process->getOutput());
            if (\is_array($outputUnserialized)
                && isset($outputUnserialized['result'])
                && \is_array($outputUnserialized['result'])) {
                return $outputUnserialized['result'];
            }

            throw new \RuntimeException("unexpected output: $output");
        } catch (ProcessFailedException) {
            $exitCode = $process->getExitCode();

            if (255 === $exitCode) {
                throw new ResourceException();
            }

            if (1 === $exitCode) {
                $output = $process->getErrorOutput();
                $outputUnserialized = unserialize($output);
                if (
                    \is_array($outputUnserialized)
                    && isset($outputUnserialized['error'])
                    && isset($outputUnserialized['message'])
                    && \is_string($outputUnserialized['error'])
                    && \is_string($outputUnserialized['message'])) {
                    $error = $outputUnserialized['error'];
                    $message = $outputUnserialized['message'];

                    switch ($error) {
                        case 'RuntimeException':
                            throw new \RuntimeException($message);
                        case 'SchemaExecuteException':
                            throw new SchemaExecuteException($message);
                        case 'QueryExecuteException':
                            throw new QueryExecuteException($message);
                    }
                }
            }

            throw new \RuntimeException("Unexpected exit code received: $exitCode");
        } catch (ProcessTimedOutException) {
            throw new TimedOutException($this->timeout);
        }
    }
}
