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
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Get existing languages
        $french = $manager->getRepository(Language::class)->findOneBy(['code' => 'fr']);
        $english = $manager->getRepository(Language::class)->findOneBy(['code' => 'en']);
        
        if (!$french) {
            return; // Skip if French language doesn't exist
        }

        // Create Product Categories
        $categoriesData = [
            [
                'code' => 'capsule-house',
                'basePrice' => 38000,
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
                'basePrice' => 45000,
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
                'basePrice' => 48000,
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
                'basePrice' => 52000,
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
                'basePrice' => 68000,
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
            $category->setBasePrice($categoryData['basePrice']);
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
            $product->setBasePrice($category->getBasePrice());
            $product->setIsActive(true);
            $product->setIsFeatured(true);
            $product->setIsCustomizable(true);
            $product->setSortOrder($index + 1);

            // Set product specifications based on category
            switch ($category->getCode()) {
                case 'capsule-house':
                    $product->setSurface(28);
                    $product->setDimensions('6m × 4,7m × 2,8m');
                    $product->setRooms(1);
                    $product->setHeight(250);
                    $product->setAssemblyTime(1);
                    $product->setWarrantyStructure(10);
                    $product->setWarrantyEquipment(5);
                    break;
                case 'apple-cabin':
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

        $manager->flush();
    }
}