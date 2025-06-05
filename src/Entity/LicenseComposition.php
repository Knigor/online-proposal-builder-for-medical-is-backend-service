<?php

namespace App\Entity;

use App\Repository\LicenseCompositionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: LicenseCompositionRepository::class)]
class LicenseComposition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['composition:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['composition:read', 'composition:write'])]
    private ?bool $required = null;

    #[ORM\Column]
    #[Groups(['composition:read', 'composition:write'])]
    private ?bool $compatible = null;

    #[ORM\ManyToOne(inversedBy: 'licenseCompositions', cascade: ['persist'])]
    #[Groups(['license:read', 'license:write'])]
    private ?BaseLicense $baseLicense = null;

    #[ORM\ManyToOne(inversedBy: 'licenseCompositions', cascade: ['persist'])]
    #[Groups(['composition:read', 'composition:write'])]
    private ?AdditionalModule $additionalModule = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isRequired(): ?bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): static
    {
        $this->required = $required;

        return $this;
    }

    public function isCompatible(): ?bool
    {
        return $this->compatible;
    }

    public function setCompatible(bool $compatible): static
    {
        $this->compatible = $compatible;

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
}
