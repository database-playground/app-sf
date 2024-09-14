<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\WithModelTimeInfo;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use WithModelTimeInfo;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Group $group = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, SolutionEvent>
     */
    #[ORM\OneToMany(targetEntity: SolutionEvent::class, mappedBy: 'submitter', orphanRemoval: true)]
    private Collection $solutionEvents;

    /**
     * @var Collection<int, SolutionVideoEvent>
     */
    #[ORM\OneToMany(targetEntity: SolutionVideoEvent::class, mappedBy: 'opener', orphanRemoval: true)]
    private Collection $solutionVideoEvents;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'commenter', orphanRemoval: true)]
    private Collection $comments;

    /**
     * @var Collection<int, CommentLikeEvent>
     */
    #[ORM\OneToMany(targetEntity: CommentLikeEvent::class, mappedBy: 'liker', orphanRemoval: true)]
    private Collection $commentLikeEvents;

    public function __construct()
    {
        $this->solutionEvents = new ArrayCollection();
        $this->solutionVideoEvents = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->commentLikeEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        \assert(!empty($this->email), 'The email should not be empty.');

        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group): static
    {
        $this->group = $group;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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
            $solutionEvent->setSubmitter($this);
        }

        return $this;
    }

    public function removeSolutionEvent(SolutionEvent $solutionEvent): static
    {
        if ($this->solutionEvents->removeElement($solutionEvent)) {
            // set the owning side to null (unless already changed)
            if ($solutionEvent->getSubmitter() === $this) {
                $solutionEvent->setSubmitter(null);
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
            $solutionVideoEvent->setOpener($this);
        }

        return $this;
    }

    public function removeSolutionVideoEvent(SolutionVideoEvent $solutionVideoEvent): static
    {
        if ($this->solutionVideoEvents->removeElement($solutionVideoEvent)) {
            // set the owning side to null (unless already changed)
            if ($solutionVideoEvent->getOpener() === $this) {
                $solutionVideoEvent->setOpener(null);
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
            $comment->setCommenter($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getCommenter() === $this) {
                $comment->setCommenter(null);
            }
        }

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
            $commentLikeEvent->setLiker($this);
        }

        return $this;
    }

    public function removeCommentLikeEvent(CommentLikeEvent $commentLikeEvent): static
    {
        if ($this->commentLikeEvents->removeElement($commentLikeEvent)) {
            // set the owning side to null (unless already changed)
            if ($commentLikeEvent->getLiker() === $this) {
                $commentLikeEvent->setLiker(null);
            }
        }

        return $this;
    }
}
