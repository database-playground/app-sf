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
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\SerializerInterface;
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

    public function __construct(
        private readonly QuestionDbRunnerService $questionDbRunnerService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[LiveProp]
    public Question $question;

    #[LiveProp]
    public User $user;

    /**
     * @var string the query to execute
     */
    #[LiveProp(writable: true)]
    public string $query;

    #[LiveAction]
    public function execute(SerializerInterface $serializer): void
    {
        $payload = Payload::loading();
        $this->emitUp('app:challenge-payload', [
            'payload' => $serializer->serialize($payload, 'json'),
        ]);

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

            $payload = Payload::result($result, same: $same);
        } catch (HttpException $e) {
            $solutionEvent = $solutionEvent->setStatus(SolutionEventStatus::Failed);

            $payload = Payload::errorWithCode($e->getStatusCode(), $e->getMessage());
        } catch (\Throwable $e) {
            $solutionEvent = $solutionEvent->setStatus(SolutionEventStatus::Failed);

            $payload = Payload::errorWithCode(500, $e->getMessage());
        } finally {
            $this->emitUp('app:challenge-payload', [
                'payload' => $serializer->serialize($payload, 'json'),
            ]);

            $this->entityManager->persist($solutionEvent);
            $this->entityManager->flush();
        }
    }
}
