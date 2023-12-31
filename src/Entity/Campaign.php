<?php

namespace App\Entity;

use App\Repository\CampaignRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: CampaignRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Campaign implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'campaign', targetEntity: Child::class, orphanRemoval: true)]
    private Collection $children;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];
    #[ORM\Column]
    private ?int $numberOfMale = null;

    #[ORM\Column]
    private ?int $numberOfFemale = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $mail = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTimeImmutable());
        }
    }

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Child>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @return Collection<int, Child>
     */
    public function getShuffledChildren(): array
    {
        $entries = $this->children->toArray();
        shuffle($entries);
        $entriesGifted = array_filter($entries, function (Child $child) {
            return $child->getDonor() !== null;
        });
        $entriesUngifted = array_filter($entries, function (Child $child) {
            return $child->getDonor() === null;
        });
        return array_merge($entriesUngifted, $entriesGifted);
    }

    public function addChild(Child $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setCampaign($this);
        }

        return $this;
    }

    public function removeChild(Child $child): static
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getCampaign() === $this) {
                $child->setCampaign(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getNumberOfMale(): ?int
    {
        return $this->numberOfMale;
    }

    public function setNumberOfMale(int $numberOfMale): static
    {
        $this->numberOfMale = $numberOfMale;

        return $this;
    }

    public function getNumberOfFemale(): ?int
    {
        return $this->numberOfFemale;
    }

    public function setNumberOfFemale(int $numberOfFemale): static
    {
        $this->numberOfFemale = $numberOfFemale;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getRoles(): array
    {

        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
        // TODO: Implement getRoles() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        // TODO: Implement getUserIdentifier() method.
        return $this->getId();
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
