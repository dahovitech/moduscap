<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductMedia;
use App\Entity\Media;
use App\Entity\ProductCategory;
use App\Entity\ProductCategoryTranslation;
use App\Entity\ProductTranslation;
use App\Entity\Language;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixture pour créer des produits d'exemple avec images
 */
class ProductWithImagesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Récupérer les médias de référence
        $mediaRepository = $manager->getRepository(Media::class);
        $languageRepository = $manager->getRepository(Language::class);
        $categoryRepository = $manager->getRepository(ProductCategory::class);
        
        // Récupérer les médias de référence ou créer des médias de fallback
        $heroMedia = $mediaRepository->findOneBy(['fileName' => 'hero-building-main.jpg']);
        $galleryMedia = $mediaRepository->findOneBy(['fileName' => 'gallery-house-modern.jpeg']);
        $technicalMedia = $mediaRepository->findOneBy(['fileName' => 'technical-plans-construction.jpg']);
        $lifestyleMedia = $mediaRepository->findOneBy(['fileName' => 'lifestyle-family-home.jpg']);
        $constructionMedia = $mediaRepository->findOneBy(['fileName' => 'construction-materials.jpg']);

        // Créer des médias de fallback si nécessaire
        $heroMedia = $heroMedia ?: $this->createFallbackMedia($manager, 'hero-building-main.jpg', 'image/jpeg');
        $galleryMedia = $galleryMedia ?: $this->createFallbackMedia($manager, 'gallery-house-modern.jpeg', 'image/jpeg');
        $technicalMedia = $technicalMedia ?: $this->createFallbackMedia($manager, 'technical-plans-construction.jpg', 'image/jpeg');
        $lifestyleMedia = $lifestyleMedia ?: $this->createFallbackMedia($manager, 'lifestyle-family-home.jpg', 'image/jpeg');
        $constructionMedia = $constructionMedia ?: $this->createFallbackMedia($manager, 'construction-materials.jpg', 'image/jpeg');

        $french = $languageRepository->findOneBy(['code' => 'fr']);
        $english = $languageRepository->findOneBy(['code' => 'en']);

        // Créer les langues si elles n'existent pas
        if (!$french) {
            $french = new Language();
            $french->setCode('fr');
            $french->setName('Français');
            $french->setNativeName('Français');
            $french->setIsActive(true);
            $french->setIsDefault(true);
            $french->setSortOrder(1);
            $manager->persist($french);
        }

        if (!$english) {
            $english = new Language();
            $english->setCode('en');
            $english->setName('English');
            $english->setNativeName('English');
            $english->setIsActive(true);
            $english->setIsDefault(false);
            $english->setSortOrder(2);
            $manager->persist($english);
        }

        // Créer des catégories si nécessaire
        $categories = [
            'modular' => 'Constructions modulaires',
            'traditional' => 'Constructions traditionnelles',
            'commercial' => 'Bâtiments commerciaux'
        ];

        foreach ($categories as $code => $name) {
            $existingCategory = $categoryRepository->findOneBy(['code' => $code]);
            if (!$existingCategory) {
                $category = new ProductCategory();
                $category->setCode($code);
                $category->setIsActive(true);
                $category->setSortOrder(0);
                
                // Ajouter des traductions
                $translationFr = new ProductCategoryTranslation();
                $translationFr->setProductCategory($category);
                $translationFr->setLanguage($french);
                $translationFr->setName($name);
                $translationFr->setDescription("Catégorie de {$name} de haute qualité");
                $manager->persist($translationFr);
                $category->addTranslation($translationFr);
                
                $translationEn = new ProductCategoryTranslation();
                $translationEn->setProductCategory($category);
                $translationEn->setLanguage($english);
                $translationEn->setName($name);
                $translationEn->setDescription("High quality {$name} category");
                $manager->persist($translationEn);
                $category->addTranslation($translationEn);
                
                $manager->persist($category);
            }
        }

        $manager->flush();

        // Créer des produits d'exemple avec images
        $this->createSampleProduct($manager, 'MODULE-001', $heroMedia, $galleryMedia, $technicalMedia, $lifestyleMedia, $constructionMedia, $french, $english, $categoryRepository->findOneBy(['code' => 'modular']));
        $this->createSampleProduct($manager, 'TRAD-001', $galleryMedia, $heroMedia, $technicalMedia, $lifestyleMedia, $constructionMedia, $french, $english, $categoryRepository->findOneBy(['code' => 'traditional']));
        $this->createSampleProduct($manager, 'COMM-001', $lifestyleMedia, $galleryMedia, $technicalMedia, $heroMedia, $constructionMedia, $french, $english, $categoryRepository->findOneBy(['code' => 'commercial']));
    }

    private function createSampleProduct(ObjectManager $manager, string $code, Media $mainImage, Media $galleryImage, Media $technicalImage, Media $lifestyleImage, Media $constructionImage, Language $french, Language $english, ProductCategory $category): void
    {
        $product = new Product();
        $product->setCode($code);
        $product->setBasePrice('250000.00');
        $product->setSurface('120.50');
        $product->setDimensions('12m x 10m x 3m');
        $product->setRooms(5);
        $product->setHeight(300);
        $product->setTechnicalSpecs('Structure acier galvanisé, isolation panneaux sandwich, électricité basse tension');
        $product->setAssemblyTime(7);
        $product->setEnergyClass('A+');
        $product->setWarrantyStructure(20);
        $product->setWarrantyEquipment(5);
        $product->setIsActive(true);
        $product->setIsFeatured(true);
        $product->setIsCustomizable(true);
        $product->setCategory($category);
        $product->setCreatedAt(new \DateTime());
        $product->setUpdatedAt(new \DateTime());

        // Traductions françaises
        $translationFr = new ProductTranslation();
        $translationFr->setProduct($product);
        $translationFr->setLanguage($french);
        $translationFr->setName("Construction {$code} - Moderne");
        $translationFr->setDescription("Une construction moderne et durable offrant le meilleur du confort et de l'efficacité énergétique.");
        $translationFr->setShortDescription("Construction moderne, économique et écologique");
        $translationFr->setConcept("Design contemporain avec espaces optimisés");
        $translationFr->setMaterialsDetail("Acier galvanisé, isolation haute performance, vitrage double");
        $translationFr->setEquipmentDetail("Cuisine équipée, système de chauffage économique, domotique");
        $translationFr->setPerformanceDetails("Classe énergétique A+, isolation thermique performante");
        $translationFr->setSpecifications("Surface: 120m², 5 pièces, hauteur sous plafond 3m");
        $translationFr->setAdvantages("Montage rapide, personnalisation, durabilité, écologique");
        $product->addTranslation($translationFr);

        // Traductions anglaises
        $translationEn = new ProductTranslation();
        $translationEn->setProduct($product);
        $translationEn->setLanguage($english);
        $translationEn->setName("Construction {$code} - Modern");
        $translationEn->setDescription("A modern and durable construction offering the best of comfort and energy efficiency.");
        $translationEn->setShortDescription("Modern, economical and ecological construction");
        $translationEn->setConcept("Contemporary design with optimized spaces");
        $translationEn->setMaterialsDetail("Galvanized steel, high performance insulation, double glazing");
        $translationEn->setEquipmentDetail("Fitted kitchen, economical heating system, home automation");
        $translationEn->setPerformanceDetails("Energy class A+, high performance thermal insulation");
        $translationEn->setSpecifications("Area: 120m², 5 rooms, ceiling height 3m");
        $translationEn->setAdvantages("Quick assembly, customization, durability, ecological");
        $product->addTranslation($translationEn);

        // Ajouter les images
        if ($mainImage) {
            $productMedia = new ProductMedia();
            $productMedia->setProduct($product);
            $productMedia->setMedia($mainImage);
            $productMedia->setMediaType('main_image');
            $productMedia->setIsMainImage(true);
            $productMedia->setSortOrder(1);
            $productMedia->setCreatedAt(new \DateTime());
            $productMedia->setUpdatedAt(new \DateTime());
            $product->addProductMedia($productMedia);
        }

        if ($galleryImage) {
            $productMedia = new ProductMedia();
            $productMedia->setProduct($product);
            $productMedia->setMedia($galleryImage);
            $productMedia->setMediaType('gallery');
            $productMedia->setIsMainImage(false);
            $productMedia->setSortOrder(2);
            $productMedia->setCreatedAt(new \DateTime());
            $productMedia->setUpdatedAt(new \DateTime());
            $product->addProductMedia($productMedia);
        }

        if ($technicalImage) {
            $productMedia = new ProductMedia();
            $productMedia->setProduct($product);
            $productMedia->setMedia($technicalImage);
            $productMedia->setMediaType('technical');
            $productMedia->setIsMainImage(false);
            $productMedia->setSortOrder(3);
            $productMedia->setCreatedAt(new \DateTime());
            $productMedia->setUpdatedAt(new \DateTime());
            $product->addProductMedia($productMedia);
        }

        if ($lifestyleImage) {
            $productMedia = new ProductMedia();
            $productMedia->setProduct($product);
            $productMedia->setMedia($lifestyleImage);
            $productMedia->setMediaType('lifestyle');
            $productMedia->setIsMainImage(false);
            $productMedia->setSortOrder(4);
            $productMedia->setCreatedAt(new \DateTime());
            $productMedia->setUpdatedAt(new \DateTime());
            $product->addProductMedia($productMedia);
        }

        $manager->persist($product);
        $manager->flush();
    }

    /**
     * Crée un média de fallback si le média recherché n'existe pas
     */
    private function createFallbackMedia(ObjectManager $manager, string $fileName, string $mimeType): Media
    {
        $media = new Media();
        $media->setFileName($fileName);
        $media->setMimeType($mimeType);
        $media->setOriginalName($fileName);
        $media->setAltText('Image de démonstration');
        $media->setIsActive(true);
        $media->setSortOrder(1);
        
        $manager->persist($media);
        
        return $media;
    }
}