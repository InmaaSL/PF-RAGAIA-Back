<?php

namespace App\Entity;

use App\Repository\CustodyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CustodyRepository::class)]
class Custody
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:main'], ['custody:main'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:main'], ['custody:main'])]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'custody', targetEntity: Userdata::class)]
    // #[Groups(['user:main'], ['custody:main'])]
    private Collection $userdatas;

    public function __construct()
    {
        $this->userdatas = new ArrayCollection();
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
     * @return Collection<int, Userdata>
     */
    public function getUserdatas(): Collection
    {
        return $this->userdatas;
    }

    public function addUserdata(Userdata $userdata): self
    {
        if (!$this->userdatas->contains($userdata)) {
            $this->userdatas->add($userdata);
            $userdata->setCustody($this);
        }

        return $this;
    }

    public function removeUserdata(Userdata $userdata): self
    {
        if ($this->userdatas->removeElement($userdata)) {
            // set the owning side to null (unless already changed)
            if ($userdata->getCustody() === $this) {
                $userdata->setCustody(null);
            }
        }

        return $this;
    }
}
