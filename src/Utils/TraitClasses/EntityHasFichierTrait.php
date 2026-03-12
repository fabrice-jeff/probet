<?php
/**
 * Created by PhpStorm.
 * User: LANGANFIN Rogelio
 * Date: 02/01/2020
 * Time: 09:31
 */

namespace App\Utils\TraitClasses;


use Doctrine\ORM\Mapping as ORM;

trait EntityHasFichierTrait
{

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $codeFichier;

    public function getCodeFichier(): ?string
    {
        return $this->codeFichier;
    }

    public function setCodeFichier(string $codeFichier): self
    {
        $this->codeFichier = $codeFichier;

        return $this;
    }


}