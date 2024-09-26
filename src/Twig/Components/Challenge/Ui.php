<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Entity\Question;
use App\Entity\User;
use App\Twig\Components\Challenge\Instruction\HintPayload;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Ui
{
    use DefaultActionTrait;

    #[LiveProp]
    public User $user;

    #[LiveProp]
    public Question $question;

    #[LiveProp]
    public int $limit;

    /**
     * @var Payload|null $result the result of the user's query
     */
    #[LiveProp(writable: true)]
    public ?Payload $result = null;

    /**
     * @var string $query the user's query
     */
    #[LiveProp(writable: true)]
    public string $query = '';

    /**
     * @var HintPayload|null $hint the hint for the user
     */
    #[LiveProp(writable: true)]
    public ?HintPayload $hint = null;

    #[LiveListener('app:challenge-payload')]
    public function updateResult(
        SerializerInterface $serializer,
        #[LiveArg('payload')] string $rawPayload,
    ): void {
        $payload = $serializer->deserialize($rawPayload, Payload::class, 'json');

        $this->result = $payload;
    }

    #[LiveListener('app:challenge-hint')]
    public function updateHint(
        SerializerInterface $serializer,
        #[LiveArg('hint')] string $rawHint,
    ): void {
        $hint = $serializer->deserialize($rawHint, HintPayload::class, 'json');

        $this->hint = $hint;
    }
}
