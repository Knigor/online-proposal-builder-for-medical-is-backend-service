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

    #[ORM\ManyToOne(inversedBy: 'commercialOffersItem')]
    private ?Product $product = null;



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


}
