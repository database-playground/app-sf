<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\WithModelTimeInfo;
use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Question
{
    use WithModelTimeInfo;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Schema $schema = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(enumType: QuestionDifficulty::class)]
    private ?QuestionDifficulty $difficulty = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $answer = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $solution_video = null;

    /**
     * @var Collection<int, SolutionEvent>
     */
    #[ORM\OneToMany(targetEntity: SolutionEvent::class, mappedBy: 'question', orphanRemoval: true)]
    private Collection $solutionEvents;

    /**
     * @var Collection<int, SolutionVideoEvent>
     */
    #[ORM\OneToMany(targetEntity: SolutionVideoEvent::class, mappedBy: 'question', orphanRemoval: true)]
    private Collection $solutionVideoEvents;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'question', orphanRemoval: true)]
    private Collection $comments;

    public function __construct()
    {
        $this->solutionEvents = new ArrayCollection();
        $this->solutionVideoEvents = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function __toString(): string
    {
        return "#{$this->id}: {$this->title}";
    }

    public function getId(): int
    {
        return (int) $this->id;
    }

    public function getSchema(): ?Schema
    {
        return $this->schema;
    }

    public function setSchema(?Schema $schema): static
    {
        $this->schema = $schema;

        return $this;
    }

    #[Groups(['searchable'])]
    public function getType(): string
    {
        return (string) $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    #[Groups(['searchable'])]
    public function getDifficulty(): QuestionDifficulty
    {
        return $this->difficulty ?? QuestionDifficulty::Unspecified;
    }

    public function setDifficulty(QuestionDifficulty $difficulty): static
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    #[Groups(['searchable'])]
    public function getTitle(): string
    {
        return (string) $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    #[Groups(['searchable'])]
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAnswer(): string
    {
        return (string) $this->answer;
    }

    public function setAnswer(string $answer): static
    {
        $this->answer = $answer;

        return $this;
    }

    public function getSolutionVideo(): ?string
    {
        return $this->solution_video;
    }

    public function setSolutionVideo(?string $solution_video): static
    {
        $this->solution_video = $solution_video;

        return $this;
    }

    /**
     * @return Collection<int, SolutionEvent>
     */
    public function getSolutionEvents(): Collection
    {
        return $this->solutionEvents;
    }

    public function addSolutionEvent(SolutionEvent $solutionEvent): static
    {
        if (!$this->solutionEvents->contains($solutionEvent)) {
            $this->solutionEvents->add($solutionEvent);
            $solutionEvent->setQuestion($this);
        }

        return $this;
    }

    public function removeSolutionEvent(SolutionEvent $solutionEvent): static
    {
        if ($this->solutionEvents->removeElement($solutionEvent)) {
            // set the owning side to null (unless already changed)
            if ($solutionEvent->getQuestion() === $this) {
                $solutionEvent->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SolutionVideoEvent>
     */
    public function getSolutionVideoEvents(): Collection
    {
        return $this->solutionVideoEvents;
    }

    public function addSolutionVideoEvent(SolutionVideoEvent $solutionVideoEvent): static
    {
        if (!$this->solutionVideoEvents->contains($solutionVideoEvent)) {
            $this->solutionVideoEvents->add($solutionVideoEvent);
            $solutionVideoEvent->setQuestion($this);
        }

        return $this;
    }

    public function removeSolutionVideoEvent(SolutionVideoEvent $solutionVideoEvent): static
    {
        if ($this->solutionVideoEvents->removeElement($solutionVideoEvent)) {
            // set the owning side to null (unless already changed)
            if ($solutionVideoEvent->getQuestion() === $this) {
                $solutionVideoEvent->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setQuestion($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getQuestion() === $this) {
                $comment->setQuestion(null);
            }
        }

        return $this;
    }
}
