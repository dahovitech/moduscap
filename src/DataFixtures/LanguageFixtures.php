<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Language;
use App\Entity\Setting;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LanguageFixtures extends Fixture
{
    public const LANGUAGE_FRENCH = 'language_french';
    public const LANGUAGE_PORTUGUESE = 'language_portuguese';
    public const LANGUAGE_GERMAN = 'language_german';
    public const LANGUAGE_ITALIAN = 'language_italian';
    public const LANGUAGE_NORWEGIAN = 'language_norwegian';
    public const LANGUAGE_LITHUANIAN = 'language_lithuanian';
    public const LANGUAGE_SPANISH = 'language_spanish';
    public const LANGUAGE_DUTCH = 'language_dutch';

    public function load(ObjectManager $manager): void
    {
        // Création des langues
        $languages = [
            [
                'code' => 'fr',
                'name' => 'Française',
                'nativeName' => 'Français',
                'isActive' => true,
                'isDefault' => true,
                'sortOrder' => 1,
                'constant' => self::LANGUAGE_FRENCH
            ],
            [
                'code' => 'pt',
                'name' => 'Portugaise',
                'nativeName' => 'Português',
                'isActive' => true,
                'isDefault' => false,
                'sortOrder' => 2,
                'constant' => self::LANGUAGE_PORTUGUESE
            ],
            [
                'code' => 'de',
                'name' => 'Allemande',
                'nativeName' => 'Deutsch',
                'isActive' => true,
                'isDefault' => false,
                'sortOrder' => 3,
                'constant' => self::LANGUAGE_GERMAN
            ],
            [
                'code' => 'it',
                'name' => 'Italienne',
                'nativeName' => 'Italiano',
                'isActive' => true,
                'isDefault' => false,
                'sortOrder' => 4,
                'constant' => self::LANGUAGE_ITALIAN
            ],
            [
                'code' => 'no',
                'name' => 'Norvégienne',
                'nativeName' => 'Norsk',
                'isActive' => true,
                'isDefault' => false,
                'sortOrder' => 5,
                'constant' => self::LANGUAGE_NORWEGIAN
            ],
            [
                'code' => 'lt',
                'name' => 'Lithuanienne',
                'nativeName' => 'Lietuvių',
                'isActive' => true,
                'isDefault' => false,
                'sortOrder' => 6,
                'constant' => self::LANGUAGE_LITHUANIAN
            ],
            [
                'code' => 'es',
                'name' => 'Espagnole',
                'nativeName' => 'Español',
                'isActive' => true,
                'isDefault' => false,
                'sortOrder' => 7,
                'constant' => self::LANGUAGE_SPANISH
            ],
            [
                'code' => 'nl',
                'name' => 'Néerlandaise',
                'nativeName' => 'Nederlands',
                'isActive' => true,
                'isDefault' => false,
                'sortOrder' => 8,
                'constant' => self::LANGUAGE_DUTCH
            ]
        ];

        foreach ($languages as $languageData) {
            $language = new Language();
            $language->setCode($languageData['code']);
            $language->setName($languageData['name']);
            $language->setNativeName($languageData['nativeName']);
            $language->setIsActive($languageData['isActive']);
            $language->setIsDefault($languageData['isDefault']);
            $language->setSortOrder($languageData['sortOrder']);
            
            $manager->persist($language);
            $this->addReference($languageData['constant'], $language);
        }


        $manager->flush();
    }
}