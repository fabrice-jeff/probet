<?php

namespace App\Entity;

use App\Repository\BetRepository;
use App\Utils\TraitClasses\EntityTimestampableTrait;
use App\Utils\TraitClasses\EntityUniqueIdTrait;
use App\Utils\TraitClasses\EntityUserOperation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: BetRepository::class)]
class Bet
{
    use EntityTimestampableTrait;
    use EntityUniqueIdTrait;
    use EntityUserOperation;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['bet.index'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['bet.index'])]
    private ?Draw $draw = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['bet.index'])]
    private ?TypeBet $typeBet = null;

    #[ORM\ManyToOne]
    #[Groups(['bet.index'])]
    private ?Status $status = null;

    #[ORM\Column]
    #[Groups(['bet.index'])]

    private ?float $amount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['bet.index'])]
    private ?float $amountWon = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['bet.index'])]
    
    private ?bool $doubleChance = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['bet.index'])]
    private ?float $gains = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDraw(): ?Draw
    {
        return $this->draw;
    }

    public function setDraw(?Draw $draw): static
    {
        $this->draw = $draw;

        return $this;
    }

    public function getTypeBet(): ?TypeBet
    {
        return $this->typeBet;
    }
    public function setTypeBet(?TypeBet $typeBet): static
    {
        $this->typeBet = $typeBet;

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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmountWon(): ?float
    {
        return $this->amountWon;
    }

    public function setAmountWon(?float $amountWon): static
    {
        $this->amountWon = $amountWon;

        return $this;
    }

    public function isDoubleChance(): ?bool
    {
        return $this->doubleChance;
    }

    public function setDoubleChance(?bool $doubleChance): static
    {
        $this->doubleChance = $doubleChance;

        return $this;
    }

    public function getGains(): ?float
    {
        return $this->gains;
    }

    public function setGains(?float $gains): static
    {
        $this->gains = $gains;

        return $this;
    }

}
