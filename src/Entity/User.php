<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column(length: 255)]
    private ?string $userName = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $hashPassword = null;

    #[ORM\Column(length: 100)]
    private ?string $role = null;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'userId')]
    private Collection $nameProduct;

    /**
     * @var Collection<int, CommercialOffers>
     */
    #[ORM\ManyToMany(targetEntity: CommercialOffers::class, mappedBy: 'userId')]
    private Collection $commercialOffers;

    public function __construct()
    {
        $this->nameProduct = new ArrayCollection();
        $this->commercialOffers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): static
    {
        $this->userName = $userName;

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

    public function getHashPassword(): ?string
    {
        return $this->hashPassword;
    }

    public function setHashPassword(string $hashPassword): static
    {
        $this->hashPassword = $hashPassword;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getNameProduct(): Collection
    {
        return $this->nameProduct;
    }

    public function addNameProduct(Product $nameProduct): static
    {
        if (!$this->nameProduct->contains($nameProduct)) {
            $this->nameProduct->add($nameProduct);
            $nameProduct->setUserId($this);
        }

        return $this;
    }

    public function removeNameProduct(Product $nameProduct): static
    {
        if ($this->nameProduct->removeElement($nameProduct)) {
            // set the owning side to null (unless already changed)
            if ($nameProduct->getUserId() === $this) {
                $nameProduct->setUserId(null);
            }
        }

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
            $commercialOffer->addUserId($this);
        }

        return $this;
    }

    public function removeCommercialOffer(CommercialOffers $commercialOffer): static
    {
        if ($this->commercialOffers->removeElement($commercialOffer)) {
            $commercialOffer->removeUserId($this);
        }

        return $this;
    }
}
