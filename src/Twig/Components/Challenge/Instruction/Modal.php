<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\Instruction;

use App\Entity\HintOpenEvent;
use App\Entity\Question;
use App\Entity\User;
use App\Service\DbRunnerService;
use App\Service\PointCalculationService;
use App\Service\PromptService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Modal
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public User $currentUser;

    #[LiveProp]
    public Question $question;

    #[LiveProp(updateFromParent: true)]
    public string $query = '';

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
        DbRunnerService $dbRunnerService,
        PromptService $promptService,
        TranslatorInterface $translator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
    ): void {
        $appFeatureHint = $parameterBag->get('app.features.hint');
        \assert(\is_bool($appFeatureHint));

        if (!$appFeatureHint) {
            throw new BadRequestHttpException('Hint feature is disabled.');
        }

        if ('' === $this->query) {
            return;
        }

        $schema = $this->question->getSchema()->getSchema();
        $answer = $this->question->getAnswer();

        $hintOpenEvent = (new HintOpenEvent())
            ->setOpener($this->currentUser)
            ->setQuestion($this->question)
            ->setQuery($this->query);

        // run answer. if it failed, we should consider it an error
        try {
            $answerResult = $dbRunnerService->runQuery($schema, $answer);
        } catch (\Throwable $e) {
            $this->emit('app:challenge-hint', [
                'hint' => $serializer->serialize(HintPayload::fromError($e->getMessage()), 'json'),
            ]);

            return;
        }

        try {
            // run query to get the error message (or compare the result)
            $result = $dbRunnerService->runQuery($schema, $this->query);
        } catch (\Throwable $e) {
            $hint = $promptService->hint($this->query, $e->getMessage(), $answer);
        }

        if (isset($result) && $result !== $answerResult) {
            $hint = $promptService->hint($this->query, 'Different output', $answer);
        }

        if (!isset($hint)) {
            $hint = $translator->trans('instruction.hint.no_hint');
        }

        $this->emit('app:challenge-hint', [
            'hint' => $serializer->serialize(HintPayload::fromHint($hint), 'json'),
        ]);

        $hintOpenEvent = $hintOpenEvent->setResponse($hint);
        $entityManager->persist($hintOpenEvent);
        $entityManager->flush();
    }
}
