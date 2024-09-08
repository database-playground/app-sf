<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Entity\Question;
use App\Entity\SolutionEvent;
use App\Entity\SolutionEventStatus;
use App\Entity\User;
use App\Exception\QueryExecuteException;
use App\Service\QuestionDbRunnerService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Executor
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    protected User $user;

    public function __construct(
        protected QuestionDbRunnerService $questionDbRunnerService,
        protected EntityManagerInterface $entityManager,
        protected Security $security,
    ) {
        $user = $this->security->getUser();
        \assert($user instanceof User);

        $this->user = $user;
    }

    #[LiveProp]
    public Question $question;

    /**
     * @var string the query to execute
     */
    #[LiveProp(writable: true)]
    public string $query = '';

    /**
     * @throws InvalidArgumentException
     */
    #[LiveAction]
    public function execute(Request $request): void
    {
        $this->emit('challenge:query-pending');

        $solutionEvent = (new SolutionEvent())
            ->setQuestion($this->question)
            ->setSubmitter($this->user)
            ->setQuery($this->query);

        try {
            $result = $this->questionDbRunnerService->getQueryResult($this->question, $this->query);

            // check if the result is UTF-8 encoded
            try {
                json_encode($result, \JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new QueryExecuteException('The result is not UTF-8 encoded.', previous: $e);
            }

            $answer = $this->questionDbRunnerService->getAnswerResult($this->question);
            $same = $result == $answer;

            $solutionEvent = $solutionEvent->setStatus($same ? SolutionEventStatus::Passed : SolutionEventStatus::Failed);
            $this->emit('challenge:query-completed', ['result' => $result, 'same' => $result == $answer]);
        } catch (HttpException $e) {
            $solutionEvent = $solutionEvent->setStatus(SolutionEventStatus::Failed);
            $this->emit('challenge:query-failed', ['error' => $e->getMessage(), 'code' => $e->getStatusCode()]);
        } catch (\Exception $e) {
            $solutionEvent = $solutionEvent->setStatus(SolutionEventStatus::Failed);
            $this->emit('challenge:query-failed', ['error' => $e->getMessage(), 'code' => 500]);
        } finally {
            $this->entityManager->persist($solutionEvent);
            $this->entityManager->flush();
        }
    }
}
