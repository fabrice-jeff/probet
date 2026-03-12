<?php

namespace App\DataFixtures;

use App\Entity\Ball;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BallFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 90; $i++) {
            $ball = new Ball();
            $manager->persist($ball);
        }
        $manager->flush();
    }
}
