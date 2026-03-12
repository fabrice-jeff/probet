<?php

namespace App\Entity;

use App\Repository\TypeTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TypeTypeRepository::class)]
class TypeType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['type_type.index'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['type_type.index'])]
    private ?string $reference = null;

    #[ORM\Column(length: 255)]
    #[Groups(['type_type.index'])]
    private ?string $name = null;



    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
