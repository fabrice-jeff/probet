<?php

namespace App\DataFixtures;

use App\Entity\TypeType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypeTypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $typeList = [
            [
                'reference' => 'TYPE_TRANSACTION_DEPOSIT',
                'name' => 'Dépot'
            ],
            [
                'reference' => 'TYPE_TRANSACTION_WITHDRAW',
                'name' => 'Retrait'
            ],
            [
                'reference' => 'TYPE_PERIOD_DAY',
                'name' => 'Aujourd\'hui'
            ],
            [
                'reference' => 'TYPE_PERIOD_WEEK',
                'name' => 'Cette semaine'
            ],
            [
                'reference' => 'TYPE_PERIOD_MONTH',
                'name' => 'Ce mois'
            ],
            [
                'reference' => 'TYPE_PERIOD_YEAR',
                'name' => 'Cette année'
            ],
            [
                'reference' => 'TYPE_CATEGORIE_DYNAMIQUE',
                'name' => 'Categorie dynamique',
            ],
            [
                'reference' => 'TYPE_CATEGORIE_MOYENNE',
                'name' => 'Categorie moyenne',
            ],
            [
                'reference' => 'TYPE_CATEGORIE_FAIBLE',
                'name' => 'Categorie faible',
            ],
            [
                'reference' => 'TYPE_WALLET_MAIN',
                'name' => 'Compte principale',
            ],
            [
                'reference' => 'TYPE_WALLET_REATTACH',
                'name' => 'Compte rattaché',
            ],
        ];
        foreach ($typeList as $value) {
            $typeType = new TypeType();
            $typeType->setReference($value['reference'])
                ->setName($value['name']);
            $manager->persist($typeType);
        }
        $manager->flush();
    }
}
