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
use Symfony\Component\Validator\Constraints\NotBlank;

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
    protected ?int $id = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string the hashed password
     */
    #[ORM\Column]
    private string $password;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Group $group = null;

    #[ORM\Column(length: 255, unique: true)]
    #[NotBlank]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $name;

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

    /**
     * @var Collection<int, HintOpenEvent>
     */
    #[ORM\OneToMany(targetEntity: HintOpenEvent::class, mappedBy: 'opener', orphanRemoval: true)]
    private Collection $hintOpenEvents;

    /**
     * @var Collection<int, LoginEvent>
     */
    #[ORM\OneToMany(targetEntity: LoginEvent::class, mappedBy: 'account', orphanRemoval: true)]
    private Collection $loginEvents;

    /**
     * @var Collection<int, Feedback>
     */
    #[ORM\OneToMany(targetEntity: Feedback::class, mappedBy: 'sender')]
    private Collection $feedback;

    /**
     * @var Collection<int, EmailDeliveryEvent>
     */
    #[ORM\OneToMany(targetEntity: EmailDeliveryEvent::class, mappedBy: 'toUser')]
    private Collection $emailDeliveryEvents;

    public function __construct()
    {
        $this->solutionEvents = new ArrayCollection();
        $this->solutionVideoEvents = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->commentLikeEvents = new ArrayCollection();
        $this->hintOpenEvents = new ArrayCollection();
        $this->loginEvents = new ArrayCollection();
        $this->feedback = new ArrayCollection();
        $this->emailDeliveryEvents = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getUserIdentifier();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        \assert('' !== $this->email);

        return $this->email;
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

        $roleUniqueList = array_unique($roles);
        \assert(array_is_list($roleUniqueList), 'The roles must be an list.');

        return $roleUniqueList;
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
        return $this->password;
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

    public function getEmail(): string
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

    public function getLastLoginAt(): ?\DateTimeInterface
    {
        $lastLoginEvent = $this->loginEvents->last();
        if (false === $lastLoginEvent) {
            return null;
        }

        return $lastLoginEvent->getCreatedAt();
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
            // set the owning side to a default class (unless already changed)
            if ($solutionEvent->getSubmitter() === $this) {
                $solutionEvent->setSubmitter(new self());
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
            // set the owning side to a default class (unless already changed)
            if ($solutionVideoEvent->getOpener() === $this) {
                $solutionVideoEvent->setOpener(new self());
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
            // set the owning side to a default class (unless already changed)
            if ($comment->getCommenter() === $this) {
                $comment->setCommenter(new self());
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
            // set the owning side to a default class (unless already changed)
            if ($commentLikeEvent->getLiker() === $this) {
                $commentLikeEvent->setLiker(new self());
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HintOpenEvent>
     */
    public function getHintOpenEvents(): Collection
    {
        return $this->hintOpenEvents;
    }

    public function addHintOpenEvent(HintOpenEvent $hintOpenEvent): static
    {
        if (!$this->hintOpenEvents->contains($hintOpenEvent)) {
            $this->hintOpenEvents->add($hintOpenEvent);
            $hintOpenEvent->setOpener($this);
        }

        return $this;
    }

    public function removeHintOpenEvent(HintOpenEvent $hintOpenEvent): static
    {
        if ($this->hintOpenEvents->removeElement($hintOpenEvent)) {
            // set the owning side to a default class (unless already changed)
            if ($hintOpenEvent->getOpener() === $this) {
                $hintOpenEvent->setOpener(new self());
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LoginEvent>
     */
    public function getLoginEvents(): Collection
    {
        return $this->loginEvents;
    }

    public function addLoginEvent(LoginEvent $loginEvent): static
    {
        if (!$this->loginEvents->contains($loginEvent)) {
            $this->loginEvents->add($loginEvent);
            $loginEvent->setAccount($this);
        }

        return $this;
    }

    public function removeLoginEvent(LoginEvent $loginEvent): static
    {
        if ($this->loginEvents->removeElement($loginEvent)) {
            // set the owning side to a default class (unless already changed)
            if ($loginEvent->getAccount() === $this) {
                $loginEvent->setAccount(new self());
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Feedback>
     */
    public function getFeedback(): Collection
    {
        return $this->feedback;
    }

    public function addFeedback(Feedback $feedback): static
    {
        if (!$this->feedback->contains($feedback)) {
            $this->feedback->add($feedback);
            $feedback->setSender($this);
        }

        return $this;
    }

    public function removeFeedback(Feedback $feedback): static
    {
        if ($this->feedback->removeElement($feedback)) {
            // set the owning side to null
            if ($feedback->getSender() === $this) {
                $feedback->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EmailDeliveryEvent>
     */
    public function getEmailDeliveryEvents(): Collection
    {
        return $this->emailDeliveryEvents;
    }

    public function addEmailDeliveryEvent(EmailDeliveryEvent $emailDeliveryEvent): static
    {
        if (!$this->emailDeliveryEvents->contains($emailDeliveryEvent)) {
            $this->emailDeliveryEvents->add($emailDeliveryEvent);
            $emailDeliveryEvent->setToUser($this);
        }

        return $this;
    }

    public function removeEmailDeliveryEvent(EmailDeliveryEvent $emailDeliveryEvent): static
    {
        if ($this->emailDeliveryEvents->removeElement($emailDeliveryEvent)) {
            // set the owning side to null (unless already changed)
            if ($emailDeliveryEvent->getToUser() === $this) {
                $emailDeliveryEvent->setToUser(null);
            }
        }

        return $this;
    }
}
