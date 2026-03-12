<?php

namespace App\Services;

use App\Entity\PosteOccuper;
use App\Entity\User;
use App\Repository\CourrierRepository;
use App\Repository\PosteOccuperRepository;

class CourrierServices
{
    private PosteOccuperRepository $posteOccuperRepository;
    private CourrierRepository $courrierRepository;

    public function __construct(PosteOccuperRepository $posteOccuperRepository, CourrierRepository $courrierRepository)
    {
        $this->posteOccuperRepository = $posteOccuperRepository;
        $this->courrierRepository = $courrierRepository;
    }

    /**
     * @param User $user
     * @return \App\Entity\UniteAdministrative|null
     */
    public function getUaUser(User $user){
        $personnel = $user->getPersonnel();

        /** @var PosteOccuper $affectation */
        $affectation = $this->posteOccuperRepository->findOneBy(['personnel' => $personnel, 'dateFin' => null]);

        if ($affectation){

            return $affectation->getFonction()->getUa();
        }else{
            return  null;
        }
    }

    /**
     * @param User $user
     * @return \App\Entity\PosteOccuper|null
     */
    public function getAffectationUser(User $user){
        $personnel = $user->getPersonnel();

        /** @var PosteOccuper $affectation */
        $affectation = $this->posteOccuperRepository->findOneBy(['personnel' => $personnel, 'dateFin' => null]);


        return $affectation;
    }

    public function userHaveAffects(User $user)
    {
        return $this->courrierRepository->findUserHaveAffects($user);
    }
}