<?php

namespace App\Entity;

use App\Repository\ProfessionalCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProfessionalCategoryRepository::class)]
class ProfessionalCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['userProfessionalCategoryCentre:main', 'user:cpc'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['userProfessionalCategoryCentre:main', 'user:cpc'])]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'professionalCategory', targetEntity: UserProfessionalCategoryCentre::class)]
    private Collection $userProfessionalCategoryCentres;

    public function __construct()
    {
        $this->userProfessionalCategoryCentres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $userProfessionalCategoryCentre->setProfessionalCategory($this);
        }

        return $this;
    }

    public function removeUserProfessionalCategoryCentre(UserProfessionalCategoryCentre $userProfessionalCategoryCentre): self
    {
        if ($this->userProfessionalCategoryCentres->removeElement($userProfessionalCategoryCentre)) {
            // set the owning side to null (unless already changed)
            if ($userProfessionalCategoryCentre->getProfessionalCategory() === $this) {
                $userProfessionalCategoryCentre->setProfessionalCategory(null);
            }
        }

        return $this;
    }
}
