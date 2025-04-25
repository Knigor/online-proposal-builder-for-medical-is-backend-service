<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'nameProduct')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userId = null;

    #[ORM\Column(length: 255)]
    private ?string $nameProduct = null;

    #[ORM\Column(length: 1000)]
    private ?string $discriptionProduct = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isActive = null;

    #[ORM\Column(length: 100)]
    private ?string $typeProduct = null;

    /**
     * @var Collection<int, PriceList>
     */
    #[ORM\OneToMany(targetEntity: PriceList::class, mappedBy: 'product')]
    private Collection $priceLists;

    /**
     * @var Collection<int, CommercialOffersItems>
     */
    #[ORM\OneToMany(targetEntity: CommercialOffersItems::class, mappedBy: 'productId')]
    private Collection $commercialOffersItems;

    public function __construct()
    {
        $this->priceLists = new ArrayCollection();
        $this->commercialOffersItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getNameProduct(): ?string
    {
        return $this->nameProduct;
    }

    public function setNameProduct(string $nameProduct): static
    {
        $this->nameProduct = $nameProduct;

        return $this;
    }

    public function getDiscriptionProduct(): ?string
    {
        return $this->discriptionProduct;
    }

    public function setDiscriptionProduct(string $discriptionProduct): static
    {
        $this->discriptionProduct = $discriptionProduct;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getTypeProduct(): ?string
    {
        return $this->typeProduct;
    }

    public function setTypeProduct(string $typeProduct): static
    {
        $this->typeProduct = $typeProduct;

        return $this;
    }

    /**
     * @return Collection<int, PriceList>
     */
    public function getPriceLists(): Collection
    {
        return $this->priceLists;
    }

    public function addPriceList(PriceList $priceList): static
    {
        if (!$this->priceLists->contains($priceList)) {
            $this->priceLists->add($priceList);
            $priceList->setProduct($this);
        }

        return $this;
    }

    public function removePriceList(PriceList $priceList): static
    {
        if ($this->priceLists->removeElement($priceList)) {
            // set the owning side to null (unless already changed)
            if ($priceList->getProduct() === $this) {
                $priceList->setProduct(null);
            }
        }

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
            $commercialOffersItem->setProductId($this);
        }

        return $this;
    }

    public function removeCommercialOffersItem(CommercialOffersItems $commercialOffersItem): static
    {
        if ($this->commercialOffersItems->removeElement($commercialOffersItem)) {
            // set the owning side to null (unless already changed)
            if ($commercialOffersItem->getProductId() === $this) {
                $commercialOffersItem->setProductId(null);
            }
        }

        return $this;
    }
}
