<?php

namespace App\Entity;

use App\Repository\UserProfessionalCategoryCentreRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserProfessionalCategoryCentreRepository::class)]
class UserProfessionalCategoryCentre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userProfessionalCategoryCentres')]
    #[Groups(['userProfessionalCategoryCentre:main', 'user:cpc', 'user:main'])]
    private ?user $user = null;

    #[ORM\ManyToOne(inversedBy: 'userProfessionalCategoryCentres')]
    #[Groups(['userProfessionalCategoryCentre:main', 'user:cpc', 'user:main'])]
    private ?ProfessionalCategory $professionalCategory = null;

    #[ORM\ManyToOne(inversedBy: 'userProfessionalCategoryCentres')]
    #[Groups(['userProfessionalCategoryCentre:main', 'user:cpc', 'user:main'])]
    private ?Centre $centre = null;


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

    public function getProfessionalCategory(): ?ProfessionalCategory
    {
        return $this->professionalCategory;
    }

    public function setProfessionalCategory(?ProfessionalCategory $professionalCategory): self
    {
        $this->professionalCategory = $professionalCategory;

        return $this;
    }

    public function getCentre(): ?Centre
    {
        return $this->centre;
    }

    public function setCentre(?Centre $centre): self
    {
        $this->centre = $centre;

        return $this;
    }
}
