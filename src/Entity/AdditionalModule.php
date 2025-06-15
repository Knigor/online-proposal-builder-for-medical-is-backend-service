<?php

namespace App\Entity;

use App\Repository\AdditionalModuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AdditionalModuleRepository::class)]
class AdditionalModule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['module:read', 'module:write','offer:item:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['module:read', 'module:write','offer:item:read'])]
    private ?string $nameModule = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['module:read', 'module:write','offer:item:read'])]
    private ?string $descriptionModule = null;

    #[ORM\Column]
    #[Groups(['module:read', 'module:write','offer:item:read'])]
    private ?float $offerPrice = null;

    #[ORM\Column]
    #[Groups(['module:read', 'module:write','offer:item:read'])]
    private ?float $purchasePrice = null;

    #[ORM\Column]
    #[Groups(['module:read', 'module:write','offer:item:read'])]
    private ?float $maxDiscountPercent = null;

    #[ORM\ManyToOne(inversedBy: 'additionalModules')]
    #[Groups(['module:read', 'module:write'])]
    private ?Product $product = null;

    /**
     * @var Collection<int, LicenseComposition>
     */
    #[ORM\OneToMany(targetEntity: LicenseComposition::class, mappedBy: 'additionalModule')]
    private Collection $licenseCompositions;

    /**
     * @var Collection<int, CommercialOffersItemModule>
     */
    #[ORM\OneToMany(targetEntity: CommercialOffersItemModule::class, mappedBy: 'CommercialOffersItems')]
    private Collection $commercialOffersItemModules;

    /**
     * @var Collection<int, CommercialOffersItemModule>
     */
    #[ORM\OneToMany(targetEntity: CommercialOffersItemModule::class, mappedBy: 'AdditionalModule')]
    private Collection $commercialOffersItemsModules;

    public function __construct()
    {
        $this->licenseCompositions = new ArrayCollection();
        $this->commercialOffersItemModules = new ArrayCollection();
        $this->commercialOffersItemsModules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameModule(): ?string
    {
        return $this->nameModule;
    }

    public function setNameModule(string $nameModule): static
    {
        $this->nameModule = $nameModule;

        return $this;
    }

    public function getDescriptionModule(): ?string
    {
        return $this->descriptionModule;
    }

    public function setDescriptionModule(string $descriptionModule): static
    {
        $this->descriptionModule = $descriptionModule;

        return $this;
    }

    public function getOfferPrice(): ?float
    {
        return $this->offerPrice;
    }

    public function setOfferPrice(float $offerPrice): static
    {
        $this->offerPrice = $offerPrice;

        return $this;
    }

    public function getPurchasePrice(): ?float
    {
        return $this->purchasePrice;
    }

    public function setPurchasePrice(float $purchasePrice): static
    {
        $this->purchasePrice = $purchasePrice;

        return $this;
    }

    public function getMaxDiscountPercent(): ?float
    {
        return $this->maxDiscountPercent;
    }

    public function setMaxDiscountPercent(float $maxDiscountPercent): static
    {
        $this->maxDiscountPercent = $maxDiscountPercent;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return Collection<int, LicenseComposition>
     */
    public function getLicenseCompositions(): Collection
    {
        return $this->licenseCompositions;
    }

    public function addLicenseComposition(LicenseComposition $licenseComposition): static
    {
        if (!$this->licenseCompositions->contains($licenseComposition)) {
            $this->licenseCompositions->add($licenseComposition);
            $licenseComposition->setAdditionalModule($this);
        }

        return $this;
    }

    public function removeLicenseComposition(LicenseComposition $licenseComposition): static
    {
        if ($this->licenseCompositions->removeElement($licenseComposition)) {
            // set the owning side to null (unless already changed)
            if ($licenseComposition->getAdditionalModule() === $this) {
                $licenseComposition->setAdditionalModule(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommercialOffersItemModule>
     */
    public function getCommercialOffersItemModules(): Collection
    {
        return $this->commercialOffersItemModules;
    }

    public function addCommercialOffersItemModule(CommercialOffersItemModule $commercialOffersItemModule): static
    {
        if (!$this->commercialOffersItemModules->contains($commercialOffersItemModule)) {
            $this->commercialOffersItemModules->add($commercialOffersItemModule);
            $commercialOffersItemModule->setCommercialOffersItems($this);
        }

        return $this;
    }

    public function removeCommercialOffersItemModule(CommercialOffersItemModule $commercialOffersItemModule): static
    {
        if ($this->commercialOffersItemModules->removeElement($commercialOffersItemModule)) {
            // set the owning side to null (unless already changed)
            if ($commercialOffersItemModule->getCommercialOffersItems() === $this) {
                $commercialOffersItemModule->setCommercialOffersItems(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommercialOffersItemModule>
     */
    public function getCommercialOffersItemsModules(): Collection
    {
        return $this->commercialOffersItemsModules;
    }

    public function addCommercialOffersItemsModule(CommercialOffersItemModule $commercialOffersItemsModule): static
    {
        if (!$this->commercialOffersItemsModules->contains($commercialOffersItemsModule)) {
            $this->commercialOffersItemsModules->add($commercialOffersItemsModule);
            $commercialOffersItemsModule->setAdditionalModule($this);
        }

        return $this;
    }

    public function removeCommercialOffersItemsModule(CommercialOffersItemModule $commercialOffersItemsModule): static
    {
        if ($this->commercialOffersItemsModules->removeElement($commercialOffersItemsModule)) {
            // set the owning side to null (unless already changed)
            if ($commercialOffersItemsModule->getAdditionalModule() === $this) {
                $commercialOffersItemsModule->setAdditionalModule(null);
            }
        }

        return $this;
    }
}
