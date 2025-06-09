<?php

namespace App\Entity;

use App\Repository\CommercialOffersItemsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CommercialOffersItemsRepository::class)]
class CommercialOffersItems
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['offer:item:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commercialOffersItems')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['offer:item:read'])]
    private ?CommercialOffers $commercialOfferId = null;

    #[ORM\ManyToOne(inversedBy: 'commercialOffersItem')]
    #[Groups(['offer:item:read'])]
    private ?Product $product = null;


    #[ORM\ManyToOne]
    #[Groups(['offer:item:read'])]
    private ?BaseLicense $baseLicense = null;

    #[ORM\ManyToOne]
    #[Groups(['offer:item:read'])]
    private ?AdditionalModule $additionalModule = null;

    #[ORM\Column]
    private int $quantity = 1;

    #[ORM\Column]
    #[Groups(['offer:item:read'])]
    private int $price = 0;

    #[ORM\Column(nullable: true)]
    #[Groups(['offer:item:read'])]
    private ?float $discount = null;




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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getBaseLicense(): ?BaseLicense
    {
        return $this->baseLicense;
    }

    public function setBaseLicense(?BaseLicense $baseLicense): static
    {
        $this->baseLicense = $baseLicense;
        return $this;
    }

    public function getAdditionalModule(): ?AdditionalModule
    {
        return $this->additionalModule;
    }

    public function setAdditionalModule(?AdditionalModule $additionalModule): static
    {
        $this->additionalModule = $additionalModule;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    public function setDiscount(?float $discount): static
    {
        $this->discount = $discount;
        return $this;
    }


}
