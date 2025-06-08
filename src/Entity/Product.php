<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['license:read', 'license:write', 'offer:item:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['license:read', 'license:write', 'offer:item:read'])]
    private ?string $nameProduct = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['offer:item:read'])]
    private ?string $discriptionProduct = null;

    /**
     * @var Collection<int, DiscountLevel>
     */
    #[ORM\OneToMany(targetEntity: DiscountLevel::class, mappedBy: 'product')]
    private Collection $discountLevels;

    /**
     * @var Collection<int, AdditionalModule>
     */
    #[ORM\OneToMany(targetEntity: AdditionalModule::class, mappedBy: 'product')]
    private Collection $additionalModules;

    /**
     * @var Collection<int, BaseLicense>
     */
    #[ORM\OneToMany(targetEntity: BaseLicense::class, mappedBy: 'product')]
    private Collection $baseLicenses;

    /**
     * @var Collection<int, CommercialOffersItems>
     */
    #[ORM\OneToMany(targetEntity: CommercialOffersItems::class, mappedBy: 'product')]
    private Collection $commercialOffersItem;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?User $userProduct = null;

    public function __construct()
    {
        $this->discountLevels = new ArrayCollection();
        $this->additionalModules = new ArrayCollection();
        $this->baseLicenses = new ArrayCollection();
        $this->commercialOffersItem = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, DiscountLevel>
     */
    public function getDiscountLevels(): Collection
    {
        return $this->discountLevels;
    }

    public function addDiscountLevel(DiscountLevel $discountLevel): static
    {
        if (!$this->discountLevels->contains($discountLevel)) {
            $this->discountLevels->add($discountLevel);
            $discountLevel->setProduct($this);
        }

        return $this;
    }

    public function removeDiscountLevel(DiscountLevel $discountLevel): static
    {
        if ($this->discountLevels->removeElement($discountLevel)) {
            // set the owning side to null (unless already changed)
            if ($discountLevel->getProduct() === $this) {
                $discountLevel->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AdditionalModule>
     */
    public function getAdditionalModules(): Collection
    {
        return $this->additionalModules;
    }

    public function addAdditionalModule(AdditionalModule $additionalModule): static
    {
        if (!$this->additionalModules->contains($additionalModule)) {
            $this->additionalModules->add($additionalModule);
            $additionalModule->setProduct($this);
        }

        return $this;
    }

    public function removeAdditionalModule(AdditionalModule $additionalModule): static
    {
        if ($this->additionalModules->removeElement($additionalModule)) {
            // set the owning side to null (unless already changed)
            if ($additionalModule->getProduct() === $this) {
                $additionalModule->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BaseLicense>
     */
    public function getBaseLicenses(): Collection
    {
        return $this->baseLicenses;
    }

    public function addBaseLicense(BaseLicense $baseLicense): static
    {
        if (!$this->baseLicenses->contains($baseLicense)) {
            $this->baseLicenses->add($baseLicense);
            $baseLicense->setProduct($this);
        }

        return $this;
    }

    public function removeBaseLicense(BaseLicense $baseLicense): static
    {
        if ($this->baseLicenses->removeElement($baseLicense)) {
            // set the owning side to null (unless already changed)
            if ($baseLicense->getProduct() === $this) {
                $baseLicense->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommercialOffersItems>
     */
    public function getCommercialOffersItem(): Collection
    {
        return $this->commercialOffersItem;
    }

    public function addCommercialOffersItem(CommercialOffersItems $commercialOffersItem): static
    {
        if (!$this->commercialOffersItem->contains($commercialOffersItem)) {
            $this->commercialOffersItem->add($commercialOffersItem);
            $commercialOffersItem->setProduct($this);
        }

        return $this;
    }

    public function removeCommercialOffersItem(CommercialOffersItems $commercialOffersItem): static
    {
        if ($this->commercialOffersItem->removeElement($commercialOffersItem)) {
            // set the owning side to null (unless already changed)
            if ($commercialOffersItem->getProduct() === $this) {
                $commercialOffersItem->setProduct(null);
            }
        }

        return $this;
    }

    public function getUserProduct(): ?User
    {
        return $this->userProduct;
    }

    public function setUserProduct(?User $userProduct): static
    {
        $this->userProduct = $userProduct;

        return $this;
    }
}
