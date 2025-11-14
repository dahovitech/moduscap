<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Setting;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SettingFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Paramètres du site MODUSCAP
        $setting = new Setting();
        $setting->setSiteName('MODUSCAP');
        $setting->setEmail('contact@moduscap.store');
        $setting->setEmailSender('noreply@moduscap.store');
        $setting->setEmailReceived('contact@moduscap.store');
        
        $manager->persist($setting);
        $manager->flush();
    }
}