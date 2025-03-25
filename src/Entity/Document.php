<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?CommercialOffers $commercialOffer = null;

    #[ORM\Column(length: 255)]
    private ?string $pathToFile = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommercialOffer(): ?CommercialOffers
    {
        return $this->commercialOffer;
    }

    public function setCommercialOffer(?CommercialOffers $commercialOffer): static
    {
        $this->commercialOffer = $commercialOffer;

        return $this;
    }

    public function getPathToFile(): ?string
    {
        return $this->pathToFile;
    }

    public function setPathToFile(string $pathToFile): static
    {
        $this->pathToFile = $pathToFile;

        return $this;
    }
}
