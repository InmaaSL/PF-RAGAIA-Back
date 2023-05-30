<?php

namespace App\Entity;

use App\Repository\EducationDocumentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EducationDocumentRepository::class)]
class EducationDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['educationDocument:main'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'educationDocuments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['educationDocument:main'])]
    private ?user $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['educationDocument:main'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    #[Groups(['educationDocument:main'])]
    private ?string $name_file = null;

    #[ORM\Column(length: 255)]
    #[Groups(['educationDocument:main'])]
    private ?string $file = null;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getNameFile(): ?string
    {
        return $this->name_file;
    }

    public function setNameFile(string $name_file): self
    {
        $this->name_file = $name_file;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(string $file): self
    {
        $this->file = $file;

        return $this;
    }
}
