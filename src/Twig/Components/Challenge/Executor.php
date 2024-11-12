<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Entity\Question;
use App\Entity\SolutionEvent;
use App\Entity\SolutionEventStatus;
use App\Entity\User;
use App\Repository\SolutionEventRepository;
use App\Service\QuestionDbRunnerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
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
        private readonly SolutionEventRepository $solutionEventRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[LiveProp]
    public Question $question;

    #[LiveProp]
    public User $user;

    public function getPreviousQuery(): string
    {
        $se = $this->solutionEventRepository->findOneBy([
            'question' => $this->question,
            'submitter' => $this->user,
        ], orderBy: ['id' => 'DESC']);

        return $se?->getQuery() ?? '';
    }

    #[LiveAction]
    public function execute(
        SerializerInterface $serializer,
        #[LiveArg] string $query,
    ): void {
        if ('' === $query) {
            return;
        }

        $solutionEvent = (new SolutionEvent())
            ->setQuestion($this->question)
            ->setSubmitter($this->user)
            ->setQuery($query);

        try {
            $result = $this->questionDbRunnerService->getQueryResult($this->question, $query);

            $answer = $this->questionDbRunnerService->getAnswerResult($this->question);
            $same = $result === $answer;

            $solutionEvent = $solutionEvent->setStatus($same ? SolutionEventStatus::Passed : SolutionEventStatus::Failed);

            $payload = Payload::fromResult($result, same: $same);
        } catch (HttpException $e) {
            $solutionEvent = $solutionEvent->setStatus(SolutionEventStatus::Failed);

            $payload = Payload::fromErrorWithCode($e->getStatusCode(), $e->getMessage());
        } catch (\Throwable $e) {
            $solutionEvent = $solutionEvent->setStatus(SolutionEventStatus::Failed);

            $payload = Payload::fromErrorWithCode(500, $e->getMessage());
        }

        try {
            $serializedPayload = $serializer->serialize($payload, 'json');
        } catch (\Throwable $e) {
            $solutionEvent = $solutionEvent->setStatus(SolutionEventStatus::Failed);

            $serializedPayload = $serializer->serialize(
                Payload::fromErrorWithCode(500, $e->getMessage()),
                'json'
            );
        }

        $this->emitUp('app:challenge-payload', [
            'payload' => $serializedPayload,
        ]);

        $this->entityManager->persist($solutionEvent);
        $this->entityManager->flush();
    }
}
