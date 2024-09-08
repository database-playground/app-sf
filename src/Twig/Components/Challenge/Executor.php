<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Entity\Question;
use App\Exception\QueryExecuteException;
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
        $this->emit('challenge:query-pending');

        try {
            $result = $this->questionDbRunnerService->getQueryResult($this->question, $this->query);

            // check if the result is UTF-8 encoded
            try {
                json_encode($result, \JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new QueryExecuteException('The result is not UTF-8 encoded.', previous: $e);
            }

            $answer = $this->questionDbRunnerService->getAnswerResult($this->question);

            $this->emit('challenge:query-completed', ['result' => $result, 'same' => $result == $answer]);
        } catch (HttpException $e) {
            $this->emit('challenge:query-failed', ['error' => $e->getMessage(), 'code' => $e->getStatusCode()]);
        } catch (\Exception $e) {
            $this->emit('challenge:query-failed', ['error' => $e->getMessage(), 'code' => 500]);
        }
    }
}
