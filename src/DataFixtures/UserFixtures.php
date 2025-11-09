<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Create Super Admin
        $superAdmin = new User();
        $superAdmin->setEmail('superadmin@moduscap.store');
        $superAdmin->setFirstName('Super');
        $superAdmin->setLastName('Admin');
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER']);
        $superAdmin->setIsActive(true);
        $superAdmin->setPassword($this->passwordHasher->hashPassword($superAdmin, 'admin123'));
        $manager->persist($superAdmin);

        // Create Admin
        $admin = new User();
        $admin->setEmail('admin@moduscap.store');
        $admin->setFirstName('John');
        $admin->setLastName('Admin');
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $admin->setIsActive(true);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);


        $manager->flush();
    }
}