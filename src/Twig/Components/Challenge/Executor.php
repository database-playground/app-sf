<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

require_once __DIR__.'/EventConstant.php';

use App\Entity\Question;
use App\Service\QuestionDbRunnerService;
use Psr\Cache\InvalidArgumentException;
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

    public function __construct(
        protected QuestionDbRunnerService $questionDbRunnerService,
    ) {
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
    public function execute(): void
    {
        $this->emit(QueryPendingEvent);

        try {
            $result = $this->questionDbRunnerService->getQueryResult($this->question, $this->query);
            $answer = $this->questionDbRunnerService->getAnswerResult($this->question);

            $this->emit(QueryCompletedEvent, ['result' => $result, 'same' => $result == $answer]);
        } catch (HttpException $e) {
            $this->emit(QueryFailedEvent, ['error' => $e->getMessage(), 'code' => $e->getStatusCode()]);
        } catch (\Exception $e) {
            $this->emit(QueryFailedEvent, ['error' => $e->getMessage(), 'code' => 500]);
        }
    }
}
