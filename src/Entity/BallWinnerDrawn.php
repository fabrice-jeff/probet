<?php

namespace App\Entity;

use App\Repository\BallWinnerDrawnRepository;
use App\Utils\TraitClasses\EntityTimestampableTrait;
use App\Utils\TraitClasses\EntityUniqueIdTrait;
use App\Utils\TraitClasses\EntityUserOperation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: BallWinnerDrawnRepository::class)]
class BallWinnerDrawn
{
    use EntityUniqueIdTrait;
    use EntityUserOperation;
    use EntityTimestampableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['ball_winner_drawn.index'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['ball_winner_drawn.index'])]
    private ?Ball $ball = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['ball_winner_drawn.index'])]
    private ?BallWinner $ballWinner = null;

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
    public function getBallWinner(): ?BallWinner
    {
        return $this->ballWinner;
    }

    public function setBallWinner(?BallWinner $ballWinner): static
    {
        $this->ballWinner = $ballWinner;

        return $this;
    }
}
