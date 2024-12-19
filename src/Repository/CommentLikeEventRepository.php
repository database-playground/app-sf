<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\CommentLikeEvent;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommentLikeEvent>
 */
class CommentLikeEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentLikeEvent::class);
    }

    /**
     * Get the number of likes for a comment.
     *
     * @param Comment $comment the comment to get the number of likes for
     *
     * @return int the number of likes for the comment
     */
    public function getLikeCount(Comment $comment): int
    {
        return $this->count([
            'comment' => $comment,
        ]);
    }

    /**
     * Get if a user liked a comment.
     *
     * @param User    $user    the user to check if they liked the comment
     * @param Comment $comment the comment to check if it was liked by the user
     *
     * @return bool if the user liked the comment
     */
    public function getLikeState(User $user, Comment $comment): bool
    {
        $likeCountByUser = $this->count([
            'liker' => $user,
            'comment' => $comment,
        ]);

        return $likeCountByUser > 0;
    }

    /**
     * Like a comment.
     *
     * If the user already liked the comment, this method does nothing.
     *
     * @param User    $user    the user liking the comment
     * @param Comment $comment the comment to like
     */
    public function like(User $user, Comment $comment): void
    {
        // If the user already liked the comment, do nothing.
        if ($this->getLikeState($user, $comment)) {
            return;
        }

        $likeEvent = (new CommentLikeEvent())
            ->setLiker($user)
            ->setComment($comment)
        ;

        $this->getEntityManager()->persist($likeEvent);
        $this->getEntityManager()->flush();
    }

    /**
     * Dislike a comment.
     *
     * If the user did not like the comment, this method does nothing.
     *
     * @param User    $user    the user disliking the comment
     * @param Comment $comment the comment to dislike
     */
    public function dislike(User $user, Comment $comment): void
    {
        $likeEvent = $this->findOneBy([
            'liker' => $user,
            'comment' => $comment,
        ]);

        if (null === $likeEvent) {
            return;
        }

        $this->getEntityManager()->remove($likeEvent);
        $this->getEntityManager()->flush();
    }
}
