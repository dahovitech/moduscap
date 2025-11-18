<?php

namespace App\Command;

use App\Entity\Language;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\ProductTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:load-products',
    description: 'Charge les produits',
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
        $io->title('Chargement des produits');

        // Récupérer toutes les catégories
        $categories = $this->entityManager->getRepository(ProductCategory::class)->findAll();
        $categoryMap = [];
        foreach ($categories as $category) {
            $categoryMap[$category->getCode()] = $category;
        }

        // Données des produits par catégorie
        $productsByCategory = [
            'capsule-house' => [
                [
                    'variant' => 'standard',
                    'name_fr' => 'Capsule Maison Standard',
                    'description_fr' => 'Maison capsule standard avec 30m² de surface habitable',
                    'name_en' => 'Standard Capsule House',
                    'description_en' => 'Standard capsule house with 30m² of living space',
                    'name_pt' => 'Casa Cápsula Standard',
                    'description_pt' => 'Casa cápsula standard com 30m² de área habitável',
                    'name_de' => 'Standard Kapselhaus',
                    'description_de' => 'Standard Kapselhaus mit 30m² Wohnfläche',
                    'name_it' => 'Casa Capsula Standard',
                    'description_it' => 'Casa capsule standard con 30m² di spazio abitativo',
                    'name_no' => 'Standard Kapselhus',
                    'description_no' => 'Standard kapselhus med 30m² boligareal',
                    'name_lt' => 'Standartinė Kapsulės Namas',
                    'description_lt' => 'Standartinis kapsulinis namas su 30m² gyvenamojo ploto',
                    'name_es' => 'Casa Cápsula Estándar',
                    'description_es' => 'Casa cápsula estándar con 30m² de espacio habitable',
                    'name_nl' => 'Standaard Capsule Huis',
                    'description_nl' => 'Standaard capsule huis met 30m² woonoppervlakte',
                    'price' => 45000.00,
                    'isActive' => true,
                    'sortOrder' => 1
                ],
                [
                    'variant' => 'premium',
                    'name_fr' => 'Capsule Maison Premium',
                    'description_fr' => 'Maison capsule premium avec 45m² et finitions de luxe',
                    'name_en' => 'Premium Capsule House',
                    'description_en' => 'Premium capsule house with 45m² and luxury finishes',
                    'name_pt' => 'Casa Cápsula Premium',
                    'description_pt' => 'Casa cápsula premium com 45m² e acabamentos de luxo',
                    'name_de' => 'Premium Kapselhaus',
                    'description_de' => 'Premium Kapselhaus mit 45m² und Luxusausstattung',
                    'name_it' => 'Casa Capsula Premium',
                    'description_it' => 'Casa capsule premium con 45m² e finiture di lusso',
                    'name_no' => 'Premium Kapselhus',
                    'description_no' => 'Premium kapselhus med 45m² og luksusfinish',
                    'name_lt' => 'Premium Kapsulės Namas',
                    'description_lt' => 'Premium kapsulinis namas su 45m² ir prabangūs apdailos darbai',
                    'name_es' => 'Casa Cápsula Premium',
                    'description_es' => 'Casa cápsula premium con 45m² y acabados de lujo',
                    'name_nl' => 'Premium Capsule Huis',
                    'description_nl' => 'Premium capsule huis met 45m² en luxe afwerking',
                    'price' => 65000.00,
                    'isActive' => true,
                    'sortOrder' => 2
                ],
                [
                    'variant' => 'compact',
                    'name_fr' => 'Capsule Maison Compact',
                    'description_fr' => 'Maison capsule compacte parfaite pour les petits espaces',
                    'name_en' => 'Compact Capsule House',
                    'description_en' => 'Compact capsule house perfect for small spaces',
                    'name_pt' => 'Casa Cápsula Compacta',
                    'description_pt' => 'Casa cápsula compacta perfeita para espaços pequenos',
                    'name_de' => 'Kompaktes Kapselhaus',
                    'description_de' => 'Kompaktes Kapselhaus perfekt für kleine Räume',
                    'name_it' => 'Casa Capsula Compatta',
                    'description_it' => 'Casa capsule compatta perfetta per spazi piccoli',
                    'name_no' => 'Kompakt Kapselhus',
                    'description_no' => 'Kompakt kapselhus perfekt for små rom',
                    'name_lt' => 'Kompaktiškas Kapsulės Namas',
                    'description_lt' => 'Kompaktiškas kapsulinis namas idealus mažiems erdvėms',
                    'name_es' => 'Casa Cápsula Compacta',
                    'description_es' => 'Casa cápsula compacta perfecta para espacios pequeños',
                    'name_nl' => 'Compact Capsule Huis',
                    'description_nl' => 'Compact capsule huis perfect voor kleine ruimtes',
                    'price' => 35000.00,
                    'isActive' => true,
                    'sortOrder' => 3
                ]
            ],
            'module-structure' => [
                [
                    'variant' => 'basic',
                    'name_fr' => 'Structure Modulaire Basique',
                    'description_fr' => 'Structure modulaire de base extensible',
                    'name_en' => 'Basic Modular Structure',
                    'description_en' => 'Extensible basic modular structure',
                    'name_pt' => 'Estrutura Modular Básica',
                    'description_pt' => 'Estrutura modular básica extensível',
                    'name_de' => 'Basis Modulare Struktur',
                    'description_de' => 'Erweiterbare grundlegende modulare Struktur',
                    'name_it' => 'Struttura Modulare Base',
                    'description_it' => 'Struttura modulare base estensibile',
                    'name_no' => 'Grunnleggende Modulær Struktur',
                    'description_no' => 'Utvidbar grunnleggende modulær struktur',
                    'name_lt' => 'Pagrindinė Modulinė Struktūra',
                    'description_lt' => 'Išplėstinė pagrindinė modulinė struktūra',
                    'name_es' => 'Estructura Modular Básica',
                    'description_es' => 'Estructura modular básica extensible',
                    'name_nl' => 'Basis Modulaire Structuur',
                    'description_nl' => 'Uitbreidbare basis modulaire structuur',
                    'price' => 25000.00,
                    'isActive' => true,
                    'sortOrder' => 1
                ],
                [
                    'variant' => 'advanced',
                    'name_fr' => 'Structure Modulaire Avancée',
                    'description_fr' => 'Structure modulaire avancée avec systèmes intégrés',
                    'name_en' => 'Advanced Modular Structure',
                    'description_en' => 'Advanced modular structure with integrated systems',
                    'name_pt' => 'Estrutura Modular Avançada',
                    'description_pt' => 'Estrutura modular avançada com sistemas integrados',
                    'name_de' => 'Erweiterte Modulare Struktur',
                    'description_de' => 'Erweiterte modulare Struktur mit integrierten Systemen',
                    'name_it' => 'Struttura Modulare Avanzata',
                    'description_it' => 'Struttura modulare avanzata con sistemi integrati',
                    'name_no' => 'Avansert Modulær Struktur',
                    'description_no' => 'Avansert modulær struktur med integrerte systemer',
                    'name_lt' => 'Pažangi Modulinė Struktūra',
                    'description_lt' => 'Pažangi modulinė struktūra su integruotais sistemomis',
                    'name_es' => 'Estructura Modular Avanzada',
                    'description_es' => 'Estructura modular avanzada con sistemas integrados',
                    'name_nl' => 'Geavanceerde Modulaire Structuur',
                    'description_nl' => 'Geavanceerde modulaire structuur met geïntegreerde systemen',
                    'price' => 40000.00,
                    'isActive' => true,
                    'sortOrder' => 2
                ]
            ],
            'prefab-elements' => [
                [
                    'variant' => 'wall-panels',
                    'name_fr' => 'Panneaux Murs Préfabriqués',
                    'description_fr' => 'Panneaux muraux préfabriqués isolés',
                    'name_en' => 'Prefab Wall Panels',
                    'description_en' => 'Insulated prefabricated wall panels',
                    'name_pt' => 'Painéis de Parede Pré-fabricados',
                    'description_pt' => 'Painéis de parede pré-fabricados isolados',
                    'name_de' => 'Vorgefertigte Wandpaneele',
                    'description_de' => 'Isolierte vorgefertigte Wandpaneele',
                    'name_it' => 'Pannelli Parete Prefabbricati',
                    'description_it' => 'Pannelli parete prefabbricati isolati',
                    'name_no' => 'Prefabrikkerte Veggpaneler',
                    'description_no' => 'Isolerte prefabrikkerte vegpaneler',
                    'name_lt' => 'Iš anksto Pagaminti Sienų Paneliai',
                    'description_lt' => 'Izoliuoti iš anksto pagaminti sienų paneliai',
                    'name_es' => 'Paneles de Pared Prefabricados',
                    'description_es' => 'Paneles de pared prefabricados aislados',
                    'name_nl' => 'Prefab Wandpanelen',
                    'description_nl' => 'Geïsoleerde prefab wandpanelen',
                    'price' => 150.00,
                    'isActive' => true,
                    'sortOrder' => 1
                ],
                [
                    'variant' => 'roof-elements',
                    'name_fr' => 'Éléments Toiture Préfabriqués',
                    'description_fr' => 'Éléments de toiture préfabriqués étanches',
                    'name_en' => 'Prefab Roof Elements',
                    'description_en' => 'Watertight prefabricated roof elements',
                    'name_pt' => 'Elementos de Telhado Pré-fabricados',
                    'description_pt' => 'Elementos de telhado pré-fabricados à prova d\'água',
                    'name_de' => 'Vorgefertigte Dachelemente',
                    'description_de' => 'Wasserdichte vorgefertigte Dachelemente',
                    'name_it' => 'Elementi Tetto Prefabbricati',
                    'description_it' => 'Elementi tetto prefabbricati impermeabili',
                    'name_no' => 'Prefabrikkerte Takelementer',
                    'description_no' => 'Vanntette prefabrikkerte takelementer',
                    'name_lt' => 'Iš anksto Pagaminti Stogo Elementai',
                    'description_lt' => 'Vandeniški iš anksto pagaminti stogo elementai',
                    'name_es' => 'Elementos de Techo Prefabricados',
                    'description_es' => 'Elementos de techo prefabricados impermeables',
                    'name_nl' => 'Prefab Dakelementen',
                    'description_nl' => 'Waterdichte prefab dakelementen',
                    'price' => 200.00,
                    'isActive' => true,
                    'sortOrder' => 2
                ]
            ],
            'eco-solutions' => [
                [
                    'variant' => 'solar-panels',
                    'name_fr' => 'Panneaux Solaires Intégrés',
                    'description_fr' => 'Système de panneaux solaires intégrés haute efficacité',
                    'name_en' => 'Integrated Solar Panels',
                    'description_en' => 'High efficiency integrated solar panel system',
                    'name_pt' => 'Painéis Solares Integrados',
                    'description_pt' => 'Sistema de painéis solares integrados de alta eficiência',
                    'name_de' => 'Integrierte Solarpaneele',
                    'description_de' => 'Hochleistungs-integriertes Solarpaneelsystem',
                    'name_it' => 'Pannelli Solari Integrati',
                    'description_it' => 'Sistema di pannelli solari integrati ad alta efficienza',
                    'name_no' => 'Integrerte Solcellepaneler',
                    'description_no' => 'Høyeffektivt integrert solcellepanelsystem',
                    'name_lt' => 'Integruoti Saulės Elementai',
                    'description_lt' => 'Aukšto efektyvumo integruota saulės elementų sistema',
                    'name_es' => 'Paneles Solares Integrados',
                    'description_es' => 'Sistema de paneles solares integrados de alta eficiencia',
                    'name_nl' => 'Geïntegreerde Zonnepanelen',
                    'description_nl' => 'Hoog rendement geïntegreerd zonnepaneelsysteem',
                    'price' => 8000.00,
                    'isActive' => true,
                    'sortOrder' => 1
                ],
                [
                    'variant' => 'water-recycling',
                    'name_fr' => 'Système Récupération Eau',
                    'description_fr' => 'Système de récupération et traitement des eaux grises',
                    'name_en' => 'Water Recycling System',
                    'description_en' => 'Grey water recovery and treatment system',
                    'name_pt' => 'Sistema de Reciclagem de Água',
                    'description_pt' => 'Sistema de recuperação e tratamento de águas cinzentas',
                    'name_de' => 'Wasserrückgewinnungssystem',
                    'description_de' => 'Grauwasser-Rückgewinnungs- und Aufbereitungssystem',
                    'name_it' => 'Sistema di Riciclo Acqua',
                    'description_it' => 'Sistema di recupero e trattamento acque grigie',
                    'name_no' => 'Vannresirkuleringssystem',
                    'description_no' => 'System for gjenvinning og behandling av gråvann',
                    'name_lt' => 'Vandens Recycling Sistema',
                    'description_lt' => 'Pilkųjų vandenų surinkimo ir apdorojimo sistema',
                    'name_es' => 'Sistema de Reciclaje de Agua',
                    'description_es' => 'Sistema de recuperación y tratamiento de aguas grises',
                    'name_nl' => 'Water Recyclesysteem',
                    'description_nl' => 'Grijswater-terugwinning en behandelingssysteem',
                    'price' => 3500.00,
                    'isActive' => true,
                    'sortOrder' => 2
                ]
            ],
            'tech-equipment' => [
                [
                    'variant' => 'smart-home',
                    'name_fr' => 'Système Domotique Complet',
                    'description_fr' => 'Système domotique complet avec IA intégrée',
                    'name_en' => 'Complete Smart Home System',
                    'description_en' => 'Complete smart home system with integrated AI',
                    'name_pt' => 'Sistema de Casa Inteligente Completo',
                    'description_pt' => 'Sistema de casa inteligente completo com IA integrada',
                    'name_de' => 'Vollständiges Smart Home System',
                    'description_de' => 'Vollständiges Smart Home System mit integrierter KI',
                    'name_it' => 'Sistema Casa Intelligente Completo',
                    'description_it' => 'Sistema casa intelligente completo con IA integrata',
                    'name_no' => 'Komplett Smart Hjem System',
                    'description_no' => 'Komplett smart hjemmesystem med integrert AI',
                    'name_lt' => 'Pilnai Protingo Namo Sistema',
                    'description_lt' => 'Pilna protingo namo sistema su integruotu AI',
                    'name_es' => 'Sistema de Hogar Inteligente Completo',
                    'description_es' => 'Sistema de hogar inteligente completo con IA integrada',
                    'name_nl' => 'Compleet Slim Huis Systeem',
                    'description_nl' => 'Compleet slim huissysteem met geïntegreerde AI',
                    'price' => 5500.00,
                    'isActive' => true,
                    'sortOrder' => 1
                ],
                [
                    'variant' => 'security-system',
                    'name_fr' => 'Système Sécurité Avancé',
                    'description_fr' => 'Système de sécurité avec surveillance 24/7',
                    'name_en' => 'Advanced Security System',
                    'description_en' => 'Security system with 24/7 surveillance',
                    'name_pt' => 'Sistema de Segurança Avançado',
                    'description_pt' => 'Sistema de segurança com vigilância 24/7',
                    'name_de' => 'Erweiterte Sicherheitssystem',
                    'description_de' => 'Sicherheitssystem mit 24/7 Überwachung',
                    'name_it' => 'Sistema di Sicurezza Avanzato',
                    'description_it' => 'Sistema di sicurezza con sorveglianza 24/7',
                    'name_no' => 'Avansert Sikkerhetssystem',
                    'description_no' => 'Sikkerhetssystem med 24/7 overvåking',
                    'name_lt' => 'Pažangi Apsaugos Sistema',
                    'description_lt' => 'Apsaugos sistema su 24/7 stebėjimu',
                    'name_es' => 'Sistema de Seguridad Avanzado',
                    'description_es' => 'Sistema de seguridad con vigilancia 24/7',
                    'name_nl' => 'Geavanceerd Beveiligingssysteem',
                    'description_nl' => 'Beveiligingssysteem met 24/7 surveillance',
                    'price' => 2800.00,
                    'isActive' => true,
                    'sortOrder' => 2
                ]
            ]
        ];

        $languages = $this->entityManager->getRepository(Language::class)->findAll();
        $createdProducts = 0;

        foreach ($productsByCategory as $categoryCode => $products) {
            if (!isset($categoryMap[$categoryCode])) {
                $io->warning("Catégorie '$categoryCode' non trouvée");
                continue;
            }

            $category = $categoryMap[$categoryCode];

            foreach ($products as $productData) {
                $productCode = $categoryCode . '-' . $productData['variant'];

                // Créer ou récupérer le produit
                $product = $this->entityManager->getRepository(Product::class)
                    ->findOneBy(['code' => $productCode]);

                if (!$product) {
                    $product = new Product();
                    $product->setCode($productCode);
                    $product->setCategory($category);
                    $product->setIsActive($productData['isActive']);
                    $product->setSortOrder($productData['sortOrder']);
                    $product->setBasePrice((string)$productData['price']);

                    $this->entityManager->persist($product);
                    $createdProducts++;
                }

                // Créer les traductions pour chaque langue
                foreach ($languages as $language) {
                    $code = $language->getCode();
                    
                    $translation = $this->entityManager->getRepository(ProductTranslation::class)
                        ->findOneBy([
                            'product' => $product,
                            'language' => $language
                        ]);

                    if (!$translation) {
                        $translation = new ProductTranslation();
                        $translation->setProduct($product);
                        $translation->setLanguage($language);
                        $this->entityManager->persist($translation);
                    }

                    $nameKey = "name_$code";
                    $descKey = "description_$code";

                    if (isset($productData[$nameKey])) {
                        $translation->setName($productData[$nameKey]);
                    }

                    if (isset($productData[$descKey])) {
                        $translation->setDescription($productData[$descKey]);
                    }
                }
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            '%d produits créés/mis à jour avec succès en %d langues',
            $createdProducts,
            count($languages)
        ));

        return Command::SUCCESS;
    }
}