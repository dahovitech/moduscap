<?php

namespace App\DataFixtures;

use App\Entity\Language;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\ProductCategoryTranslation;
use App\Entity\ProductOption;
use App\Entity\ProductOptionGroup;
use App\Entity\ProductOptionGroupTranslation;
use App\Entity\ProductOptionTranslation;
use App\Entity\ProductTranslation;
use App\Entity\Media;
use App\Entity\ProductMedia;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixture complète qui crée d'abord les produits puis leurs médias
 * Évite les problèmes d'ordre d'exécution des fixtures séparées
 */
class CompleteFixtures extends Fixture
{
    // Configuration des médias par produit (même que MediaFixtures)
    private const MEDIA_CONFIG = [
        'capsule-house' => [
            'main' => 'o_1j3q9vbbdsgf22hil31kue7sda_15074.png',
            'gallery' => [
                'o_1j3qafmjg81m589fpk23b1sutk_23664.jpg',
                'o_1j47ae3vvv1eck1q5kbvk1p478_64516_12425.jpg',
                'o_1j47ag4v1a1i1jupin6m27g6c_71300_52290_39204_13379.jpg'
            ],
            'alt_texts' => [
                'fr' => [
                    'main' => 'Maison capsule moderne - Vue principale',
                    'gallery' => [
                        'Intérieur de la maison capsule',
                        'Vue extérieure détaillée',
                        'Détails architecturaux de la capsule'
                    ]
                ],
                'en' => [
                    'main' => 'Modern capsule house - Main view',
                    'gallery' => [
                        'Capsule house interior',
                        'Detailed exterior view',
                        'Architectural details of the capsule'
                    ]
                ]
            ]
        ],
        'apple-cabin' => [
            'main' => 'o_1j3qbs92qof98h11oj2s551edf9.jpg',
            'gallery' => [
                'o_1j3qc5q8h1hgh1dv56fs1hdk10m9k.jpg',
                'o_1j3qf369j1fvtg4h1jv114v51aqc8.jpg'
            ],
            'alt_texts' => [
                'fr' => [
                    'main' => 'Cabane Apple en bois - Vue principale',
                    'gallery' => [
                        'Intérieur cozy de la cabane',
                        'Ambiance chaleureux de la cabane Apple'
                    ]
                ],
                'en' => [
                    'main' => 'Apple wooden cabin - Main view',
                    'gallery' => [
                        'Cozy interior of the cabin',
                        'Warm atmosphere of the Apple cabin'
                    ]
                ]
            ]
        ],
        'natural-house' => [
            'main' => 'o_1j3qbcbqgpmc14r51ac41sghotsg.jpg',
            'gallery' => [
                'o_1j3qfjorkedkj92pjuao8vr8.jpg',
                'o_1j3qg0nr8hj010lb1p36lv16ia8.jpg',
                'o_1j3qg2kpupkt4tprdk198d1g269_28910.jpg',
                'Natural.jpg@v=20251003.jpg',
                'A1_left side.jpg@v=20251003.jpg',
                'D1_right side.jpg@v=20251003.jpg',
                'E8_left side.jpg@v=20251003.jpg'
            ],
            'alt_texts' => [
                'fr' => [
                    'main' => 'Maison naturelle écologique - Vue principale',
                    'gallery' => [
                        'Matériaux naturels de la maison',
                        'Vue d\'ensemble de l\'architecture',
                        'Détails de construction écologique',
                        'Modèle Natural - Vue principale',
                        'Vue côté gauche modèle A1',
                        'Vue côté droit modèle D1',
                        'Vue côté gauche modèle E8'
                    ]
                ],
                'en' => [
                    'main' => 'Ecological natural house - Main view',
                    'gallery' => [
                        'Natural materials of the house',
                        'Overview of the architecture',
                        'Ecological construction details',
                        'Natural model - Main view',
                        'Left side view model A1',
                        'Right side view model D1',
                        'Left side view model E8'
                    ]
                ]
            ]
        ],
        'dome-house' => [
            'main' => 'o_1igb671gubt3b68135i17appagb_33825.jpg',
            'gallery' => [
                'o_1igb6fo0j76m1cmmko11ke41r0c8_71287.jpg',
                'o_1j3qavh2815l09jcntfolqirhb.jpg',
                'o_1j3qb0q9718d01sv61op81339r6cb.jpg'
            ],
            'alt_texts' => [
                'fr' => [
                    'main' => 'Maison dôme futuriste - Vue principale',
                    'gallery' => [
                        'Architecture unique en forme de dôme',
                        'Design moderne et innovant',
                        'Structure géodésique détaillée'
                    ]
                ],
                'en' => [
                    'main' => 'Futuristic dome house - Main view',
                    'gallery' => [
                        'Unique dome-shaped architecture',
                        'Modern and innovative design',
                        'Detailed geodesic structure'
                    ]
                ]
            ]
        ],
        'model-double' => [
            'main' => 'o_1iou50gg5k8l1lsfhrtqdd1b61t_12826_67060_47166_74242_54459.jpg',
            'gallery' => [
                'o_1j3qev1bh6si13va1nac1b6v1nd98.jpg',
                'o_1j3qfb97l1scngf01h7g151s1h6a8.jpg'
            ],
            'alt_texts' => [
                'fr' => [
                    'main' => 'Modèle double spacieux - Vue principale',
                    'gallery' => [
                        'Espace double optimal',
                        'Aménagement intérieur fonctionnel'
                    ]
                ],
                'en' => [
                    'main' => 'Spacious double model - Main view',
                    'gallery' => [
                        'Optimal double space',
                        'Functional interior layout'
                    ]
                ]
            ]
        ]
    ];

