<?php

namespace App\Entity;

use App\Repository\ManagerLkRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ManagerLkRepository::class)]
class ManagerLk
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'managerLks')]
    private ?User $userId = null;

    #[ORM\ManyToOne(inversedBy: 'managerLks')]
    private ?CommercialOffers $commercialOffersId = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    private ?string $emailClient = null;

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

    public function getCommercialOffersId(): ?CommercialOffers
    {
        return $this->commercialOffersId;
    }

    public function setCommercialOffersId(?CommercialOffers $commercialOffersId): static
    {
        $this->commercialOffersId = $commercialOffersId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getEmailClient(): ?string
    {
        return $this->emailClient;
    }

    public function setEmailClient(string $emailClient): static
    {
        $this->emailClient = $emailClient;

        return $this;
    }
}
