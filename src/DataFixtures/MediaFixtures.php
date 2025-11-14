<?php

namespace App\DataFixtures;

use App\Entity\Media;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour les médias avec exemples d'images de produits
 */
class MediaFixtures extends Fixture
{
    public const MEDIA_HERO_BUILDING = 'media_hero_building';
    public const MEDIA_GALLERY_HOUSE = 'media_gallery_house';
    public const MEDIA_TECHNICAL_PLANS = 'media_technical_plans';
    public const MEDIA_LIFESTYLE_FAMILY = 'media_lifestyle_family';
    public const MEDIA_CONSTRUCTION_MATERIALS = 'media_construction_materials';

    public function load(ObjectManager $manager): void
    {
        // Image hero - Image principale de construction moderne
        $heroMedia = new Media();
        $heroMedia->setFileName('hero-building-main.jpg');
        $heroMedia->setAlt('Construction moderne - Image principale');
        $heroMedia->setExtension('jpg');
        $this->addReference(self::MEDIA_HERO_BUILDING, $heroMedia);
        $manager->persist($heroMedia);

        // Image gallery - Maison moderne
        $galleryMedia = new Media();
        $galleryMedia->setFileName('gallery-house-modern.jpeg');
        $galleryMedia->setAlt('Maison moderne - Galerie');
        $galleryMedia->setExtension('jpeg');
        $this->addReference(self::MEDIA_GALLERY_HOUSE, $galleryMedia);
        $manager->persist($galleryMedia);

        // Image technique - Plans de construction
        $technicalMedia = new Media();
        $technicalMedia->setFileName('technical-plans-construction.jpg');
        $technicalMedia->setAlt('Plans techniques de construction');
        $technicalMedia->setExtension('jpg');
        $this->addReference(self::MEDIA_TECHNICAL_PLANS, $technicalMedia);
        $manager->persist($technicalMedia);

        // Image lifestyle - Famille dans maison moderne
        $lifestyleMedia = new Media();
        $lifestyleMedia->setFileName('lifestyle-family-home.jpg');
        $lifestyleMedia->setAlt('Famille dans maison moderne - Lifestyle');
        $lifestyleMedia->setExtension('jpg');
        $this->addReference(self::MEDIA_LIFESTYLE_FAMILY, $lifestyleMedia);
        $manager->persist($lifestyleMedia);

        // Image construction - Matériaux et échafaudage
        $constructionMedia = new Media();
        $constructionMedia->setFileName('construction-materials.jpg');
        $constructionMedia->setAlt('Matériaux de construction et échafaudage');
        $constructionMedia->setExtension('jpg');
        $this->addReference(self::MEDIA_CONSTRUCTION_MATERIALS, $constructionMedia);
        $manager->persist($constructionMedia);

        $manager->flush();
    }
}