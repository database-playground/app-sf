<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Instruction;

use App\Entity\HintOpenEvent;
use App\Entity\Question;
use App\Entity\SolutionEventStatus;
use App\Entity\SqlRunnerDto\SqlRunnerRequest;
use App\Entity\User;
use App\Exception\HintException;
use App\Repository\SolutionEventRepository;
use App\Service\PointCalculationService;
use App\Service\PromptService;
use App\Service\SqlRunnerComparer;
use App\Service\SqlRunnerService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

use function Symfony\Component\Translation\t;

#[AsLiveComponent]
final class Modal
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[LiveProp]
    public User $currentUser;

    #[LiveProp]
    public Question $question;

    public function getCost(): int
    {
        return PointCalculationService::hintOpenEventPoint;
    }

    /**
     * Ask GPT to generate an instruction for the user.
     *
     * It will emit an event {@link Content} will listen to.
     */
    #[LiveAction]
    public function instruct(
        SolutionEventRepository $solutionEventRepository,
        SqlRunnerService $sqlRunnerService,
        PromptService $promptService,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
    ): void {
        $appFeatureHint = $parameterBag->get('app.features.hint');
        \assert(\is_bool($appFeatureHint));

        if (!$appFeatureHint) {
            throw new BadRequestHttpException('Hint feature is disabled.');
        }

        $query = $solutionEventRepository->getLatestQuery($this->question, $this->currentUser);
        if (null === $query) {
            $this->flushHint('informative', t('instruction.hint.not_submitted'));

            return;
        }
        if (SolutionEventStatus::Passed === $query->getStatus()) {
            $this->flushHint('informative', t('instruction.hint.solved'));

            return;
        }

        $schema = $query->getQuestion()->getSchema();

        try {
            $answer = $query->getQuestion()->getAnswer();
            $answerResult = $sqlRunnerService->runQuery(
                (new SqlRunnerRequest())
                    ->setSchema($schema->getSchema())
                    ->setQuery($answer),
            );
        } catch (\Throwable $e) {
            $this->flushHint('informative', t('instruction.hint.answer-wrong', [
                '%error%' => $e->getMessage(),
            ]));

            return;
        }

        $hintOpenEvent = (new HintOpenEvent())
            ->setOpener($this->currentUser)
            ->setQuestion($this->question)
            ->setQuery($query->getQuery());

        try {
            try {
                $userResult = $sqlRunnerService->runQuery(
                    (new SqlRunnerRequest())
                        ->setSchema($schema->getSchema())
                        ->setQuery($query->getQuery()),
                );
            } catch (\Throwable $e) {
                $hint = $promptService->hint($query->getQuery(), $e->getMessage(), $answer);
                $entityManager->persist($hintOpenEvent->setResponse($hint));
                $this->flushHint('hint', $hint);

                return;
            }

            $compareResult = SqlRunnerComparer::compare($answerResult, $userResult);
            if ($compareResult->correct()) {
                $this->flushHint('informative', t('instruction.hint.solved'));

                return;
            }

            $compareReason = $compareResult->reason()->trans($translator, 'en_US');

            $hint = $promptService->hint($query->getQuery(), "Different result: {$compareReason}", $answer);
            $entityManager->persist($hintOpenEvent->setResponse($hint));
            $this->flushHint('hint', $hint);
        } catch (HintException $e) {
            $this->logger->error('Failed to generate hint', [
                'exception' => $e,
                'query' => $query->getQuery(),
                'answer' => $answer,
            ]);

            $this->flushHint('informative', t('instruction.hint.hint-service-error', [
                '%error%' => $e->getPrevious()?->getMessage() ?? $e->getMessage(),
            ]));
        } finally {
            $entityManager->flush();
        }
    }

    /**
     * Flush the hint to the client.
     *
     * @param string                     $type the type of the hint (informative or hint)
     * @param string|TranslatableMessage $hint the hint to flush
     */
    private function flushHint(string $type, string|TranslatableMessage $hint): void
    {
        $this->emit('app:challenge-hint', [
            'type' => $type,
            'hint' => $hint instanceof TranslatableMessage
                ? $hint->trans($this->translator)
                : $hint,
        ]);
    }
}
