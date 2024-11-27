<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Tabs;

use App\Entity\ChallengeDto\FallableSqlRunnerResult;
use App\Entity\Question;
use App\Exception\SqlRunner\QueryExecuteException;
use App\Exception\SqlRunner\SchemaExecuteException;
use App\Service\QuestionSqlRunnerService;
use Psr\Log\LoggerInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

use function Symfony\Component\Translation\t;

#[AsTwigComponent]
final class AnswerQueryResult
{
    public function __construct(
        private readonly QuestionSqlRunnerService $questionSqlRunnerService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @var Question the question to present the answer
     */
    public Question $question;

    public function getAnswer(): FallableSqlRunnerResult
    {
        try {
            $answerResultDto = $this->questionSqlRunnerService->getAnswerResult($this->question);
        } catch (SchemaExecuteException $e) {
            $this->logger->error('Schema Error', [
                'exception' => $e,
            ]);

            $errorMessage = t('challenge.errors.schema-error', [
                '%error%' => $e->getMessage(),
            ]);

            return (new FallableSqlRunnerResult())->setErrorMessage($errorMessage);
        } catch (QueryExecuteException $e) {
            $this->logger->error('Failed to get the answer result', [
                'exception' => $e,
            ]);

            $errorMessage = t('challenge.errors.answer-query-failure', [
                '%error%' => $e->getMessage(),
            ]);

            return (new FallableSqlRunnerResult())->setErrorMessage($errorMessage);
        } catch (\Throwable $e) {
            $this->logger->error('SQL Runner failed when running answer', [
                'exception' => $e,
            ]);

            $errorMessage = t('challenge.errors.unavailable', [
                '%error%' => $e->getMessage(),
            ]);

            return (new FallableSqlRunnerResult())->setErrorMessage($errorMessage);
        }

        return (new FallableSqlRunnerResult())->setResult($answerResultDto);
    }
}
