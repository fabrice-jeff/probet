<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $roles = ['ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_USER_MERCHANT'];
        foreach ($roles as $value) {
            $role= new Role();
            $role->setName($value);
            $manager->persist($role);
        }
        $manager->flush();
    }
}
