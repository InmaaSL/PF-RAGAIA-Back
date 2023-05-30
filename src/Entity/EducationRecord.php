<?php

namespace App\Entity;

use App\Repository\EducationRecordRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EducationRecordRepository::class)]
class EducationRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['educationRecord:main'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'educationRecords')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['educationRecord:main'])]
    private ?user $user = null;

    #[ORM\Column(length: 255)]
    #[Groups(['educationRecord:main'])]
    private ?string $type_record = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['educationRecord:main'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['educationRecord:main'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['educationRecord:main'])]
    private ?bool $isDeleted = null;

    #[ORM\ManyToOne(inversedBy: 'workerEducationRecords')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['educationRecord:main'])]
    private ?user $worker = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTypeRecord(): ?string
    {
        return $this->type_record;
    }

    public function setTypeRecord(string $type_record): self
    {
        $this->type_record = $type_record;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getWorker(): ?user
    {
        return $this->worker;
    }

    public function setWorker(?user $worker): self
    {
        $this->worker = $worker;

        return $this;
    }
}
