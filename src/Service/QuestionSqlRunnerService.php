<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Question;
use App\Entity\SqlRunnerDto\SqlRunnerRequest;
use App\Entity\SqlRunnerDto\SqlRunnerResult;
use App\Exception\SqlRunner\QueryExecuteException;
use App\Exception\SqlRunner\RunnerException;
use App\Exception\SqlRunner\SchemaExecuteException;

/**
 * The {@link SqlRunnerService} that retrieves the answer and schema
 * from the {@link Question}.
 */
final class QuestionSqlRunnerService
{
    public function __construct(
        protected SqlRunnerService $sqlRunnerService,
    ) {
    }

    /**
     * Get the result of the query from the question.
     *
     * @param Question $question the question to get the result from
     * @param string   $query    the query to execute
     *
     * @return SqlRunnerResult the result of the query
     *
     * @throws QueryExecuteException  when the query execution fails
     * @throws SchemaExecuteException when the schema execution fails
     * @throws RunnerException        when the runner fails (internal error or client error)
     */
    protected function getResult(Question $question, string $query): SqlRunnerResult
    {
        $schema = $question->getSchema();

        return $this->sqlRunnerService->runQuery(
            (new SqlRunnerRequest())
                ->setQuery($query)
                ->setSchema($schema->getSchema())
        );
    }

    /**
     * Get the result of the query from the question.
     *
     * @param Question $question the question to get the result from
     *
     * @return SqlRunnerResult the result of the query
     *
     * @throws QueryExecuteException  when the query execution fails
     * @throws SchemaExecuteException when the schema execution fails
     * @throws RunnerException        when the runner fails (internal error or client error)
     */
    public function getAnswerResult(Question $question): SqlRunnerResult
    {
        return $this->getResult($question, $question->getAnswer());
    }

    /**
     * Get the result of the query from the question.
     *
     * @param Question $question the question to get the result from
     * @param string   $query    the query to execute
     *
     * @return SqlRunnerResult the result of the query
     *
     * @throws QueryExecuteException  when the query execution fails
     * @throws SchemaExecuteException when the schema execution fails
     * @throws RunnerException        when the runner fails (internal error or client error)
     */
    public function getQueryResult(Question $question, string $query): SqlRunnerResult
    {
        return $this->getResult($question, $query);
    }
}
