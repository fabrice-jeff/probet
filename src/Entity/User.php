<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Utils\TraitClasses\EntityTimestampableTrait;
use App\Utils\TraitClasses\EntityUniqueIdTrait;
use App\Utils\TraitClasses\EntityUserOperation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use EntityUniqueIdTrait;
    use EntityUserOperation;
    use EntityTimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user.index'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user.index'])]

    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user.index'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    #[Groups(['user.index'])]
    private ?bool $active = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Groups(['user.index'])]
    private ?Actor $actor = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $reinitializeCode = null;

    #[ORM\Column(nullable: true)]

    private ?\DateTimeImmutable $reinitializeCodeSentAt = null;

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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getActor(): ?Actor
    {
        return $this->actor;
    }

    public function setActor(Actor $actor): static
    {
        // set the owning side of the relation if necessary
        if ($actor->getUser() !== $this) {
            $actor->setUser($this);
        }

        $this->actor = $actor;

        return $this;
    }

    public function getReinitializeCode(): ?string
    {
        return $this->reinitializeCode;
    }

    public function setReinitializeCode(?string $reinitializeCode): static
    {
        $this->reinitializeCode = $reinitializeCode;

        return $this;
    }

    public function getReinitializeCodeSentAt(): ?\DateTimeImmutable
    {
        return $this->reinitializeCodeSentAt;
    }

    public function setReinitializeCodeSentAt(?\DateTimeImmutable $reinitializeCodeSentAt): static
    {
        $this->reinitializeCodeSentAt = $reinitializeCodeSentAt;

        return $this;
    }

}
