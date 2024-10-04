<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Comment
{
    use Trait\WithModelTimeInfo;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private User $commenter;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private Question $question;

    #[ORM\Column(type: Types::TEXT)]
    #[NotBlank]
    private string $content;

    /**
     * @var Collection<int, CommentLikeEvent>
     */
    #[ORM\OneToMany(targetEntity: CommentLikeEvent::class, mappedBy: 'comment', orphanRemoval: true)]
    private Collection $commentLikeEvents;

    public function __construct()
    {
        $this->commentLikeEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommenter(): User
    {
        return $this->commenter;
    }

    public function setCommenter(User $commenter): static
    {
        $this->commenter = $commenter;

        return $this;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return Collection<int, CommentLikeEvent>
     */
    public function getCommentLikeEvents(): Collection
    {
        return $this->commentLikeEvents;
    }

    public function addCommentLikeEvent(CommentLikeEvent $commentLikeEvent): static
    {
        if (!$this->commentLikeEvents->contains($commentLikeEvent)) {
            $this->commentLikeEvents->add($commentLikeEvent);
            $commentLikeEvent->setComment($this);
        }

        return $this;
    }

    public function removeCommentLikeEvent(CommentLikeEvent $commentLikeEvent): static
    {
        if ($this->commentLikeEvents->removeElement($commentLikeEvent)) {
            // set the owning side to a default class (unless already changed)
            if ($commentLikeEvent->getComment() === $this) {
                $commentLikeEvent->setComment(new self());
            }
        }

        return $this;
    }
}
