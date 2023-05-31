<?php

namespace App\Entity;

use App\Repository\ObjectiveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ObjectiveRepository::class)]
class Objective
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['objective:main'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'objectives')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['objective:main'])]
    private ?user $user = null;

    #[ORM\ManyToOne(inversedBy: 'objectives')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['objective:main'])]
    private ?ObjectiveType $type = null;

    #[ORM\Column(length: 10)]
    #[Groups(['objective:main'])]
    private ?string $month = null;

    #[ORM\Column(length: 10)]
    #[Groups(['objective:main'])]
    private ?string $year = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['objective:main'])]
    private ?string $need_detected = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['objective:main'])]
    private ?string $objective = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['objective:main'])]
    private ?string $indicator = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['objective:main'])]
    private ?string $valuation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['objective:main'])]
    private ?string $comment = null;

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

    public function getType(): ?ObjectiveType
    {
        return $this->type;
    }

    public function setType(?ObjectiveType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getMonth(): ?string
    {
        return $this->month;
    }

    public function setMonth(string $month): self
    {
        $this->month = $month;

        return $this;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(string $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getNeedDetected(): ?string
    {
        return $this->need_detected;
    }

    public function setNeedDetected(string $need_detected): self
    {
        $this->need_detected = $need_detected;

        return $this;
    }

    public function getObjective(): ?string
    {
        return $this->objective;
    }

    public function setObjective(string $objective): self
    {
        $this->objective = $objective;

        return $this;
    }

    public function getIndicator(): ?string
    {
        return $this->indicator;
    }

    public function setIndicator(string $indicator): self
    {
        $this->indicator = $indicator;

        return $this;
    }

    public function getValuation(): ?string
    {
        return $this->valuation;
    }

    public function setValuation(string $valuation): self
    {
        $this->valuation = $valuation;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
