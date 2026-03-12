<?php

namespace App\Entity;

use App\Repository\BallDrawnRepository;
use App\Utils\TraitClasses\EntityTimestampableTrait;
use App\Utils\TraitClasses\EntityUniqueIdTrait;
use App\Utils\TraitClasses\EntityUserOperation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: BallDrawnRepository::class)]
class BallDrawn
{
    use EntityUniqueIdTrait;
    use EntityUserOperation;
    use EntityTimestampableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['ball_drawn.index'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ball $ball = null;

    #[ORM\ManyToOne(cascade: ["persist", "remove" ])]
    #[Groups(['ball_drawn.group'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bet $bet = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBall(): ?Ball
    {
        return $this->ball;
    }

    public function setBall(?Ball $ball): static
    {
        $this->ball = $ball;

        return $this;
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


}
