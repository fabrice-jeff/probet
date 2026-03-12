<?php

namespace App\DataFixtures;

use App\Entity\TypeBet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypeBetFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $typeBetList = [
            [
                'reference' => 'TYPE_BET_NAP1_POTO',
                'name' => 'Nap1 POTO',
                'percentage' => '50'
            ],
            [
                'reference' => 'TYPE_BET_NAP1_BLOQUE',
                'name' => 'Nap1 BLOQUE',
                'percentage' => '15'
            ],
            [
                'reference' => 'TYPE_BET_NAP2',
                'name' => 'NAP2',
                'percentage' => '300'
            ],
            [
                'reference' => 'TYPE_BET_NAP3',
                'name' => 'NAP3',
                'percentage' => '3000'
            ],
            [
                'reference' => 'TYPE_BET_NAP4',
                'name' => 'NAP4',
                'percentage' => '10000'
            ],
            [
                'reference' => 'TYPE_BET_NAP5',
                'name' => 'NAP5',
                'percentage' => '50000'
            ],
            [
                'reference' => 'TYPE_BET_TCHIGAN',
                'name' => 'Tchigan',
                'percentage' => '0'
            ],
            [
                'reference' => 'TYPE_BET_PERM2',
                'name' => 'Perm2',
                'percentage' => '300'
            ],
            [
                'reference' => 'TYPE_BET_PERM3',
                'name' => 'Perm3',
                'percentage' => '300'
            ],

        ];
        foreach ($typeBetList as $value) {
            $typeBet = new TypeBet();
            $typeBet->setReference($value['reference'])
                ->setName($value['name'])
                ->setPercentage($value['percentage']);
            $manager->persist($typeBet);
        }
        $manager->flush();
    }
}
