<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge;

use App\Entity\Comment as CommentEntity;
use App\Entity\Question as QuestionEntity;
use App\Entity\User as UserEntity;
use App\Repository\CommentRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Comment
{
    use DefaultActionTrait;

    #[LiveProp]
    public QuestionEntity $question;

    #[LiveProp]
    public UserEntity $currentUser;

    public function __construct(
        private readonly CommentRepository $commentRepository,
    ) {
    }

    /**
     * Get the comments of the question.
     *
     * @return CommentEntity[]
     */
    public function getComments(): array
    {
        return $this->commentRepository->findQuestionComments($this->question);
    }

    #[LiveListener('app:comment-refresh')]
    public function refresh(): void
    {
        // Refresh the comments.
        //
        // It calls "__invoke()" implicitly, so this method itself is no-op.
    }
}
