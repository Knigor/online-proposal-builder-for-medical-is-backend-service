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
    #[Groups(['module:read', 'module:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['module:read', 'module:write'])]
    private ?string $nameModule = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['module:read', 'module:write'])]
    private ?string $descriptionModule = null;

    #[ORM\Column]
    #[Groups(['module:read', 'module:write'])]
    private ?float $offerPrice = null;

    #[ORM\Column]
    #[Groups(['module:read', 'module:write'])]
    private ?float $purchasePrice = null;

    #[ORM\Column]
    #[Groups(['module:read', 'module:write'])]
    private ?float $maxDiscountPercent = null;

    #[ORM\ManyToOne(inversedBy: 'additionalModules')]
    #[Groups(['module:read', 'module:write'])]
    private ?Product $product = null;

    /**
     * @var Collection<int, LicenseComposition>
     */
    #[ORM\OneToMany(targetEntity: LicenseComposition::class, mappedBy: 'additionalModule')]
    private Collection $licenseCompositions;

    public function __construct()
    {
        $this->licenseCompositions = new ArrayCollection();
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
}
