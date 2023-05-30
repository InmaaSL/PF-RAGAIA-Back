<?php

namespace App\Entity;

use App\Repository\HealthRecordsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: HealthRecordsRepository::class)]
class HealthRecords
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['healthRecord:main'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'healthRecords')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['healthRecord:main'])]
    private ?User $user = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['healthRecord:main'])]
    private ?string $type_consultation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['healthRecord:main'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['healthRecord:main'])]
    private ?string $what_happens = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['healthRecord:main'])]
    private ?string $diagnostic = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['healthRecord:main'])]
    private ?string $treatment = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['healthRecord:main'])]
    private ?string $revision = null;

    #[ORM\ManyToOne(inversedBy: 'workerHealthRecords')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['healthRecord:main'])]
    private ?user $worker = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['healthRecord:main'])]
    private ?bool $isDeleted = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['healthRecord:main'])]
    private ?\DateTimeInterface $consultation_date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTypeConsultation(): ?string
    {
        return $this->type_consultation;
    }

    public function setTypeConsultation(?string $type_consultation): self
    {
        $this->type_consultation = $type_consultation;

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

    public function getWhatHappens(): ?string
    {
        return $this->what_happens;
    }

    public function setWhatHappens(string $what_happens): self
    {
        $this->what_happens = $what_happens;

        return $this;
    }

    public function getDiagnostic(): ?string
    {
        return $this->diagnostic;
    }

    public function setDiagnostic(string $diagnostic): self
    {
        $this->diagnostic = $diagnostic;

        return $this;
    }

    public function getTreatment(): ?string
    {
        return $this->treatment;
    }

    public function setTreatment(string $treatment): self
    {
        $this->treatment = $treatment;

        return $this;
    }

    public function getRevision(): ?string
    {
        return $this->revision;
    }

    public function setRevision(string $revision): self
    {
        $this->revision = $revision;

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

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getConsultationDate(): ?\DateTimeInterface
    {
        return $this->consultation_date;
    }

    public function setConsultationDate(\DateTimeInterface $consultation_date): self
    {
        $this->consultation_date = $consultation_date;

        return $this;
    }
}
