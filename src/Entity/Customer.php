<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['create', 'update', 'view'])]
    private ?string $nameCustomer = null;

    #[ORM\Column(length: 255)]
    #[Groups(['create', 'update', 'view'])]
    private ?string $contactPerson = null;

    #[ORM\Column(length: 100)]
    #[Groups(['create', 'update', 'view'])]
    private ?string $phone = null;

    #[ORM\Column(length: 100)]
    #[Groups(['create', 'update', 'view'])]
    private ?string $email = null;

    /**
     * @var Collection<int, CommercialOffers>
     */
    #[ORM\OneToMany(targetEntity: CommercialOffers::class, mappedBy: 'customer')]
    private Collection $commercialOffers;

    public function __construct()
    {
        $this->commercialOffers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameCustomer(): ?string
    {
        return $this->nameCustomer;
    }

    public function setNameCustomer(string $nameCustomer): static
    {
        $this->nameCustomer = $nameCustomer;

        return $this;
    }

    public function getContactPerson(): ?string
    {
        return $this->contactPerson;
    }

    public function setContactPerson(string $contactPerson): static
    {
        $this->contactPerson = $contactPerson;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, CommercialOffers>
     */
    public function getCommercialOffers(): Collection
    {
        return $this->commercialOffers;
    }

    public function addCommercialOffer(CommercialOffers $commercialOffer): static
    {
        if (!$this->commercialOffers->contains($commercialOffer)) {
            $this->commercialOffers->add($commercialOffer);
            $commercialOffer->setCustomer($this);
        }

        return $this;
    }

    public function removeCommercialOffer(CommercialOffers $commercialOffer): static
    {
        if ($this->commercialOffers->removeElement($commercialOffer)) {
            // set the owning side to null (unless already changed)
            if ($commercialOffer->getCustomer() === $this) {
                $commercialOffer->setCustomer(null);
            }
        }

        return $this;
    }
}
