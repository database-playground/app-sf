<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\CommentModule;

use App\Entity\Comment as CommentEntity;
use App\Entity\Question as QuestionEntity;
use App\Entity\User as UserEntity;
use App\Form\CommentCreateFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class CommentForm
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ?CommentEntity $initialComment = null;

    #[LiveProp]
    public UserEntity $commenter;

    #[LiveProp]
    public QuestionEntity $question;

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        $this->initialComment = (new CommentEntity())
            ->setQuestion($this->question)
            ->setCommenter($this->commenter);

        return $this->formFactory->create(CommentCreateFormType::class, $this->initialComment);
    }

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager): void
    {
        $this->submitForm();

        $comment = $this->getForm()->getData();
        \assert($comment instanceof CommentEntity);

        $entityManager->persist($comment);
        $entityManager->flush();

        $this->emitUp('app:comment-refresh');
    }
}
