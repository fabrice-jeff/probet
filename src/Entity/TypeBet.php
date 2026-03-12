<?php

namespace App\Entity;

use App\Repository\TypeBetRepository;
use App\Utils\TraitClasses\EntityTimestampableTrait;
use App\Utils\TraitClasses\EntityUniqueIdTrait;
use App\Utils\TraitClasses\EntityUserOperation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TypeBetRepository::class)]
class TypeBet
{
    use EntityUniqueIdTrait;
    use EntityUserOperation;
    use EntityTimestampableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['type_bet.index'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['type_bet.index'])]
    private ?string $name = null;


    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['type_bet.index'])]
    private ?string $reference = null;

    #[ORM\Column(length: 255)]
    #[Groups(['type_bet.index'])]
    private ?string $percentage = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getName(): ?string
    {
        return $this->name;
    }
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getPercentage(): ?string
    {
        return $this->percentage;
    }

    public function setPercentage(string $percentage): static
    {
        $this->percentage = $percentage;

        return $this;
    }
}
