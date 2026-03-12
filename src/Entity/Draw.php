<?php

namespace App\Entity;

use App\Repository\DrawRepository;
use App\Utils\TraitClasses\EntityTimestampableTrait;
use App\Utils\TraitClasses\EntityUniqueIdTrait;
use App\Utils\TraitClasses\EntityUserOperation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DrawRepository::class)]
class Draw
{
    use EntityUniqueIdTrait;
    use EntityUserOperation;
    use EntityTimestampableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['draw.index'])]
    private ?Game $game = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['draw.index'])]
    private ?Actor $actor = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['draw.index'])]
    private ?Status $status = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['draw.index'])]
    private ?string $number = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['draw.index'])]
    private ?float $amount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $wallet = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['draw.index'])]
    private ?float $potentialsGains = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;

        return $this;
    }

    public function getActor(): ?Actor
    {
        return $this->actor;
    }

    public function setActor(?Actor $actor): static
    {
        $this->actor = $actor;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getWallet(): ?string
    {
        return $this->wallet;
    }

    public function setWallet(string $wallet): static
    {
        $this->wallet = $wallet;
        return $this;
    }

    public function getPotentialsGains(): ?float
    {
        return $this->potentialsGains;
    }

    public function setPotentialsGains(?float $potentialsGains): static
    {
        $this->potentialsGains = $potentialsGains;

        return $this;
    }
}
