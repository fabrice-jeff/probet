<?php

namespace App\Entity;

use App\Repository\CoupleDrawnRepository;
use App\Utils\TraitClasses\EntityTimestampableTrait;
use App\Utils\TraitClasses\EntityUniqueIdTrait;
use App\Utils\TraitClasses\EntityUserOperation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CoupleDrawnRepository::class)]
class CoupleDrawn
{
    use EntityUniqueIdTrait;
    use EntityUserOperation;
    use EntityTimestampableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['couple_drawn.index'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['couple_drawn.index'])]
    private ?Bet $bet = null;

    #[ORM\Column(length: 255)]
    #[Groups(['couple_drawn.index'])]
    private ?string $balls = null;

    #[ORM\ManyToOne]
    #[Groups(['couple_drawn.index'])]
    private ?Status $status = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(nullable: true)]
    private ?float $amountWon = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getBet(): ?Bet
    {
        return $this->bet;
    }
    public function setBet(?Bet $bet): static
    {
        $this->bet = $bet;
        return $this;
    }

    public function getBalls(): ?string
    {
        return $this->balls;
    }

    public function setBalls(string $balls): static
    {
        $this->balls = $balls;

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
}