    // Mapping des noms de dossiers par catégorie
    private const FOLDER_MAPPINGS = [
        'capsule-house' => 'Capsule House',
        'apple-cabin' => 'Apple Cabin',
        'natural-house' => 'Natural House',
        'dome-house' => 'Dome House',
        'model-double' => 'Model Double',
        'catalogues' => 'catalogues',
    ];

    // Mapping des types MIME par extension
    private const MIME_TYPES = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp'
    ];

    public function load(ObjectManager $manager): void
    {
        echo "🚀 Démarrage du chargement complet (produits + médias)\n";

        try {
            // ÉTAPE 1: Créer les produits d'abord
            $this->createProducts($manager);

            // ÉTAPE 2: Créer les médias pour ces produits
            $this->createProductMedia($manager);

            $manager->flush();
            echo "✅ Fixtures complètes chargées avec succès !\n";

        } catch (\Exception $e) {
            echo "❌ Erreur lors du chargement des fixtures complètes: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    /**
     * ÉTAPE 1: Création des produits (copié de ProductFixtures)
     */
    private function createProducts(ObjectManager $manager): void
    {
        echo "📦 Création des produits...\n";

        // Get existing languages
        $french = $manager->getRepository(Language::class)->findOneBy(['code' => 'fr']);
        $english = $manager->getRepository(Language::class)->findOneBy(['code' => 'en']);
        
        if (!$french) {
            echo "⚠️  Langue française non trouvée, skips des produits\n";
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

        $categories = [];
        foreach ($categoriesData as $categoryData) {
            $category = new ProductCategory();
            $category->setCode($categoryData['code']);
            $category->setIsActive(true);
            $category->setIsFeatured(true);
            $category->setSortOrder(array_search($categoryData, $categoriesData) + 1);

            // Add translations
            foreach ($categoryData['translations'] as $locale => $translationData) {
                $language = $manager->getRepository(Language::class)->findOneBy(['code' => $locale]);
                if ($language) {
                    $translation = new ProductCategoryTranslation();
                    $translation->setName($translationData['name']);
                    $translation->setDescription($translationData['description']);
                    $translation->setShortDescription($translationData['shortDescription']);
                    $translation->setLanguage($language);
                    $category->addTranslation($translation);
                }
            }

            $manager->persist($category);
            $categories[] = $category;
        }

        // Create Option Groups
        $optionGroupsData = [
            [
                'code' => 'bardage',
                'inputType' => 'select',
                'isRequired' => true,
                'translations' => [
                    'fr' => [
                        'name' => 'Type de bardage',
                        'description' => 'Choisissez le type de bardage pour vos murs extérieurs'
                    ],
                    'en' => [
                        'name' => 'Cladding type',
                        'description' => 'Choose the type of cladding for your exterior walls'
                    ]
                ]
            ],
            [
                'code' => 'couverture',
                'inputType' => 'select',
                'isRequired' => true,
                'translations' => [
                    'fr' => [
                        'name' => 'Type de couverture',
                        'description' => 'Sélectionnez le matériau de couverture pour votre toiture'
                    ],
                    'en' => [
                        'name' => 'Roofing type',
                        'description' => 'Select the roofing material for your roof'
                    ]
                ]
            ],
            [
                'code' => 'equipements',
                'inputType' => 'multiselect',
                'isRequired' => false,
                'translations' => [
                    'fr' => [
                        'name' => 'Équipements optionnels',
                        'description' => 'Équipements supplémentaires pour améliorer votre confort'
                    ],
                    'en' => [
                        'name' => 'Optional equipment',
                        'description' => 'Additional equipment to improve your comfort'
                    ]
                ]
            ]
        ];

        $optionGroups = [];
        foreach ($optionGroupsData as $groupData) {
            $group = new ProductOptionGroup();
            $group->setCode($groupData['code']);
            $group->setInputType($groupData['inputType']);
            $group->setIsRequired($groupData['isRequired']);
            $group->setIsActive(true);
            $group->setSortOrder(array_search($groupData, $optionGroupsData) + 1);

            // Add translations
            foreach ($groupData['translations'] as $locale => $translationData) {
                $language = $manager->getRepository(Language::class)->findOneBy(['code' => $locale]);
                if ($language) {
                    $translation = new ProductOptionGroupTranslation();
                    $translation->setName($translationData['name']);
                    $translation->setDescription($translationData['description']);
                    $translation->setLanguage($language);
                    $group->addTranslation($translation);
                }
            }

            $manager->persist($group);
            $optionGroups[] = $group;
        }

        // Create Options for bardage group
        $bardageOptions = [
            ['code' => 'bardage-original', 'price' => 0, 'translations' => [
                'fr' => ['name' => 'Original', 'description' => 'Bardage original suivant les plans de base'],
                'en' => ['name' => 'Original', 'description' => 'Original cladding following basic plans']
            ]],
            ['code' => 'bardage-terre', 'price' => 2500, 'translations' => [
                'fr' => ['name' => 'Bardage terre', 'description' => 'Solution rustique avec bardage terre'],
                'en' => ['name' => 'Earth cladding', 'description' => 'Rustic solution with earth cladding']
            ]],
            ['code' => 'bardage-aluminium', 'price' => 1800, 'translations' => [
                'fr' => ['name' => 'Bardage aluminium', 'description' => 'Bardage aluminium haute qualité'],
                'en' => ['name' => 'Aluminium cladding', 'description' => 'High quality aluminium cladding']
            ]]
        ];

        foreach ($bardageOptions as $optionData) {
            $option = new ProductOption();
            $option->setCode($optionData['code']);
            $option->setPrice($optionData['price']);
            $option->setIsActive(true);
            $option->setSortOrder(array_search($optionData, $bardageOptions) + 1);
            $option->setOptionGroup($optionGroups[0]); // bardage group

            // Add translations
            foreach ($optionData['translations'] as $locale => $translationData) {
                $language = $manager->getRepository(Language::class)->findOneBy(['code' => $locale]);
                if ($language) {
                    $translation = new ProductOptionTranslation();
                    $translation->setName($translationData['name']);
                    $translation->setDescription($translationData['description']);
                    $translation->setLanguage($language);
                    $option->addTranslation($translation);
                }
            }

            $manager->persist($option);
        }

        // Create Options for couverture group
        $couvertureOptions = [
            ['code' => 'couverture-tuiles', 'price' => 0, 'translations' => [
                'fr' => ['name' => 'Tuiles terre cuite', 'description' => 'Couverture en tuiles terre cuite'],
                'en' => ['name' => 'Clay tiles', 'description' => 'Clay tile roofing']
            ]],
            ['code' => 'couverture-vegetale', 'price' => 3200, 'translations' => [
                'fr' => ['name' => 'Toiture végétale', 'description' => 'Couverture végétalisée écologique'],
                'en' => ['name' => 'Green roof', 'description' => 'Ecological green roofing']
            ]],
            ['code' => 'couverture-étanche', 'price' => 1500, 'translations' => [
                'fr' => ['name' => 'Toiture étanchéité renforcée', 'description' => 'Système d\'étanchéité renforcé'],
                'en' => ['name' => 'Reinforced waterproofing', 'description' => 'Reinforced waterproofing system']
            ]]
        ];

        foreach ($couvertureOptions as $optionData) {
            $option = new ProductOption();
            $option->setCode($optionData['code']);
            $option->setPrice($optionData['price']);
            $option->setIsActive(true);
            $option->setSortOrder(array_search($optionData, $couvertureOptions) + 1);
            $option->setOptionGroup($optionGroups[1]); // couverture group

            // Add translations
            foreach ($optionData['translations'] as $locale => $translationData) {
                $language = $manager->getRepository(Language::class)->findOneBy(['code' => $locale]);
                if ($language) {
                    $translation = new ProductOptionTranslation();
                    $translation->setName($translationData['name']);
                    $translation->setDescription($translationData['description']);
                    $translation->setLanguage($language);
                    $option->addTranslation($translation);
                }
            }

            $manager->persist($option);
        }

        // Create Options for équipements group
        $equipementsOptions = [
            ['code' => 'climatisation', 'price' => 3500, 'translations' => [
                'fr' => ['name' => 'Climatisation', 'description' => 'Système de climatisation réversible'],
                'en' => ['name' => 'Air conditioning', 'description' => 'Reversible air conditioning system']
            ]],
            ['code' => 'domotique', 'price' => 2800, 'translations' => [
                'fr' => ['name' => 'Système domotique', 'description' => 'Maison connectée avec contrôle smartphone'],
                'en' => ['name' => 'Smart home system', 'description' => 'Connected house with smartphone control']
            ]],
            ['code' => 'cheminee', 'price' => 4200, 'translations' => [
                'fr' => ['name' => 'Cheminée insert', 'description' => 'Cheminée insert pour ambiance chaleureuse'],
                'en' => ['name' => 'Fireplace insert', 'description' => 'Fireplace insert for warm atmosphere']
            ]]
        ];

        foreach ($equipementsOptions as $optionData) {
            $option = new ProductOption();
            $option->setCode($optionData['code']);
            $option->setPrice($optionData['price']);
            $option->setIsActive(true);
            $option->setSortOrder(array_search($optionData, $equipementsOptions) + 1);
            $option->setOptionGroup($optionGroups[2]); // equipements group

            // Add translations
            foreach ($optionData['translations'] as $locale => $translationData) {
                $language = $manager->getRepository(Language::class)->findOneBy(['code' => $locale]);
                if ($language) {
                    $translation = new ProductOptionTranslation();
                    $translation->setName($translationData['name']);
                    $translation->setDescription($translationData['description']);
                    $translation->setLanguage($language);
                    $option->addTranslation($translation);
                }
            }

            $manager->persist($option);
        }

        // Create Products based on categories
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
                $language = $manager->getRepository(Language::class)->findOneBy(['code' => $locale]);
                if ($language) {
                    $translation = new ProductTranslation();
                    $translation->setName($translationData['name']);
                    $translation->setDescription($translationData['description']);
                    $translation->setShortDescription($translationData['shortDescription']);
                    $translation->setLanguage($language);
                    $product->addTranslation($translation);
                }
            }

            $manager->persist($product);
        }

        echo "✅ " . count($categories) . " catégories et produits créés\n";
    }

    /**
     * ÉTAPE 2: Création des médias pour les produits (copié de MediaFixtures)
     */
    private function createProductMedia(ObjectManager $manager): void
    {
        echo "🖼️  Création des médias pour les produits...\n";

        // Récupération des produits existants (ils ont été créés à l'étape 1)
        $products = $manager->getRepository(Product::class)->findAll();
        
        if (empty($products)) {
            echo "⚠️  Aucun produit trouvé pour créer les médias\n";
            return;
        }

        echo "📊 " . count($products) . ' produits trouvés pour la création de médias\n';

        // Traitement des produits par batch pour optimiser les performances
        $this->processProductsInBatches($manager, $products);

        echo "✅ Médias créés avec succès\n";
    }

    /**
     * Traite les produits par batch pour optimiser les performances
     */
    private function processProductsInBatches(ObjectManager $manager, array $products, int $batchSize = 10): void
    {
        $processed = 0;
        
        foreach ($products as $product) {
            try {
                $this->createProductImages($manager, $product);
                $processed++;
                
                // Flush périodique pour éviter la saturation mémoire
                if ($processed % $batchSize === 0) {
                    $manager->flush();
                    echo "📦 Batch de {$batchSize} produits traité (processed: {$processed})\n";
                }
                
            } catch (\Exception $e) {
                echo "❌ Erreur lors du traitement du produit: " . $product->getCode() . " - " . $e->getMessage() . "\n";
                // Continue avec les autres produits même en cas d'erreur
            }
        }
    }

    private function createProductImages(ObjectManager $manager, Product $product): void
    {
        $productCode = $product->getCode();
        
        // Vérification de la configuration pour ce produit
        if (!isset(self::MEDIA_CONFIG[$productCode])) {
            echo "⚠️  Aucun média configuré pour le produit: {$productCode}\n";
            return;
        }

        $mediaConfig = self::MEDIA_CONFIG[$productCode];
        echo "🖼️  Traitement des médias pour le produit: {$productCode}\n";

        // Création de l'image principale
        if (isset($mediaConfig['main'])) {
            $mainImagePath = $this->validateImagePath($product, $mediaConfig['main']);
            if ($mainImagePath) {
                $altText = $this->getAltTextForImage($product, 'main', $mediaConfig, $manager);
                $this->createMedia($manager, $product, $mediaConfig['main'], 'main_image', true, 0, $altText, $mainImagePath);
            }
        }

        // Création des images de galerie
        if (isset($mediaConfig['gallery']) && is_array($mediaConfig['gallery'])) {
            $sortOrder = 1;
            foreach ($mediaConfig['gallery'] as $index => $galleryImage) {
                $galleryImagePath = $this->validateImagePath($product, $galleryImage);
                if ($galleryImagePath) {
                    $altText = $this->getAltTextForImage($product, 'gallery', $mediaConfig, $manager, $index);
                    $this->createMedia($manager, $product, $galleryImage, 'gallery', false, $sortOrder, $altText, $galleryImagePath);
                    $sortOrder++;
                }
            }
        }
    }

    /**
     * Valide l'existence du fichier image et retourne le chemin complet en essayant différents dossiers
     */
    private function validateImagePath(Product $product, string $fileName): ?string
    {
        // Essai avec le code du produit
        $imagePath = $this->buildImagePath($product->getCode(), $fileName);
        if (file_exists($imagePath)) {
            return $imagePath;
        }

        // Essai avec le nom de dossier de la catégorie
        if ($product->getCategory()) {
            $categoryFolder = $this->getCategoryFolderName($product->getCategory()->getCode());
            $imagePath = $this->buildImagePath($categoryFolder, $fileName);
            if (file_exists($imagePath)) {
                return $imagePath;
            }
        }

        // Aucune image trouvée
        echo "❌ Fichier image non trouvé pour le produit: {$product->getCode()} - fichier: {$fileName}\n";

        return null;
    }

    /**
     * Génère le texte alternatif pour l'image en tenant compte des langues disponibles
     */
    private function getAltTextForImage(Product $product, string $imageType, array $mediaConfig, ObjectManager $manager, int $galleryIndex = null): string
    {
        // Récupération des langues disponibles
        $languages = $manager->getRepository(Language::class)->findBy(['isActive' => true]);
        $defaultLocale = $this->getDefaultLocale($languages);
        
        // Définition des textes alternatifs par défaut si non configurés
        $defaultAltTexts = $this->getDefaultAltTexts($product, $imageType, $galleryIndex);
        
        // Vérification si des textes alternatifs sont configurés
        if (!isset($mediaConfig['alt_texts'])) {
            return $defaultAltTexts[$defaultLocale] ?? $defaultAltTexts['fr'] ?? $product->getName();
        }

        // Priorité à la langue par défaut, sinon première langue disponible
        $targetLocale = $defaultLocale;
        if (!isset($mediaConfig['alt_texts'][$targetLocale])) {
            $availableLocales = array_keys($mediaConfig['alt_texts']);
            $targetLocale = $availableLocales[0] ?? $defaultLocale;
        }

        // Récupération du texte alternatif dans la langue cible
        if ($imageType === 'main' && isset($mediaConfig['alt_texts'][$targetLocale]['main'])) {
            return $mediaConfig['alt_texts'][$targetLocale]['main'];
        }
        
        if ($imageType === 'gallery' && isset($mediaConfig['alt_texts'][$targetLocale]['gallery'][$galleryIndex])) {
            return $mediaConfig['alt_texts'][$targetLocale]['gallery'][$galleryIndex];
        }

        // Fallback vers le texte par défaut
        return $defaultAltTexts[$targetLocale] ?? $defaultAltTexts['fr'] ?? $product->getName();
    }

    private function getDefaultLocale(array $languages): string
    {
        foreach ($languages as $language) {
            if ($language->getIsDefault()) {
                return $language->getLocale();
            }
        }
        return 'fr'; // Fallback vers le français
    }

    private function getDefaultAltTexts(Product $product, string $imageType, ?int $galleryIndex): array
    {
        $productName = $product->getName();
        
        return [
            'fr' => $imageType === 'main' 
                ? "{$productName} - Image principale"
                : "{$productName} - Galerie" . ($galleryIndex !== null ? " " . ($galleryIndex + 1) : ""),
            'en' => $imageType === 'main' 
                ? "{$productName} - Main image"
                : "{$productName} - Gallery" . ($galleryIndex !== null ? " " . ($galleryIndex + 1) : "")
        ];
    }

    private function createMedia(
        ObjectManager $manager, 
        Product $product, 
        string $fileName, 
        string $mediaType, 
        bool $isMainImage, 
        int $sortOrder, 
        string $altText,
        string $imagePath
    ): void {
        try {
            // Vérification supplémentaire de l'existence du fichier
            if (!file_exists($imagePath)) {
                echo "❌ Fichier image inexistant lors de la création: {$imagePath} - fichier: {$fileName}\n";
                return;
            }

            // Création de l'entité Media
            $media = new Media();
            $media->setFileName($fileName);
            $media->setAlt($altText);
            $media->setExtension($this->getFileExtension($fileName));

            $manager->persist($media);

            // Création de la relation ProductMedia
            $productMedia = new ProductMedia();
            $productMedia->setProduct($product);
            $productMedia->setMedia($media);
            $productMedia->setMediaType($mediaType);
            $productMedia->setIsMainImage($isMainImage);
            $productMedia->setSortOrder($sortOrder);

            $manager->persist($productMedia);

            echo "✅ Média créé avec succès: {$product->getCode()} - {$fileName} ({$mediaType})\n";

        } catch (\Exception $e) {
            echo "❌ Erreur lors de la création du média pour {$product->getCode()} - {$fileName}: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    private function getCategoryFolderName(?string $categoryCode): string
    {
        return self::FOLDER_MAPPINGS[$categoryCode] ?? 'otherpic';
    }

    private function getMimeType(string $fileName): string
    {
        $extension = $this->getFileExtension($fileName);
        return self::MIME_TYPES[strtolower($extension)] ?? 'image/jpeg';
    }

    private function getFileExtension(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_EXTENSION);
    }

    /**
     * Construit le chemin complet vers un fichier image
     */
    private function buildImagePath(string $folderName, string $fileName): string
    {
        return sprintf(
            '%s/public/images/products/%s/%s',
            $this->getProjectRoot(),
            $folderName,
            $fileName
        );
    }

    /**
     * Récupère le chemin racine du projet
     */
    private function getProjectRoot(): string
    {
        return dirname(__DIR__, 4); // Remonte 4 niveaux depuis src/DataFixtures
    }
}