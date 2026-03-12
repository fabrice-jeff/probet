<?php

namespace App\Entity;

use App\Repository\ActorRepository;
use App\Utils\TraitClasses\EntityTimestampableTrait;
use App\Utils\TraitClasses\EntityUniqueIdTrait;
use App\Utils\TraitClasses\EntityUserOperation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ActorRepository::class)]
class Actor
{
    use EntityUniqueIdTrait;
    use EntityUserOperation;
    use EntityTimestampableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['actor.index'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['actor.index'])]
    private ?string $email = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['actor.index'])]
    private ?Country $country = null;

    #[ORM\OneToOne(inversedBy: 'actor', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Groups(['actor.index'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['actor.index'])]
    private ?string $firstName = null;


    #[ORM\Column(length: 255, unique: true,)]
    #[Groups(['actor.index'])]
    private ?string $identifier = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['actor.index'])]
    private ?bool $firstTransaction = null;

    #[ORM\Column]
    #[Groups(['actor.index'])]
    private ?float $mainWallet = null;

    #[ORM\Column]
    #[Groups(['actor.index'])]
    private ?float $reattachWallet = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['actor.index'])]
    private ?string $phoneNumber = null;

    public function getId(): ?int
    {
        return $this->id;
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


    public function getCountry(): ?Country
    {
         return $this->country;
    }

    public function setCountry(?Country $country): static
    {
         $this->country = $country;

         return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function isFirstTransaction(): ?bool
    {
        return $this->firstTransaction;
    }

    public function setFirstTransaction(?bool $firstTransaction): static
    {
        $this->firstTransaction = $firstTransaction;

        return $this;
    }

    public function getMainWallet(): ?float
    {
        return $this->mainWallet;
    }

    public function setMainWallet(float $mainWallet): static
    {
        $this->mainWallet = $mainWallet;

        return $this;
    }

    public function getReattachWallet(): ?float
    {
        return $this->reattachWallet;
    }

    public function setReattachWallet(float $reattachWallet): static
    {
        $this->reattachWallet = $reattachWallet;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }
}
