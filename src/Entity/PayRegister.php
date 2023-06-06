<?php

namespace App\Entity;

use App\Repository\PayRegisterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PayRegisterRepository::class)]
class PayRegister
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['paidRegister:main'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'payRegisters')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['paidRegister:main'])]
    private ?user $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['paidRegister:main'])]
    private ?\DateTimeInterface $week_start = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['paidRegister:main'])]
    private ?\DateTimeInterface $week_end = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['paidRegister:main'])]
    private ?float $base_pay = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['paidRegister:main'])]
    private ?float $max_pay = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['paidRegister:main'])]
    private ?int $percent_measure = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['paidRegister:main'])]
    private ?float $base_pay_rest = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['paidRegister:main'])]
    private ?float $max_incentive = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['paidRegister:main'])]
    private ?float $incentive = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['paidRegister:main'])]
    private ?float $max_study = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['paidRegister:main'])]
    private ?float $study = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['paidRegister:main'])]
    private ?float $max_bedroom = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['paidRegister:main'])]
    private ?float $bedroom = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['paidRegister:main'])]
    private ?float $total_incentive = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['paidRegister:main'])]
    private ?string $negative_pay = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['paidRegister:main'])]
    private ?float $total_pay = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['paidRegister:main'])]
    private ?float $discount = null;

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

    public function getWeekStart(): ?\DateTimeInterface
    {
        return $this->week_start;
    }

    public function setWeekStart(\DateTimeInterface $week_start): self
    {
        $this->week_start = $week_start;

        return $this;
    }

    public function getWeekEnd(): ?\DateTimeInterface
    {
        return $this->week_end;
    }

    public function setWeekEnd(\DateTimeInterface $week_end): self
    {
        $this->week_end = $week_end;

        return $this;
    }

    public function getBasePay(): ?float
    {
        return $this->base_pay;
    }

    public function setBasePay(?float $base_pay): self
    {
        $this->base_pay = $base_pay;

        return $this;
    }

    public function getMaxPay(): ?float
    {
        return $this->max_pay;
    }

    public function setMaxPay(?float $max_pay): self
    {
        $this->max_pay = $max_pay;

        return $this;
    }

    public function getPercentMeasure(): ?int
    {
        return $this->percent_measure;
    }

    public function setPercentMeasure(?int $percent_measure): self
    {
        $this->percent_measure = $percent_measure;

        return $this;
    }

    public function getBasePayRest(): ?float
    {
        return $this->base_pay_rest;
    }

    public function setBasePayRest(?float $base_pay_rest): self
    {
        $this->base_pay_rest = $base_pay_rest;

        return $this;
    }

    public function getMaxIncentive(): ?float
    {
        return $this->max_incentive;
    }

    public function setMaxIncentive(?float $max_incentive): self
    {
        $this->max_incentive = $max_incentive;

        return $this;
    }

    public function getIncentive(): ?float
    {
        return $this->incentive;
    }

    public function setIncentive(?float $incentive): self
    {
        $this->incentive = $incentive;

        return $this;
    }

    public function getMaxStudy(): ?float
    {
        return $this->max_study;
    }

    public function setMaxStudy(?float $max_study): self
    {
        $this->max_study = $max_study;

        return $this;
    }

    public function getStudy(): ?float
    {
        return $this->study;
    }

    public function setStudy(?float $study): self
    {
        $this->study = $study;

        return $this;
    }

    public function getMaxBedroom(): ?float
    {
        return $this->max_bedroom;
    }

    public function setMaxBedroom(?float $max_bedroom): self
    {
        $this->max_bedroom = $max_bedroom;

        return $this;
    }

    public function getBedroom(): ?float
    {
        return $this->bedroom;
    }

    public function setBedroom(?float $bedroom): self
    {
        $this->bedroom = $bedroom;

        return $this;
    }

    public function getTotalIncentive(): ?float
    {
        return $this->total_incentive;
    }

    public function setTotalIncentive(?float $total_incentive): self
    {
        $this->total_incentive = $total_incentive;

        return $this;
    }

    public function getNegativePay(): ?string
    {
        return $this->negative_pay;
    }

    public function setNegativePay(?string $negative_pay): self
    {
        $this->negative_pay = $negative_pay;

        return $this;
    }

    public function getTotalPay(): ?float
    {
        return $this->total_pay;
    }

    public function setTotalPay(?float $total_pay): self
    {
        $this->total_pay = $total_pay;

        return $this;
    }

    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    public function setDiscount(?float $discount): self
    {
        $this->discount = $discount;

        return $this;
    }
}
