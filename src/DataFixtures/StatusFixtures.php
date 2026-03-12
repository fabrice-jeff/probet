<?php

namespace App\DataFixtures;

use App\Entity\Status;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class StatusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $statusList = [
            'STATUS_GAME_OF_ELEVEN',
            'STATUS_GAME_OF_FIFTEEN',
            'STATUS_GAME_OF_EIGHTEEN',
            'STATUS_GAME_VALIDATED',
            'STATUS_GAME_INCOMPLETE',
            'STATUS_BET_WIN',
            'STATUS_BET_LOST',
        ];
        foreach ($statusList as $value) {
            $status = new Status();
            $status->setName($value);
            $manager->persist($status);
        }
        $manager->flush();
    }
}
