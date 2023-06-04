<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['post:main'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['post:main'])]
    private ?TopicPost $topic = null;

    #[ORM\Column(length: 255)]
    #[Groups(['post:main'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['post:main'])]
    private ?string $message = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['post:main'])]
    private ?user $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['post:main'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: PostMessage::class, orphanRemoval: true)]
    private Collection $postMessages;

    public function __construct()
    {
        $this->postMessages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTopic(): ?TopicPost
    {
        return $this->topic;
    }

    public function setTopic(?TopicPost $topic): self
    {
        $this->topic = $topic;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection<int, PostMessage>
     */
    public function getPostMessages(): Collection
    {
        return $this->postMessages;
    }

    public function addPostMessage(PostMessage $postMessage): self
    {
        if (!$this->postMessages->contains($postMessage)) {
            $this->postMessages->add($postMessage);
            $postMessage->setPost($this);
        }

        return $this;
    }

    public function removePostMessage(PostMessage $postMessage): self
    {
        if ($this->postMessages->removeElement($postMessage)) {
            // set the owning side to null (unless already changed)
            if ($postMessage->getPost() === $this) {
                $postMessage->setPost(null);
            }
        }

        return $this;
    }
}
