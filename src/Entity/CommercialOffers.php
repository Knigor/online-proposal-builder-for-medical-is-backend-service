<?php

namespace App\Entity;

use App\Repository\CommercialOffersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommercialOffersRepository::class)]
class CommercialOffers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'commercialOffers')]
    private Collection $userId;



    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Document>
     */
    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'commercialOffer')]
    private Collection $documents;

    #[ORM\Column]
    private ?int $totalPrice = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $acceptedAt = null;

    /**
     * @var Collection<int, CommercialOffersItems>
     */
    #[ORM\OneToMany(targetEntity: CommercialOffersItems::class, mappedBy: 'commercialOfferId', orphanRemoval: true)]
    private Collection $commercialOffersItems;

    public function __construct()
    {
        $this->userId = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->commercialOffersItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUserId(): Collection
    {
        return $this->userId;
    }

    public function addUserId(User $userId): static
    {
        if (!$this->userId->contains($userId)) {
            $this->userId->add($userId);
        }

        return $this;
    }

    public function removeUserId(User $userId): static
    {
        $this->userId->removeElement($userId);

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
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

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setCommercialOffer($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): static
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getCommercialOffer() === $this) {
                $document->setCommercialOffer(null);
            }
        }

        return $this;
    }

    public function getTotalPrice(): ?int
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(int $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    public function setAcceptedAt(?\DateTimeImmutable $acceptedAt): static
    {
        $this->acceptedAt = $acceptedAt;

        return $this;
    }

    /**
     * @return Collection<int, CommercialOffersItems>
     */
    public function getCommercialOffersItems(): Collection
    {
        return $this->commercialOffersItems;
    }

    public function addCommercialOffersItem(CommercialOffersItems $commercialOffersItem): static
    {
        if (!$this->commercialOffersItems->contains($commercialOffersItem)) {
            $this->commercialOffersItems->add($commercialOffersItem);
            $commercialOffersItem->setCommercialOfferId($this);
        }

        return $this;
    }

    public function removeCommercialOffersItem(CommercialOffersItems $commercialOffersItem): static
    {
        if ($this->commercialOffersItems->removeElement($commercialOffersItem)) {
            // set the owning side to null (unless already changed)
            if ($commercialOffersItem->getCommercialOfferId() === $this) {
                $commercialOffersItem->setCommercialOfferId(null);
            }
        }

        return $this;
    }
}
