<?php

namespace App\Entity;

use App\Repository\CalendarEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: CalendarEntryRepository::class)]
class CalendarEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['calendar:main'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['calendar:main'])]
    private ?\DateTimeInterface $entry_date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['calendar:main'])]
    private ?\DateTimeInterface $entry_time = null;

    #[ORM\Column]
    #[Groups(['calendar:main'])]
    private ?bool $all_day = null;

    #[ORM\Column(length: 255)]
    #[Groups(['calendar:main'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['calendar:main'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'calendarEntriesUser')]
    #[Groups(['calendar:main'])]
    private ?user $user = null;

    #[ORM\ManyToOne(inversedBy: 'calendarEntriesWorker')]
    #[Groups(['calendar:main'])]
    private ?user $worker = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['calendar:main'])]
    private ?string $place = null;

    #[ORM\Column]
    #[Groups(['calendar:main'])]
    private ?bool $remember = null;

    #[ORM\Column(length: 255)]
    #[Groups(['calendar:main'])]
    private ?string $register_type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntryDate(): ?\DateTimeInterface
    {
        return $this->entry_date;
    }

    public function setEntryDate(\DateTimeInterface $entry_date): self
    {
        $this->entry_date = $entry_date;

        return $this;
    }

    public function getEntryTime(): ?\DateTimeInterface
    {
        return $this->entry_time;
    }

    public function setEntryTime(?\DateTimeInterface $entry_time): self
    {
        $this->entry_time = $entry_time;

        return $this;
    }

    public function isAllDay(): ?bool
    {
        return $this->all_day;
    }

    public function setAllDay(bool $all_day): self
    {
        $this->all_day = $all_day;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getWorker(): ?user
    {
        return $this->worker;
    }

    public function setWorker(?user $worker): self
    {
        $this->worker = $worker;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(?string $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function isRemember(): ?bool
    {
        return $this->remember;
    }

    public function setRemember(bool $remember): self
    {
        $this->remember = $remember;

        return $this;
    }

    public function getRegisterType(): ?string
    {
        return $this->register_type;
    }

    public function setRegisterType(string $register_type): self
    {
        $this->register_type = $register_type;

        return $this;
    }
}
