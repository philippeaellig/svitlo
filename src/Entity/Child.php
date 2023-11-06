<?php

namespace App\Entity;

use App\Repository\ChildRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChildRepository::class)]
class Child
{

    public static array $GENDER = [
        1 => 'male',
        2 => 'female',
    ];
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $age = null;

    #[ORM\Column(nullable: true)]
    private ?int $gender = null;

    #[ORM\ManyToOne(inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campaign $campaign = null;

    #[ORM\OneToOne(mappedBy: 'child', cascade: ['persist', 'remove'])]
    private ?Donor $donor = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getAge(): ?string
    {
        return $this->age;
    }

    public function setAge(?string $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setGender(?int $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(?Campaign $campaign): static
    {
        $this->campaign = $campaign;

        return $this;
    }

    public function getDonor(): ?Donor
    {
        return $this->donor;
    }

    public function setDonor(Donor $donor): static
    {
        // set the owning side of the relation if necessary
        if ($donor->getChild() !== $this) {
            $donor->setChild($this);
        }

        $this->donor = $donor;

        return $this;
    }
}
