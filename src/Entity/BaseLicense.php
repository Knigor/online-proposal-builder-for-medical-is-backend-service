<?php

namespace App\Entity;

use App\Repository\BaseLicenseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: BaseLicenseRepository::class)]
class BaseLicense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['license:read', 'license:write','offer:item:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['license:read', 'license:write','offer:item:read'])]
    private ?string $nameLicense = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['license:read', 'license:write','offer:item:read'])]
    private ?string $descriptionLicense = null;

    #[ORM\Column]
    #[Groups(['license:read', 'license:write','offer:item:read'])]
    private ?float $offerPriceLicense = null;

    #[ORM\Column]
    #[Groups(['license:read', 'license:write','offer:item:read'])]
    private ?float $purchasePriceLicense = null;

    #[ORM\Column]
    #[Groups(['license:read', 'license:write','offer:item:read'])]
    private ?float $maxDiscount = null;

    #[ORM\Column(length: 255)]
    #[Groups(['license:read', 'license:write','offer:item:read'])]
    private ?string $typeLicense = null;

    #[ORM\ManyToOne(inversedBy: 'baseLicenses')]
    #[Groups(['license:read', 'license:write','offer:item:read'])]
    private ?Product $product = null;

    /**
     * @var Collection<int, LicenseComposition>
     */
    #[ORM\OneToMany(targetEntity: LicenseComposition::class, mappedBy: 'baseLicense')]
    private Collection $licenseCompositions;

    public function __construct()
    {
        $this->licenseCompositions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameLicense(): ?string
    {
        return $this->nameLicense;
    }

    public function setNameLicense(string $nameLicense): static
    {
        $this->nameLicense = $nameLicense;

        return $this;
    }

    public function getDescriptionLicense(): ?string
    {
        return $this->descriptionLicense;
    }

    public function setDescriptionLicense(string $descriptionLicense): static
    {
        $this->descriptionLicense = $descriptionLicense;

        return $this;
    }

    public function getOfferPriceLicense(): ?float
    {
        return $this->offerPriceLicense;
    }

    public function setOfferPriceLicense(float $offerPriceLicense): static
    {
        $this->offerPriceLicense = $offerPriceLicense;

        return $this;
    }

    public function getPurchasePriceLicense(): ?float
    {
        return $this->purchasePriceLicense;
    }

    public function setPurchasePriceLicense(float $purchasePriceLicense): static
    {
        $this->purchasePriceLicense = $purchasePriceLicense;

        return $this;
    }

    public function getMaxDiscount(): ?float
    {
        return $this->maxDiscount;
    }

    public function setMaxDiscount(float $maxDiscount): static
    {
        $this->maxDiscount = $maxDiscount;

        return $this;
    }

    public function getTypeLicense(): ?string
    {
        return $this->typeLicense;
    }

    public function setTypeLicense(string $typeLicense): static
    {
        $this->typeLicense = $typeLicense;

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
            $licenseComposition->setBaseLicense($this);
        }

        return $this;
    }

    public function removeLicenseComposition(LicenseComposition $licenseComposition): static
    {
        if ($this->licenseCompositions->removeElement($licenseComposition)) {
            // set the owning side to null (unless already changed)
            if ($licenseComposition->getBaseLicense() === $this) {
                $licenseComposition->setBaseLicense(null);
            }
        }

        return $this;
    }
}
