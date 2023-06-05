<?php

namespace App\Entity;

use App\Repository\PaidManagementRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PaidManagementRepository::class)]
class PaidManagement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['paidManagement:main'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['paidManagement:main'])]
    private ?int $age = null;

    #[ORM\Column]
    #[Groups(['paidManagement:main'])]
    private ?float $max_pay = null;

    #[ORM\Column]
    #[Groups(['paidManagement:main'])]
    private ?float $min_pay = null;

    #[ORM\Column]
    #[Groups(['paidManagement:main'])]
    private ?float $incentive = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getMaxPay(): ?float
    {
        return $this->max_pay;
    }

    public function setMaxPay(float $max_pay): self
    {
        $this->max_pay = $max_pay;

        return $this;
    }

    public function getMinPay(): ?float
    {
        return $this->min_pay;
    }

    public function setMinPay(float $min_pay): self
    {
        $this->min_pay = $min_pay;

        return $this;
    }

    public function getIncentive(): ?float
    {
        return $this->incentive;
    }

    public function setIncentive(float $incentive): self
    {
        $this->incentive = $incentive;

        return $this;
    }
}
