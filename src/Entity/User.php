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
    #[Groups(['user:main', 'user:cpc', 'expedient:main', 'expedient:main', 
    'healthDocument:main', 'objective:main', 'calendar:main', 'post:main',
    'postMessage:main', 'paidRegister:main'])]
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
    #[Groups(['user:main', 'user:cpc','healthRecord:main', 'educationRecord:main', 
    'objective:main', 'calendar:main', 'post:main', 'postMessage:main', 'paidRegister:main'])]
    private ?UserData $userData = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserProfessionalCategoryCentre::class)]
    #[Groups(['user:main'])]
    private Collection $userProfessionalCategoryCentres;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Expedient::class)]
    private Collection $expedients;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: HealthRecords::class, orphanRemoval: true)]
    private Collection $healthRecords;

    #[ORM\OneToMany(mappedBy: 'worker', targetEntity: HealthRecords::class, orphanRemoval: true)]
    private Collection $workerHealthRecords;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: HealthDocument::class, orphanRemoval: true)]
    private Collection $healthDocuments;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: EducationDocument::class, orphanRemoval: true)]
    private Collection $educationDocuments;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: EducationRecord::class, orphanRemoval: true)]
    private Collection $educationRecords;

    #[ORM\OneToMany(mappedBy: 'worker', targetEntity: EducationRecord::class, orphanRemoval: true)]
    private Collection $workerEducationRecords;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Objective::class, orphanRemoval: true)]
    private Collection $objectives;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CalendarEntry::class)]
    private Collection $calendarEntriesUser;

    #[ORM\OneToMany(mappedBy: 'worker', targetEntity: CalendarEntry::class)]
    private Collection $calendarEntriesWorker;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Post::class, orphanRemoval: true)]
    private Collection $posts;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PostMessage::class, orphanRemoval: true)]
    private Collection $postMessages;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PayRegister::class, orphanRemoval: true)]
    private Collection $payRegisters;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $confirmation_token = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password_request_token = null;

    public function __construct()
    {
        $this->userProfessionalCategoryCentres = new ArrayCollection();
        $this->expedients = new ArrayCollection();
        $this->healthRecords = new ArrayCollection();
        $this->workerHealthRecords = new ArrayCollection();
        $this->healthDocuments = new ArrayCollection();
        $this->educationDocuments = new ArrayCollection();
        $this->educationRecords = new ArrayCollection();
        $this->workerEducationRecords = new ArrayCollection();
        $this->objectives = new ArrayCollection();
        $this->calendarEntriesUser = new ArrayCollection();
        $this->calendarEntriesWorker = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->postMessages = new ArrayCollection();
        $this->payRegisters = new ArrayCollection();
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
     * @return Collection<int, UserProfessionalCategoryCentre>
     */
    public function getUserProfessionalCategoryCentres(): Collection
    {
        return $this->userProfessionalCategoryCentres;
    }

    public function addUserProfessionalCategoryCentre(UserProfessionalCategoryCentre $userProfessionalCategoryCentre): self
    {
        if (!$this->userProfessionalCategoryCentres->contains($userProfessionalCategoryCentre)) {
            $this->userProfessionalCategoryCentres->add($userProfessionalCategoryCentre);
            $userProfessionalCategoryCentre->setUser($this);
        }

        return $this;
    }

    public function removeUserProfessionalCategoryCentre(UserProfessionalCategoryCentre $userProfessionalCategoryCentre): self
    {
        if ($this->userProfessionalCategoryCentres->removeElement($userProfessionalCategoryCentre)) {
            // set the owning side to null (unless already changed)
            if ($userProfessionalCategoryCentre->getUser() === $this) {
                $userProfessionalCategoryCentre->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Expedient>
     */
    public function getExpedients(): Collection
    {
        return $this->expedients;
    }

    public function addExpedient(Expedient $expedient): self
    {
        if (!$this->expedients->contains($expedient)) {
            $this->expedients->add($expedient);
            $expedient->setUser($this);
        }

        return $this;
    }

    public function removeExpedient(Expedient $expedient): self
    {
        if ($this->expedients->removeElement($expedient)) {
            // set the owning side to null (unless already changed)
            if ($expedient->getUser() === $this) {
                $expedient->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HealthRecords>
     */
    public function getHealthRecords(): Collection
    {
        return $this->healthRecords;
    }

    public function addHealthRecord(HealthRecords $healthRecord): self
    {
        if (!$this->healthRecords->contains($healthRecord)) {
            $this->healthRecords->add($healthRecord);
            $healthRecord->setUser($this);
        }

        return $this;
    }

    public function removeHealthRecord(HealthRecords $healthRecord): self
    {
        if ($this->healthRecords->removeElement($healthRecord)) {
            // set the owning side to null (unless already changed)
            if ($healthRecord->getUser() === $this) {
                $healthRecord->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HealthRecords>
     */
    public function getWorkerHealthRecords(): Collection
    {
        return $this->workerHealthRecords;
    }

    public function addWorkerHealthRecord(HealthRecords $workerHealthRecord): self
    {
        if (!$this->workerHealthRecords->contains($workerHealthRecord)) {
            $this->workerHealthRecords->add($workerHealthRecord);
            $workerHealthRecord->setWorker($this);
        }

        return $this;
    }

    public function removeWorkerHealthRecord(HealthRecords $workerHealthRecord): self
    {
        if ($this->workerHealthRecords->removeElement($workerHealthRecord)) {
            // set the owning side to null (unless already changed)
            if ($workerHealthRecord->getWorker() === $this) {
                $workerHealthRecord->setWorker(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HealthDocument>
     */
    public function getHealthDocuments(): Collection
    {
        return $this->healthDocuments;
    }

    public function addHealthDocument(HealthDocument $healthDocument): self
    {
        if (!$this->healthDocuments->contains($healthDocument)) {
            $this->healthDocuments->add($healthDocument);
            $healthDocument->setUser($this);
        }

        return $this;
    }

    public function removeHealthDocument(HealthDocument $healthDocument): self
    {
        if ($this->healthDocuments->removeElement($healthDocument)) {
            // set the owning side to null (unless already changed)
            if ($healthDocument->getUser() === $this) {
                $healthDocument->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EducationDocument>
     */
    public function getEducationDocuments(): Collection
    {
        return $this->educationDocuments;
    }

    public function addEducationDocument(EducationDocument $educationDocument): self
    {
        if (!$this->educationDocuments->contains($educationDocument)) {
            $this->educationDocuments->add($educationDocument);
            $educationDocument->setUser($this);
        }

        return $this;
    }

    public function removeEducationDocument(EducationDocument $educationDocument): self
    {
        if ($this->educationDocuments->removeElement($educationDocument)) {
            // set the owning side to null (unless already changed)
            if ($educationDocument->getUser() === $this) {
                $educationDocument->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EducationRecord>
     */
    public function getEducationRecords(): Collection
    {
        return $this->educationRecords;
    }

    public function addEducationRecord(EducationRecord $educationRecord): self
    {
        if (!$this->educationRecords->contains($educationRecord)) {
            $this->educationRecords->add($educationRecord);
            $educationRecord->setUser($this);
        }

        return $this;
    }

    public function removeEducationRecord(EducationRecord $educationRecord): self
    {
        if ($this->educationRecords->removeElement($educationRecord)) {
            // set the owning side to null (unless already changed)
            if ($educationRecord->getUser() === $this) {
                $educationRecord->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EducationRecord>
     */
    public function getWorkerEducationRecords(): Collection
    {
        return $this->workerEducationRecords;
    }

    public function addWorkerEducationRecord(EducationRecord $workerEducationRecord): self
    {
        if (!$this->workerEducationRecords->contains($workerEducationRecord)) {
            $this->workerEducationRecords->add($workerEducationRecord);
            $workerEducationRecord->setWorker($this);
        }

        return $this;
    }

    public function removeWorkerEducationRecord(EducationRecord $workerEducationRecord): self
    {
        if ($this->workerEducationRecords->removeElement($workerEducationRecord)) {
            // set the owning side to null (unless already changed)
            if ($workerEducationRecord->getWorker() === $this) {
                $workerEducationRecord->setWorker(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Objective>
     */
    public function getObjectives(): Collection
    {
        return $this->objectives;
    }

    public function addObjective(Objective $objective): self
    {
        if (!$this->objectives->contains($objective)) {
            $this->objectives->add($objective);
            $objective->setUser($this);
        }

        return $this;
    }

    public function removeObjective(Objective $objective): self
    {
        if ($this->objectives->removeElement($objective)) {
            // set the owning side to null (unless already changed)
            if ($objective->getUser() === $this) {
                $objective->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CalendarEntry>
     */
    public function getCalendarEntriesUser(): Collection
    {
        return $this->calendarEntriesUser;
    }

    public function addCalendarEntriesUser(CalendarEntry $calendarEntriesUser): self
    {
        if (!$this->calendarEntriesUser->contains($calendarEntriesUser)) {
            $this->calendarEntriesUser->add($calendarEntriesUser);
            $calendarEntriesUser->setUser($this);
        }

        return $this;
    }

    public function removeCalendarEntriesUser(CalendarEntry $calendarEntriesUser): self
    {
        if ($this->calendarEntriesUser->removeElement($calendarEntriesUser)) {
            // set the owning side to null (unless already changed)
            if ($calendarEntriesUser->getUser() === $this) {
                $calendarEntriesUser->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CalendarEntry>
     */
    public function getCalendarEntriesWorker(): Collection
    {
        return $this->calendarEntriesWorker;
    }

    public function addCalendarEntriesWorker(CalendarEntry $calendarEntriesWorker): self
    {
        if (!$this->calendarEntriesWorker->contains($calendarEntriesWorker)) {
            $this->calendarEntriesWorker->add($calendarEntriesWorker);
            $calendarEntriesWorker->setWorker($this);
        }

        return $this;
    }

    public function removeCalendarEntriesWorker(CalendarEntry $calendarEntriesWorker): self
    {
        if ($this->calendarEntriesWorker->removeElement($calendarEntriesWorker)) {
            // set the owning side to null (unless already changed)
            if ($calendarEntriesWorker->getWorker() === $this) {
                $calendarEntriesWorker->setWorker(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

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
            $postMessage->setUser($this);
        }

        return $this;
    }

    public function removePostMessage(PostMessage $postMessage): self
    {
        if ($this->postMessages->removeElement($postMessage)) {
            // set the owning side to null (unless already changed)
            if ($postMessage->getUser() === $this) {
                $postMessage->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PayRegister>
     */
    public function getPayRegisters(): Collection
    {
        return $this->payRegisters;
    }

    public function addPayRegister(PayRegister $payRegister): self
    {
        if (!$this->payRegisters->contains($payRegister)) {
            $this->payRegisters->add($payRegister);
            $payRegister->setUser($this);
        }

        return $this;
    }

    public function removePayRegister(PayRegister $payRegister): self
    {
        if ($this->payRegisters->removeElement($payRegister)) {
            // set the owning side to null (unless already changed)
            if ($payRegister->getUser() === $this) {
                $payRegister->setUser(null);
            }
        }

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmation_token;
    }

    public function setConfirmationToken(?string $confirmation_token): self
    {
        $this->confirmation_token = $confirmation_token;

        return $this;
    }

    public function getPasswordRequestToken(): ?string
    {
        return $this->password_request_token;
    }

    public function setPasswordRequestToken(?string $password_request_token): self
    {
        $this->password_request_token = $password_request_token;

        return $this;
    }
}
