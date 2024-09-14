<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Question;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Get the comments of a question.
     *
     * @param Question $question the question to get the comments for
     * @param int|null $limit    The maximum number of comments to return. Used for pagination.
     * @param int|null $offset   The number of comments to skip. Used for pagination.
     *
     * @return Comment[] the comments of the question
     */
    public function findQuestionComments(Question $question, ?int $limit = null, ?int $offset = null): array
    {
        return $this->findBy(
            ['question' => $question],
            ['id' => 'DESC'],
            limit: $limit,
            offset: $offset
        );
    }

    /**
     * Get all the comments created by a user.
     *
     * @param User     $user   the user to get the comments for
     * @param int|null $limit  The maximum number of comments to return. Used for pagination.
     * @param int|null $offset The number of comments to skip. Used for pagination.
     *
     * @return Comment[] the comments created by the user
     */
    public function findUserComments(User $user, ?int $limit = null, ?int $offset = null): array
    {
        return $this->findBy(
            ['commenter' => $user],
            ['id' => 'DESC'],
            limit: $limit,
            offset: $offset
        );
    }
}
