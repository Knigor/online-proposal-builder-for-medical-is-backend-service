<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Serializer\Attribute\Groups;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['product:read'])]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $hashPassword = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'userId')]
    private Collection $nameProduct;

    /**
     * @var Collection<int, CommercialOffers>
     */
    #[ORM\ManyToMany(targetEntity: CommercialOffers::class, mappedBy: 'userId')]
    private Collection $commercialOffers;

    public function __construct()
    {
        $this->nameProduct = new ArrayCollection();
        $this->commercialOffers = new ArrayCollection();
        $this->roles = ['ROLE_USER'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->username;
    }

    public function setUserName(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getHashPassword(): ?string
    {
        return $this->hashPassword;
    }

    public function setHashPassword(string $hashPassword): static
    {
        $this->hashPassword = $hashPassword;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // гарантируем, что всегда будет хотя бы ROLE_USER
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->hashPassword;
    }

    // Изменение метода getUserIdentifier, теперь возвращает userName
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function eraseCredentials(): void
    {
        // Очистка временных данных, если они есть
    }

    /**
     * @return Collection<int, Product>
     */
    public function getNameProduct(): Collection
    {
        return $this->nameProduct;
    }

    public function addNameProduct(Product $nameProduct): static
    {
        if (!$this->nameProduct->contains($nameProduct)) {
            $this->nameProduct->add($nameProduct);
            $nameProduct->setUserId($this);
        }

        return $this;
    }

    public function removeNameProduct(Product $nameProduct): static
    {
        if ($this->nameProduct->removeElement($nameProduct)) {
            if ($nameProduct->getUserId() === $this) {
                $nameProduct->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommercialOffers>
     */
    public function getCommercialOffers(): Collection
    {
        return $this->commercialOffers;
    }

    public function addCommercialOffer(CommercialOffers $commercialOffer): static
    {
        if (!$this->commercialOffers->contains($commercialOffer)) {
            $this->commercialOffers->add($commercialOffer);
            $commercialOffer->addUserId($this);
        }

        return $this;
    }

    public function removeCommercialOffer(CommercialOffers $commercialOffer): static
    {
        if ($this->commercialOffers->removeElement($commercialOffer)) {
            $commercialOffer->removeUserId($this);
        }

        return $this;
    }
}
