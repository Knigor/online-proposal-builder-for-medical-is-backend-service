<?php

namespace App\Entity;

use App\Repository\DiscountLevelRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscountLevelRepository::class)]
class DiscountLevel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    private ?int $minLicenses = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxLicenses = null;

    #[ORM\Column(nullable: true)]
    private ?int $minAmount = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxAmount = null;

    #[ORM\Column(nullable: true)]
    private ?float $discountPercent = null;

    #[ORM\ManyToOne(inversedBy: 'discountLevels')]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getMinLicenses(): ?int
    {
        return $this->minLicenses;
    }

    public function setMinLicenses(?int $minLicenses): static
    {
        $this->minLicenses = $minLicenses;

        return $this;
    }

    public function getMaxLicenses(): ?int
    {
        return $this->maxLicenses;
    }

    public function setMaxLicenses(?int $maxLicenses): static
    {
        $this->maxLicenses = $maxLicenses;

        return $this;
    }

    public function getMinAmount(): ?int
    {
        return $this->minAmount;
    }

    public function setMinAmount(?int $minAmount): static
    {
        $this->minAmount = $minAmount;

        return $this;
    }

    public function getMaxAmount(): ?int
    {
        return $this->maxAmount;
    }

    public function setMaxAmount(?int $maxAmount): static
    {
        $this->maxAmount = $maxAmount;

        return $this;
    }

    public function getDiscountPercent(): ?float
    {
        return $this->discountPercent;
    }

    public function setDiscountPercent(?float $discountPercent): static
    {
        $this->discountPercent = $discountPercent;

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
}
