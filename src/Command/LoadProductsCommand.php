<?php

namespace App\Command;

use App\Entity\Language;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\ProductCategoryTranslation;
// use App\Entity\ProductOption;
// use App\Entity\ProductOptionGroup;
// use App\Entity\ProductOptionGroupTranslation;
// use App\Entity\ProductOptionTranslation;
use App\Entity\ProductTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour charger les produits et leurs données associées
 */
#[AsCommand(
    name: 'app:load-products',
    description: 'Charge les produits et catégories en base de données',
)]
class LoadProductsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('🚀 Chargement des produits MODUSCAP');

        try {
            $this->createProducts($io);
            $this->entityManager->flush();
            
            $io->success('✅ Produits chargés avec succès !');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('❌ Erreur lors du chargement des produits: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function createProducts(SymfonyStyle $io): void
    {
        $io->section('📦 Création des produits et catégories...');

        // Get existing languages
        $french = $this->entityManager->getRepository(Language::class)->findOneBy(['code' => 'fr']);
        $english = $this->entityManager->getRepository(Language::class)->findOneBy(['code' => 'en']);
        
        if (!$french) {
            $io->warning('⚠️  Langue française non trouvée, skip des produits');
            return;
        }

        // Create Product Categories
        $categoriesData = [
            [
                'code' => 'capsule-house',
                'translations' => [
                    'fr' => [
                        'name' => 'Capsule House',
                        'description' => 'Le Capsule House est l\'innovation de MODUSCAP pour l\'habitat ultra-compact. Conçu pour maximiser chaque mètre carré, il propose un espace de vie optimisé et fonctionnel.',
                        'shortDescription' => 'Habitat ultra-compact de 28m² - Le plus accessible de la gamme'
                    ],
                    'en' => [
                        'name' => 'Capsule House',
                        'description' => 'The Capsule House is MODUSCAP\'s innovation for ultra-compact housing. Designed to maximize every square meter, it offers optimized and functional living space.',
                        'shortDescription' => 'Ultra-compact habitat of 28m² - The most affordable in the range'
                    ]
                ]
            ],
            [
                'code' => 'apple-cabin',
                'translations' => [
                    'fr' => [
                        'name' => 'Apple Cabin',
                        'description' => 'L\'Apple Cabin représente l\'équilibre parfait entre fonctionnalité et esthétique moderne. Inspiré par les formes organiques et le design scandinave.',
                        'shortDescription' => 'Habitat de 35m² - Équilibre parfait entre design et fonctionnalité'
                    ],
                    'en' => [
                        'name' => 'Apple Cabin',
                        'description' => 'The Apple Cabin represents the perfect balance between functionality and modern aesthetics. Inspired by organic forms and Scandinavian design.',
                        'shortDescription' => '35m² habitat - Perfect balance between design and functionality'
                    ]
                ]
            ],
            [
                'code' => 'natural-house',
                'translations' => [
                    'fr' => [
                        'name' => 'Natural House',
                        'description' => 'Le Natural House respecte intégralement la philosophie de l\'écologie et des matériaux naturels. Habitat entièrement autonome et respectueux de l\'environnement.',
                        'shortDescription' => 'Habitat de 38m² - Éco-responsable avec autonomie énergétique'
                    ],
                    'en' => [
                        'name' => 'Natural House',
                        'description' => 'The Natural House fully respects the philosophy of ecology and natural materials. Entirely self-sufficient and environmentally friendly habitat.',
                        'shortDescription' => '38m² habitat - Eco-responsible with energy independence'
                    ]
                ]
            ],
            [
                'code' => 'dome-house',
                'translations' => [
                    'fr' => [
                        'name' => 'Dome House',
                        'description' => 'Le Dome House incarne l\'excellence architecturale de MODUSCAP. Avec sa forme sphérique innovante, il offre une expérience d\'habitat unique.',
                        'shortDescription' => 'Habitat de 42m² - Design unique avec forme sphérique'
                    ],
                    'en' => [
                        'name' => 'Dome House',
                        'description' => 'The Dome House embodies MODUSCAP\'s architectural excellence. With its innovative spherical shape, it offers a unique habitat experience.',
                        'shortDescription' => '42m² habitat - Unique design with spherical shape'
                    ]
                ]
            ],
            [
                'code' => 'model-double',
                'translations' => [
                    'fr' => [
                        'name' => 'Model Double',
                        'description' => 'Le Model Double représente la solution familiale premium de MODUSCAP. Conçu pour les maisonnées de 2-4 personnes, il offre l\'espace et le confort d\'une vraie maison.',
                        'shortDescription' => 'Habitat de 62m² - Solution familiale premium sur 2 niveaux'
                    ],
                    'en' => [
                        'name' => 'Model Double',
                        'description' => 'The Model Double represents MODUSCAP\'s premium family solution. Designed for households of 2-4 people, it offers the space and comfort of a real house.',
                        'shortDescription' => '62m² habitat - Premium family solution on 2 levels'
                    ]
                ]
            ]
        ];

        $io->progressStart(count($categoriesData));
        $categories = [];
        foreach ($categoriesData as $categoryData) {
            $category = new ProductCategory();
            $category->setCode($categoryData['code']);
            $category->setIsActive(true);
            $category->setIsFeatured(true);
            $category->setSortOrder(array_search($categoryData, $categoriesData) + 1);

            // Add translations
            foreach ($categoryData['translations'] as $locale => $translationData) {
                $language = $this->entityManager->getRepository(Language::class)->findOneBy(['code' => $locale]);
                if ($language) {
                    $translation = new ProductCategoryTranslation();
                    $translation->setName($translationData['name']);
                    $translation->setDescription($translationData['description']);
                    $translation->setShortDescription($translationData['shortDescription']);
                    $translation->setLanguage($language);
                    $category->addTranslation($translation);
                }
            }

            $this->entityManager->persist($category);
            $categories[] = $category;
            $io->progressAdvance();
        }
        $io->progressFinish();



        // Create Products based on categories
        $io->section('🏠 Création des produits...');
        $io->progressStart(count($categories));
        
        foreach ($categories as $index => $category) {
            $product = new Product();
            $product->setCode($category->getCode());
            $product->setCategory($category);
            $product->setIsActive(true);
            $product->setIsFeatured(true);
            $product->setIsCustomizable(true);
            $product->setSortOrder($index + 1);

            // Set product specifications based on category
            switch ($category->getCode()) {
                case 'capsule-house':
                    $product->setBasePrice(38000);
                    $product->setSurface(28);
                    $product->setDimensions('6m × 4,7m × 2,8m');
                    $product->setRooms(1);
                    $product->setHeight(250);
                    $product->setAssemblyTime(1);
                    $product->setWarrantyStructure(10);
                    $product->setWarrantyEquipment(5);
                    break;
                case 'apple-cabin':
                    $product->setBasePrice(45000);
                    $product->setSurface(35);
                    $product->setDimensions('7m × 5m × 3m');
                    $product->setRooms(2);
                    $product->setHeight(260);
                    $product->setAssemblyTime(2);
                    $product->setEnergyClass('B');
                    $product->setWarrantyStructure(10);
                    $product->setWarrantyEquipment(5);
                    break;
                case 'natural-house':
                    $product->setBasePrice(48000);
                    $product->setSurface(38);
                    $product->setDimensions('6,5m × 6m × 3,2m');
                    $product->setRooms(2);
                    $product->setHeight(320);
                    $product->setAssemblyTime(3);
                    $product->setEnergyClass('A+');
                    $product->setWarrantyStructure(10);
                    $product->setWarrantyEquipment(5);
                    break;
                case 'dome-house':
                    $product->setBasePrice(52000);
                    $product->setSurface(42);
                    $product->setDimensions('Diamètre 7,3m × 4,2m');
                    $product->setRooms(3);
                    $product->setHeight(420);
                    $product->setAssemblyTime(4);
                    $product->setEnergyClass('A+');
                    $product->setWarrantyStructure(10);
                    $product->setWarrantyEquipment(5);
                    break;
                case 'model-double':
                    $product->setBasePrice(68000);
                    $product->setSurface(62);
                    $product->setDimensions('8m × 4m × 6,5m');
                    $product->setRooms(4);
                    $product->setHeight(650);
                    $product->setAssemblyTime(4);
                    $product->setEnergyClass('A');
                    $product->setWarrantyStructure(10);
                    $product->setWarrantyEquipment(5);
                    break;
            }

            // Add translations using the same data as categories
            $categoryData = $categoriesData[$index];
            foreach ($categoryData['translations'] as $locale => $translationData) {
                $language = $this->entityManager->getRepository(Language::class)->findOneBy(['code' => $locale]);
                if ($language) {
                    $translation = new ProductTranslation();
                    $translation->setName($translationData['name']);
                    $translation->setDescription($translationData['description']);
                    $translation->setShortDescription($translationData['shortDescription']);
                    $translation->setLanguage($language);
                    $product->addTranslation($translation);
                }
            }

            $this->entityManager->persist($product);
            $io->progressAdvance();
        }
        $io->progressFinish();

        $io->info(sprintf('✅ %d catégories et %d produits créés avec succès', 
            count($categories),
            count($categories)
        ));
    }
}