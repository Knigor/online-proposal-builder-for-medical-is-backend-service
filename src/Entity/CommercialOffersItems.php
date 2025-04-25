<?php

namespace App\Entity;

use App\Repository\CommercialOffersItemsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommercialOffersItemsRepository::class)]
class CommercialOffersItems
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commercialOffersItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CommercialOffers $commercialOfferId = null;

    #[ORM\ManyToOne(inversedBy: 'commercialOffersItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $productId = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?int $unitPrice = null;

    #[ORM\Column]
    private ?int $discountPercent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommercialOfferId(): ?CommercialOffers
    {
        return $this->commercialOfferId;
    }

    public function setCommercialOfferId(?CommercialOffers $commercialOfferId): static
    {
        $this->commercialOfferId = $commercialOfferId;

        return $this;
    }

    public function getProductId(): ?Product
    {
        return $this->productId;
    }

    public function setProductId(?Product $productId): static
    {
        $this->productId = $productId;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnitPrice(): ?int
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(int $unitPrice): static
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    public function getDiscountPercent(): ?int
    {
        return $this->discountPercent;
    }

    public function setDiscountPercent(int $discountPercent): static
    {
        $this->discountPercent = $discountPercent;

        return $this;
    }
}
