<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CommentLikeEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentLikeEventRepository::class)]
class CommentLikeEvent extends BaseEvent
{
    #[ORM\ManyToOne(inversedBy: 'commentLikeEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private User $liker;

    #[ORM\ManyToOne(inversedBy: 'commentLikeEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private Comment $comment;

    public function getLiker(): User
    {
        return $this->liker;
    }

    public function setLiker(User $liker): static
    {
        $this->liker = $liker;

        return $this;
    }

    public function getComment(): Comment
    {
        return $this->comment;
    }

    public function setComment(Comment $comment): static
    {
        $this->comment = $comment;

        return $this;
    }
}
