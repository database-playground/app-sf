<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Tabs;

use App\Entity\ChallengeDto\FallableSqlRunnerResult;
use App\Entity\Question;
use App\Entity\User;
use App\Exception\SqlRunner\QueryExecuteException;
use App\Exception\SqlRunner\SchemaExecuteException;
use App\Repository\SolutionEventRepository;
use App\Service\QuestionSqlRunnerService;
use App\Service\SqlRunnerComparer;
use Psr\Log\LoggerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

use function Symfony\Component\Translation\t;

#[AsLiveComponent]
final class UserQueryResult
{
    use DefaultActionTrait;

    public function __construct(
        private readonly QuestionSqlRunnerService $questionSqlRunnerService,
        private readonly SolutionEventRepository $solutionEventRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @var Question $question the question to present the answer
     */
    #[LiveProp]
    public Question $question;

    /**
     * @var User $user the user to get the latest query result from
     */
    #[LiveProp]
    public User $user;

    #[LiveProp(writable: true)]
    public ?string $query = null;

    #[PostMount]
    public function postMount(): void
    {
        $this->query = $this->solutionEventRepository->getLatestQuery($this->question, $this->user)?->getQuery();
    }

    public function getResult(): ?FallableSqlRunnerResult
    {
        if (null === $this->query) {
            return null;
        }

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

        try {
            $resultDto = $this->questionSqlRunnerService->getQueryResult($this->question, $this->query);
        } catch (SchemaExecuteException $e) {
            $this->logger->error('Schema Error', [
                'exception' => $e,
            ]);

            $errorMessage = t('challenge.errors.schema-error', [
                '%error%' => $e->getMessage(),
            ]);

            return (new FallableSqlRunnerResult())->setErrorMessage($errorMessage);
        } catch (QueryExecuteException $e) {
            $errorMessage = t('challenge.errors.user-query-error', [
                '%error%' => $e->getMessage(),
            ]);

            return (new FallableSqlRunnerResult())->setErrorMessage($errorMessage);
        } catch (\Throwable $e) {
            $this->logger->error('SQL Runner failed when running user queries', [
                'exception' => $e,
            ]);

            $errorMessage = t('challenge.errors.unavailable', [
                '%error%' => $e->getMessage(),
            ]);

            return (new FallableSqlRunnerResult())->setErrorMessage($errorMessage);
        }

        // compare the result
        $compareResult = SqlRunnerComparer::compare($answerResultDto, $resultDto);
        if ($compareResult->correct()) {
            return (new FallableSqlRunnerResult())->setResult($resultDto);
        }

        $errorMessage = t('challenge.errors.user-query-failure', [
            '%error%' => $compareResult->reason(),
        ]);

        return (new FallableSqlRunnerResult())->setResult($resultDto)->setErrorMessage($errorMessage);
    }

    #[LiveListener('app:challenge-executor:query-created')]
    public function onQueryUpdated(#[LiveArg] string $query): void
    {
        $this->query = $query;
    }
}
