<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:main'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:main'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['user:main'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    #[Groups(['user:main'])]
    private ?bool $deleted = null;

    #[ORM\Column]
    #[Groups(['user:main'])]
    private ?bool $confirmed = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Groups(['user:main'])]
    private ?UserData $userData = null;

    #[ORM\ManyToMany(targetEntity: ProfesionalCategory::class, inversedBy: 'users')]
    #[Groups(['user:main'])]
    private Collection $profesional_category;

    #[ORM\ManyToMany(targetEntity: Centre::class, inversedBy: 'users')]
    #[Groups(['user:main'])]
    private Collection $workplace;

    public function __construct()
    {
        $this->profesional_category = new ArrayCollection();
        $this->workplace = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
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

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function isConfirmed(): ?bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(bool $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    public function getUserData(): ?UserData
    {
        return $this->userData;
    }

    public function setUserData(UserData $userData): self
    {
        // set the owning side of the relation if necessary
        if ($userData->getUser() !== $this) {
            $userData->setUser($this);
        }

        $this->userData = $userData;

        return $this;
    }

    /**
     * @return Collection<int, ProfesionalCategory>
     */
    public function getProfesionalCategory(): Collection
    {
        return $this->profesional_category;
    }

    public function addProfesionalCategory(ProfesionalCategory $profesionalCategory): self
    {
        if (!$this->profesional_category->contains($profesionalCategory)) {
            $this->profesional_category->add($profesionalCategory);
        }

        return $this;
    }

    public function removeProfesionalCategory(ProfesionalCategory $profesionalCategory): self
    {
        $this->profesional_category->removeElement($profesionalCategory);

        return $this;
    }

    /**
     * @return Collection<int, Centre>
     */
    public function getWorkplace(): Collection
    {
        return $this->workplace;
    }

    public function addWorkplace(Centre $workplace): self
    {
        if (!$this->workplace->contains($workplace)) {
            $this->workplace->add($workplace);
        }

        return $this;
    }

    public function removeWorkplace(Centre $workplace): self
    {
        $this->workplace->removeElement($workplace);

        return $this;
    }
}
