<?php
/**
 * Credits
 * Created by PhpStorm.
 * User: LANGANFIN Rogelio
 * Date: 02/01/2020
 * Time: 09:31
 */

namespace App\Utils\TraitClasses;


use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

trait  EntityUserOperation
{
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[\Symfony\Component\Serializer\Attribute\Groups('insert_by')]
    private $insertBy;

    /**
     * @return User|null
     */
    public function getInsertBy(): ?User
    {
        return $this->insertBy;
    }

    /**
     * @param User $user
     * @return self
     */
    public function setInsertBy(?User $user): self
    {
        $this->insertBy = $user;

        return $this;
    }
}