<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Question;
use App\Exception\NoSchemaException;
use App\Exception\QueryExecuteException;
use App\Exception\SchemaExecuteException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The {@link DbRunnerService} that retrieves the answer and schema
 * from the {@link Question}.
 */
readonly class QuestionDbRunnerService
{
    public function __construct(
        public DbRunnerService $dbRunnerService,
    ) {
    }

    /**
     * Get the result of the query from the question.
     *
     * @param Question $question the question to get the result from
     * @param string   $query    the query to execute
     *
     * @return array<array<string, mixed>> the result of the query
     *
     * @throws InvalidArgumentException
     * @throws SchemaExecuteException
     * @throws QueryExecuteException
     */
    protected function getResult(Question $question, string $query): array
    {
        $schema = $question->getSchema();
        if (!$schema) {
            throw new NoSchemaException($question->getId());
        }

        return $this->dbRunnerService->runQuery(
            $schema->getSchema(),
            $query,
        );
    }

    /**
     * Get the result of the query from the question.
     *
     * @param Question $question the question to get the result from
     *
     * @return array<array<string, mixed>> the result of the query
     *
     * @throws NotFoundHttpException
     * @throws InvalidArgumentException
     * @throws SchemaExecuteException
     * @throws QueryExecuteException
     */
    public function getAnswerResult(Question $question): array
    {
        return $this->getResult($question, $question->getAnswer());
    }

    /**
     * Get the result of the query from the question.
     *
     * @param Question $question the question to get the result from
     * @param string   $query    the query to execute
     *
     * @return array<array<string, mixed>> the result of the query
     *
     * @throws NotFoundHttpException
     * @throws InvalidArgumentException
     * @throws SchemaExecuteException
     * @throws QueryExecuteException
     */
    public function getQueryResult(Question $question, string $query): array
    {
        return $this->getResult($question, $query);
    }
}
