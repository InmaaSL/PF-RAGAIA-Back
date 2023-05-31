<?php

namespace App\Entity;

use App\Repository\ObjectiveTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: ObjectiveTypeRepository::class)]
class ObjectiveType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['objective_type:main','objective:main'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['objective_type:main','objective:main'])]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Objective::class, orphanRemoval: true)]
    private Collection $objectives;

    public function __construct()
    {
        $this->objectives = new ArrayCollection();
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
     * @return Collection<int, Objective>
     */
    public function getObjectives(): Collection
    {
        return $this->objectives;
    }

    public function addObjective(Objective $objective): self
    {
        if (!$this->objectives->contains($objective)) {
            $this->objectives->add($objective);
            $objective->setType($this);
        }

        return $this;
    }

    public function removeObjective(Objective $objective): self
    {
        if ($this->objectives->removeElement($objective)) {
            // set the owning side to null (unless already changed)
            if ($objective->getType() === $this) {
                $objective->setType(null);
            }
        }

        return $this;
    }
}
