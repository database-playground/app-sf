<?php

declare(strict_types=1);

namespace App\Twig\Components\Challenge\CommentModule;

use App\Entity\Comment as CommentEntity;
use App\Entity\User as UserEntity;
use App\Repository\CommentLikeEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Comment
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public UserEntity $currentUser;

    #[LiveProp]
    public CommentEntity $comment;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CommentLikeEventRepository $commentLikeEventRepository,
    ) {
    }

    public function getLikes(): int
    {
        return $this->commentLikeEventRepository->getLikeCount($this->comment);
    }

    public function getLiked(): bool
    {
        return $this->commentLikeEventRepository->getLikeState($this->currentUser, $this->comment);
    }

    public function isOwned(): bool
    {
        $commenter = $this->comment->getCommenter();

        return $this->currentUser->getUserIdentifier() === $commenter->getUserIdentifier();
    }

    #[LiveAction]
    public function delete(): void
    {
        // If the current user is not the owner of the comment,
        // we throw a 403 Forbidden exception.
        if (!$this->isOwned()) {
            throw new AccessDeniedHttpException('You are not allowed to delete this comment.');
        }

        $this->entityManager->remove($this->comment);
        $this->entityManager->flush();

        $this->emitUp('app:comment-refresh');
    }

    #[LiveAction]
    public function likeOrDislike(): void
    {
        // User cannot like their own comment
        if ($this->isOwned()) {
            throw new BadRequestHttpException('You cannot like your own comment.');
        }

        if ($this->getLiked()) {
            $this->commentLikeEventRepository->dislike($this->currentUser, $this->comment);
        } else {
            $this->commentLikeEventRepository->like($this->currentUser, $this->comment);
        }

        $this->emitSelf('app:comment-liked');
    }
}
