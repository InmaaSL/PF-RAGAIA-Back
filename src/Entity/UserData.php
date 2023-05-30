<?php

namespace App\Entity;

use App\Repository\UserDataRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserDataRepository::class)]
class UserData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:main'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'userData', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user:main'])]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:main', 'user:cpc', 'healthRecord:main'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:main', 'healthRecord:main'])]
    private ?string $surname = null;

    #[ORM\Column(length: 20)]
    #[Groups(['user:main'])]
    private ?string $dni = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:main'])]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['user:main'])]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:main'])]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:main'])]
    private ?string $town = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:main'])]
    private ?string $province = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['user:main'])]
    private ?string $postal_code = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['user:main'])]
    private ?\DateTimeInterface $birth_date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['user:main'])]
    private ?\DateTimeInterface $admission_date = null;

    #[ORM\ManyToOne(inversedBy: 'userdatas')]
    #[Groups(['user:main'], ['custody:main'])]
    private ?Custody $custody = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['user:main'])]
    private ?string $case_number = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(user $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function setDni(string $dni): self
    {
        $this->dni = $dni;

        return $this;
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function setTown(?string $town): self
    {
        $this->town = $town;

        return $this;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(?string $province): self
    {
        $this->province = $province;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postal_code;
    }

    public function setPostalCode(?string $postal_code): self
    {
        $this->postal_code = $postal_code;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birth_date;
    }

    public function setBirthDate(?\DateTimeInterface $birth_date): self
    {
        $this->birth_date = $birth_date;

        return $this;
    }

    public function getAdmissionDate(): ?\DateTimeInterface
    {
        return $this->admission_date;
    }

    public function setAdmissionDate(?\DateTimeInterface $admission_date): self
    {
        $this->admission_date = $admission_date;

        return $this;
    }

    public function getCustody(): ?custody
    {
        return $this->custody;
    }

    public function setCustody(?custody $custody): self
    {
        $this->custody = $custody;

        return $this;
    }

    public function getCaseNumber(): ?string
    {
        return $this->case_number;
    }

    public function setCaseNumber(?string $case_number): self
    {
        $this->case_number = $case_number;

        return $this;
    }
}
