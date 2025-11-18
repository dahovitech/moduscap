<?php

namespace App\Command;

use App\Entity\Language;
use App\Entity\ProductCategory;
use App\Entity\ProductCategoryTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:load-categories',
    description: 'Charge les catégories de produits',
)]
class LoadCategoriesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Chargement des catégories de produits');

        // Données des catégories
        $categories = [
            [
                'code' => 'capsule-house',
                'name_fr' => 'Maison Capsule',
                'description_fr' => 'Maisons capsules compactes et modernes',
                'name_en' => 'Capsule House',
                'description_en' => 'Compact and modern capsule houses',
                'name_pt' => 'Casa Cápsula',
                'description_pt' => 'Casas cápsulas compactas e modernas',
                'name_de' => 'Kapselhaus',
                'description_de' => 'Kompakte und moderne Kapselhäuser',
                'name_it' => 'Casa Capsula',
                'description_it' => 'Case capsule compatte e moderne',
                'name_no' => 'Kapselhus',
                'description_no' => 'Kompakte og moderne kapselhus',
                'name_lt' => 'Kapsulės Namas',
                'description_lt' => 'Kompaktiški ir modernūs kapsuliniai namai',
                'name_es' => 'Casa Cápsula',
                'description_es' => 'Casas cápsulas compactas y modernas',
                'name_nl' => 'Capsule Huis',
                'description_nl' => 'Compacte en moderne capsule huizen',
                'isActive' => true,
                'sortOrder' => 1
            ],
            [
                'code' => 'module-structure',
                'name_fr' => 'Structure Modulaire',
                'description_fr' => 'Structures modulaires flexibles',
                'name_en' => 'Modular Structure',
                'description_en' => 'Flexible modular structures',
                'name_pt' => 'Estrutura Modular',
                'description_pt' => 'Estruturas modulares flexíveis',
                'name_de' => 'Modulare Struktur',
                'description_de' => 'Flexible modulare Strukturen',
                'name_it' => 'Struttura Modulare',
                'description_it' => 'Strutture modulari flessibili',
                'name_no' => 'Modulær Struktur',
                'description_no' => 'Fleksible modulære strukturer',
                'name_lt' => 'Modulinė Struktūra',
                'description_lt' => 'Lanksčios modulinės struktūros',
                'name_es' => 'Estructura Modular',
                'description_es' => 'Estructuras modulares flexibles',
                'name_nl' => 'Modulaire Structuur',
                'description_nl' => 'Flexibele modulaire structuren',
                'isActive' => true,
                'sortOrder' => 2
            ],
            [
                'code' => 'prefab-elements',
                'name_fr' => 'Éléments Préfabriqués',
                'description_fr' => 'Éléments de construction préfabriqués',
                'name_en' => 'Prefabricated Elements',
                'description_en' => 'Prefabricated construction elements',
                'name_pt' => 'Elementos Pré-fabricados',
                'description_pt' => 'Elementos de construção pré-fabricados',
                'name_de' => 'Vorgefertigte Elemente',
                'description_de' => 'Vorgefertigte Bauelemente',
                'name_it' => 'Elementi Prefabbricati',
                'description_it' => 'Elementi di costruzione prefabbricati',
                'name_no' => 'Prefab-elementer',
                'description_no' => 'Prefabrikkerte byggelementer',
                'name_lt' => 'Iš anksto Pagaminti Elementai',
                'description_lt' => 'Iš anksto pagaminti statybos elementai',
                'name_es' => 'Elementos Prefabricados',
                'description_es' => 'Elementos de construcción prefabricados',
                'name_nl' => 'Prefab Elementen',
                'description_nl' => 'Prefab bouwelementen',
                'isActive' => true,
                'sortOrder' => 3
            ],
            [
                'code' => 'eco-solutions',
                'name_fr' => 'Solutions Éco',
                'description_fr' => 'Solutions écologiques durables',
                'name_en' => 'Eco Solutions',
                'description_en' => 'Sustainable ecological solutions',
                'name_pt' => 'Soluções Eco',
                'description_pt' => 'Soluções ecológicas sustentáveis',
                'name_de' => 'Eco-Lösungen',
                'description_de' => 'Nachhaltige ökologische Lösungen',
                'name_it' => 'Soluzioni Eco',
                'description_it' => 'Soluzioni ecologiche sostenibili',
                'name_no' => 'Eco-løsninger',
                'description_no' => 'Bærekraftige økologiske løsninger',
                'name_lt' => 'Eko Sprendimai',
                'description_lt' => 'Tvarios ekologiniai sprendimai',
                'name_es' => 'Soluciones Eco',
                'description_es' => 'Soluciones ecológicas sostenibles',
                'name_nl' => 'Eco Oplossingen',
                'description_nl' => 'Duurzame ecologische oplossingen',
                'isActive' => true,
                'sortOrder' => 4
            ],
            [
                'code' => 'tech-equipment',
                'name_fr' => 'Équipements Techniques',
                'description_fr' => 'Équipements technologiques avancés',
                'name_en' => 'Technical Equipment',
                'description_en' => 'Advanced technological equipment',
                'name_pt' => 'Equipamentos Técnicos',
                'description_pt' => 'Equipamentos tecnológicos avançados',
                'name_de' => 'Technische Ausrüstung',
                'description_de' => 'Erweiterte technologische Ausrüstung',
                'name_it' => 'Attrezzature Tecniche',
                'description_it' => 'Attrezzature tecnologiche avanzate',
                'name_no' => 'Teknisk Utstyr',
                'description_no' => 'Avansert teknologisk utstyr',
                'name_lt' => 'Techninė Įranga',
                'description_lt' => 'Išplėsta technologinė įranga',
                'name_es' => 'Equipos Técnicos',
                'description_es' => 'Equipos tecnológicos avanzados',
                'name_nl' => 'Technische Uitrusting',
                'description_nl' => 'Geavanceerde technologische uitrusting',
                'isActive' => true,
                'sortOrder' => 5
            ]
        ];

        $languages = $this->entityManager->getRepository(Language::class)->findAll();

        $createdCategories = 0;

        foreach ($categories as $categoryData) {
            // Créer ou récupérer la catégorie
            $category = $this->entityManager->getRepository(ProductCategory::class)
                ->findOneBy(['code' => $categoryData['code']]);

            if (!$category) {
                $category = new ProductCategory();
                $category->setCode($categoryData['code']);
                $category->setIsActive($categoryData['isActive']);
                $category->setSortOrder($categoryData['sortOrder']);

                $this->entityManager->persist($category);
                $createdCategories++;
            }

            // Créer les traductions pour chaque langue
            foreach ($languages as $language) {
                $code = $language->getCode();
                
                $translation = $this->entityManager->getRepository(ProductCategoryTranslation::class)
                    ->findOneBy([
                        'productCategory' => $category,
                        'language' => $language
                    ]);

                if (!$translation) {
                    $translation = new ProductCategoryTranslation();
                    $translation->setProductCategory($category);
                    $translation->setLanguage($language);
                    $this->entityManager->persist($translation);
                }

                $nameKey = "name_$code";
                $descKey = "description_$code";

                if (isset($categoryData[$nameKey])) {
                    $translation->setName($categoryData[$nameKey]);
                }

                if (isset($categoryData[$descKey])) {
                    $translation->setDescription($categoryData[$descKey]);
                }
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            '%d catégories créées/mises à jour avec succès en %d langues',
            $createdCategories,
            count($languages)
        ));

        return Command::SUCCESS;
    }
}