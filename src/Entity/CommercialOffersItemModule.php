<?php

namespace App\Entity;

use App\Repository\CommercialOffersItemModuleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommercialOffersItemModuleRepository::class)]
class CommercialOffersItemModule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'additionalModules')]
    private CommercialOffersItems $item;

    #[ORM\ManyToOne]
    private AdditionalModule $additionalModule;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getItem(): CommercialOffersItems { return $this->item; }
    public function setItem(CommercialOffersItems $item): void { $this->item = $item; }

    public function getAdditionalModule(): AdditionalModule { return $this->additionalModule; }
    public function setAdditionalModule(AdditionalModule $module): void { $this->additionalModule = $module; }

}
