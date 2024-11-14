<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ChallengeDto\QueryResultDto;
use App\Entity\Question;
use App\Exception\QueryExecuteException;
use App\Exception\SchemaExecuteException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Lock\LockFactory;

/**
 * The {@link DbRunnerService} that retrieves the answer and schema
 * from the {@link Question}.
 */
final class QuestionDbRunnerService
{
    public function __construct(
        protected DbRunnerService $dbRunnerService,
        protected LockFactory $lockFactory,
    ) {
    }

    /**
     * Get the result of the query from the question.
     *
     * @param Question $question the question to get the result from
     * @param string   $query    the query to execute
     *
     * @return QueryResultDto the result of the query
     *
     * @throws InvalidArgumentException
     * @throws SchemaExecuteException
     * @throws QueryExecuteException
     */
    protected function getResult(Question $question, string $query): QueryResultDto
    {
        $schema = $question->getSchema();

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
     * @return QueryResultDto the result of the query
     *
     * @throws NotFoundHttpException
     * @throws InvalidArgumentException
     * @throws SchemaExecuteException
     * @throws QueryExecuteException
     */
    public function getAnswerResult(Question $question): QueryResultDto
    {
        $lock = $this->lockFactory->createLock("question_{$question->getId()}_answer");

        try {
            $lock->acquire(true);
            $result = $this->getResult($question, $question->getAnswer());
        } finally {
            $lock->release();
        }

        return $result;
    }

    /**
     * Get the result of the query from the question.
     *
     * @param Question $question the question to get the result from
     * @param string   $query    the query to execute
     *
     * @return QueryResultDto the result of the query
     *
     * @throws NotFoundHttpException
     * @throws InvalidArgumentException
     * @throws SchemaExecuteException
     * @throws QueryExecuteException
     */
    public function getQueryResult(Question $question, string $query): QueryResultDto
    {
        return $this->getResult($question, $query);
    }
}
