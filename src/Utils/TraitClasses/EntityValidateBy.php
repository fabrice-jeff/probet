<?php
/**
 * Created by PhpStorm.
 * User: LANGANFIN Rogelio
 * Date: 02/01/2020
 * Time: 09:31
 */

namespace App\Utils\TraitClasses;


use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

trait EntityValidateBy
{

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private User $validateBy;

    /**
     * @return User
     */
    public function getValidateBy(): User
    {
        return $this->validateBy;
    }

    /**
     * @param User $validateBy
     * @return self
     */
    public function setValidateBy(User $validateBy)
    {
        $this->validateBy = $validateBy;

        return $this;
    }

}