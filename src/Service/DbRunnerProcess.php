<?php

declare(strict_types=1);
/**
 * The process to run the query.
 *
 * It is isolated from the main PHP worker process (DbRunner.php)
 * to prevent the SQLite3 extension from crashing the PHP process.
 */

/**
 * Throws the error in the RPC form.
 *
 * @param string $className the class name of the exception
 * @param string $message   the message of the exception
 *
 * @return never-return the function always exits with 0
 */
function throws(string $className, string $message): void
{
    fwrite(\STDERR, serialize(['error' => $className, 'message' => $message]));
    exit(1);
}

$stdin = file_get_contents('php://stdin');
if (false === $stdin) {
    throws('RuntimeException', 'could not read from stdin');
}

$input = unserialize($stdin);

assert(is_array($input));
$schema = $input['schema'];
assert(is_string($schema));
$query = $input['query'];
assert(is_string($query));

$sqlite = new SQLite3(':memory:');
$sqlite->busyTimeout(3000 /* milliseconds */);
$sqlite->enableExceptions(true);

try {
    try {
        $sqlite->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL;');
    } catch (Exception) {
        throws('SchemaExecuteException', $sqlite->lastErrorMsg());
    }

    try {
        $sqlite->exec($schema);
    } catch (Exception) {
        throws('SchemaExecuteException', $sqlite->lastErrorMsg());
    }

    try {
        $result = $sqlite->query($query);
    } catch (Exception) {
        throws('QueryExecuteException', $sqlite->lastErrorMsg());
    }

    if (is_bool($result)) {
        throws('QueryExecuteException', "invalid query given: '$query'");
    }

    $resultArray = [];

    try {
        while ($row = $result->fetchArray(\SQLITE3_ASSOC)) {
            $resultArray[] = $row;
        }
    } finally {
        $result->finalize();
    }

    echo serialize(['result' => $resultArray]);
} finally {
    $sqlite->close();
}
