<?php

namespace App\Command;

use App\Entity\Language;
use App\Entity\ProductOption;
use App\Entity\ProductOptionGroup;
use App\Entity\ProductOptionGroupTranslation;
use App\Entity\ProductOptionTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:load-product-options',
    description: 'Charge les options de produits',
)]
class LoadProductOptionsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Chargement des options de produits');

        // Groupes d'options avec traductions dans les 9 langues
        $optionGroups = [
            [
                'code' => 'bardage',
                'name_fr' => 'Bardage',
                'description_fr' => 'Types de bardage extérieur',
                'name_en' => 'Siding',
                'description_en' => 'Exterior siding types',
                'name_es' => 'Revestimiento',
                'description_es' => 'Tipos de revestimiento exterior',
                'name_de' => 'Verkleidung',
                'description_de' => 'Arten von Außenverkleidung',
                'name_it' => 'Rivestimento',
                'description_it' => 'Tipi di rivestimento esterno',
                'name_pt' => 'Revestimento',
                'description_pt' => 'Tipos de revestimento exterior',
                'name_ar' => 'الكسوات',
                'description_ar' => 'أنواع الكسوات الخارجي',
                'name_zh' => '外墙板',
                'description_zh' => '外部外墙板类型',
                'name_ja' => '外装材',
                'description_ja' => '外装材のタイプ',
                'inputType' => 'select',
                'isRequired' => true,
                'sortOrder' => 1,
                'isActive' => true
            ],
            [
                'code' => 'couverture',
                'name_fr' => 'Couverture',
                'description_fr' => 'Types de couverture de toiture',
                'name_en' => 'Roofing',
                'description_en' => 'Roofing cover types',
                'name_es' => 'Cubierta',
                'description_es' => 'Tipos de cubierta de techo',
                'name_de' => 'Bedachung',
                'description_de' => 'Arten von Dachbedeckung',
                'name_it' => 'Copertura',
                'description_it' => 'Tipi di copertura del tetto',
                'name_pt' => 'Cobertura',
                'description_pt' => 'Tipos de cobertura do telhado',
                'name_ar' => 'الطلاء',
                'description_ar' => 'أنواع الطلاء',
                'name_zh' => '屋顶',
                'description_zh' => '屋顶覆盖类型',
                'name_ja' => '屋根',
                'description_ja' => '屋根材のタイプ',
                'inputType' => 'select',
                'isRequired' => true,
                'sortOrder' => 2,
                'isActive' => true
            ],
            [
                'code' => 'materiaux',
                'name_fr' => 'Matériaux',
                'description_fr' => 'Matériaux de construction principaux',
                'name_en' => 'Materials',
                'description_en' => 'Main construction materials',
                'name_es' => 'Materiales',
                'description_es' => 'Materiales principales de construcción',
                'name_de' => 'Materialien',
                'description_de' => 'Hauptbaumaterialien',
                'name_it' => 'Materiali',
                'description_it' => 'Materiali principali di costruzione',
                'name_pt' => 'Materiais',
                'description_pt' => 'Materiais principais de construção',
                'name_ar' => 'المواد',
                'description_ar' => 'المواد الرئيسية للبناء',
                'name_zh' => '材料',
                'description_zh' => '主要建筑材料',
                'name_ja' => '材料',
                'description_ja' => '主要な建築材料',
                'inputType' => 'multiselect',
                'isRequired' => false,
                'sortOrder' => 3,
                'isActive' => true
            ],
            [
                'code' => 'equipements',
                'name_fr' => 'Équipements',
                'description_fr' => 'Équipements et accessoires',
                'name_en' => 'Equipment',
                'description_en' => 'Equipment and accessories',
                'name_es' => 'Equipamiento',
                'description_es' => 'Equipamiento y accesorios',
                'name_de' => 'Ausrüstung',
                'description_de' => 'Ausrüstung und Zubehör',
                'name_it' => 'Attrezzature',
                'description_it' => 'Attrezzature e accessori',
                'name_pt' => 'Equipamento',
                'description_pt' => 'Equipamento e acessórios',
                'name_ar' => 'المعدات',
                'description_ar' => 'المعدات والملحقات',
                'name_zh' => '设备',
                'description_zh' => '设备和配件',
                'name_ja' => '設備',
                'description_ja' => '設備と附件',
                'inputType' => 'multiselect',
                'isRequired' => false,
                'sortOrder' => 4,
                'isActive' => true
            ]
        ];

        // Options individuelles avec traductions dans les 9 langues
        $options = [
            // Bardage
            [
                'groupCode' => 'bardage',
                'code' => 'bardage-bois',
                'name_fr' => 'Bardage Bois',
                'description_fr' => 'Bardage en bois naturel',
                'name_en' => 'Wood Siding',
                'description_en' => 'Natural wood siding',
                'name_es' => 'Revestimiento de Madera',
                'description_es' => 'Revestimiento en madera natural',
                'name_de' => 'Holzverkleidung',
                'description_de' => 'Verkleidung aus natürlichem Holz',
                'name_it' => 'Rivestimento in Legno',
                'description_it' => 'Rivestimento in legno naturale',
                'name_pt' => 'Revestimento de Madeira',
                'description_pt' => 'Revestimento em madeira natural',
                'name_ar' => 'كسوات خشبي',
                'description_ar' => 'كسوات من الخشب الطبيعي',
                'name_zh' => '木材外墙板',
                'description_zh' => '天然木材外墙板',
                'name_ja' => '木材外装材',
                'description_ja' => '天然木材の外壁材',
                'price' => 50.00,
                'isActive' => true,
                'sortOrder' => 1
            ],
            [
                'groupCode' => 'bardage',
                'code' => 'bardage-metal',
                'name_fr' => 'Bardage Métal',
                'description_fr' => 'Bardage en métal',
                'name_en' => 'Metal Siding',
                'description_en' => 'Metal siding',
                'name_es' => 'Revestimiento de Metal',
                'description_es' => 'Revestimiento en metal',
                'name_de' => 'Metallverkleidung',
                'description_de' => 'Verkleidung aus Metall',
                'name_it' => 'Rivestimento in Metallo',
                'description_it' => 'Rivestimento in metallo',
                'name_pt' => 'Revestimento de Metal',
                'description_pt' => 'Revestimento em metal',
                'name_ar' => 'كسوات معدني',
                'description_ar' => 'كسوات من المعدن',
                'name_zh' => '金属外墙板',
                'description_zh' => '金属外墙板',
                'name_ja' => '金属外装材',
                'description_ja' => '金属の外壁材',
                'price' => 40.00,
                'isActive' => true,
                'sortOrder' => 2
            ],
            [
                'groupCode' => 'bardage',
                'code' => 'bardage-composite',
                'name_fr' => 'Bardage Composite',
                'description_fr' => 'Bardage composite moderne',
                'name_en' => 'Composite Siding',
                'description_en' => 'Modern composite siding',
                'name_es' => 'Revestimiento Compuesto',
                'description_es' => 'Revestimiento compuesto moderno',
                'name_de' => 'Composite-Verkleidung',
                'description_de' => 'Moderne Composite-Verkleidung',
                'name_it' => 'Rivestimento Composito',
                'description_it' => 'Rivestimento composito moderno',
                'name_pt' => 'Revestimento Composto',
                'description_pt' => 'Revestimento composto moderno',
                'name_ar' => 'كسوات مركب',
                'description_ar' => 'كسوات مركب حديث',
                'name_zh' => '复合外墙板',
                'description_zh' => '现代复合外墙板',
                'name_ja' => '複合外装材',
                'description_ja' => 'モダンな複合外壁材',
                'price' => 60.00,
                'isActive' => true,
                'sortOrder' => 3
            ],
            // Couverture
            [
                'groupCode' => 'couverture',
                'code' => 'toiture-tuile',
                'name_fr' => 'Toiture Tuile',
                'description_fr' => 'Couverture en tuiles',
                'name_en' => 'Tile Roofing',
                'description_en' => 'Tile roof covering',
                'name_es' => 'Cubierta de Teja',
                'description_es' => 'Cubierta de tejas',
                'name_de' => 'Ziegeldach',
                'description_de' => 'Dachziegel-Eindeckung',
                'name_it' => 'Copertura in Tegole',
                'description_it' => 'Copertura in tegole',
                'name_pt' => 'Cobertura de Telha',
                'description_pt' => 'Cobertura de telhas',
                'name_ar' => 'سقف قرميد',
                'description_ar' => 'سقف من القراميد',
                'name_zh' => '瓦屋顶',
                'description_zh' => '瓦片屋顶',
                'name_ja' => '瓦屋根',
                'description_ja' => '瓦屋根葺き',
                'price' => 80.00,
                'isActive' => true,
                'sortOrder' => 1
            ],
            [
                'groupCode' => 'couverture',
                'code' => 'toiture-tole',
                'name_fr' => 'Toiture Tôle',
                'description_fr' => 'Couverture en tôle',
                'name_en' => 'Sheet Metal Roofing',
                'description_en' => 'Sheet metal roof covering',
                'name_es' => 'Cubierta de Chapa',
                'description_es' => 'Cubierta de chapa metálica',
                'name_de' => 'Blechdach',
                'description_de' => 'Blech-Metall-Dacheindeckung',
                'name_it' => 'Copertura in Lamiera',
                'description_it' => 'Copertura in lamiera metallica',
                'name_pt' => 'Cobertura de Chapa',
                'description_pt' => 'Cobertura de chapa metálica',
                'name_ar' => 'سقف صاج',
                'description_ar' => 'سقف من الصفائح المعدنية',
                'name_zh' => '金属板屋顶',
                'description_zh' => '金属板屋顶',
                'name_ja' => '鋼板屋根',
                'description_ja' => '鋼板屋根葺き',
                'price' => 45.00,
                'isActive' => true,
                'sortOrder' => 2
            ],
            [
                'groupCode' => 'couverture',
                'code' => 'toiture-vegetale',
                'name_fr' => 'Toiture Végétale',
                'description_fr' => 'Couverture végétale écologique',
                'name_en' => 'Green Roofing',
                'description_en' => 'Ecological green roof covering',
                'name_es' => 'Cubierta Vegetal',
                'description_es' => 'Cubierta vegetal ecológica',
                'name_de' => 'Gründach',
                'description_de' => 'Ökologische Gründach-Eindeckung',
                'name_it' => 'Copertura Vegetale',
                'description_it' => 'Copertura vegetale ecologica',
                'name_pt' => 'Cobertura Vegetal',
                'description_pt' => 'Cobertura vegetal ecológica',
                'name_ar' => 'سقف نباتي',
                'description_ar' => 'سقف نباتي بيئي',
                'name_zh' => '绿色屋顶',
                'description_zh' => '生态绿色屋顶',
                'name_ja' => '緑の屋根',
                'description_ja' => 'エコな緑の屋根葺き',
                'price' => 120.00,
                'isActive' => true,
                'sortOrder' => 3
            ],
            // Matériaux
            [
                'groupCode' => 'materiaux',
                'code' => 'bois-massif',
                'name_fr' => 'Bois Massif',
                'description_fr' => 'Construction en bois massif',
                'name_en' => 'Solid Wood',
                'description_en' => 'Solid wood construction',
                'name_es' => 'Madera Maciza',
                'description_es' => 'Construcción en madera maciza',
                'name_de' => 'Vollholz',
                'description_de' => 'Massivholz-Konstruktion',
                'name_it' => 'Legno Massiccio',
                'description_it' => 'Costruzione in legno massiccio',
                'name_pt' => 'Madeira Maciça',
                'description_pt' => 'Construção em madeira maciça',
                'name_ar' => 'خشب صلب',
                'description_ar' => 'بناء بالخشب الصلب',
                'name_zh' => '实木',
                'description_zh' => '实木建筑',
                'name_ja' => '一枚木',
                'description_ja' => '一枚木の建築',
                'price' => 100.00,
                'isActive' => true,
                'sortOrder' => 1
            ],
            [
                'groupCode' => 'materiaux',
                'code' => 'bois-moderne',
                'name_fr' => 'Bois Moderne',
                'description_fr' => 'Construction en bois moderne traité',
                'name_en' => 'Modern Wood',
                'description_en' => 'Modern treated wood construction',
                'name_es' => 'Madera Moderna',
                'description_es' => 'Construcción en madera moderna tratada',
                'name_de' => 'Modernes Holz',
                'description_de' => 'Moderne behandelte Holzkonstruktion',
                'name_it' => 'Legno Moderno',
                'description_it' => 'Costruzione in legno moderno trattato',
                'name_pt' => 'Madeira Moderna',
                'description_pt' => 'Construção em madeira moderna tratada',
                'name_ar' => 'خشب حديث',
                'description_ar' => 'بناء بالخشب الحديث المعالج',
                'name_zh' => '现代木材',
                'description_zh' => '现代处理木材建筑',
                'name_ja' => 'モダン木材',
                'description_ja' => 'モダン木材の建築',
                'price' => 85.00,
                'isActive' => true,
                'sortOrder' => 2
            ],
            [
                'groupCode' => 'materiaux',
                'code' => 'structure-metal',
                'name_fr' => 'Structure Métal',
                'description_fr' => 'Construction en structure métallique',
                'name_en' => 'Metal Structure',
                'description_en' => 'Metal structure construction',
                'name_es' => 'Estructura Metálica',
                'description_es' => 'Construcción en estructura metálica',
                'name_de' => 'Metallstruktur',
                'description_de' => 'Metallstruktur-Konstruktion',
                'name_it' => 'Struttura Metallica',
                'description_it' => 'Costruzione in struttura metallica',
                'name_pt' => 'Estrutura Metálica',
                'description_pt' => 'Construção em estrutura metálica',
                'name_ar' => 'هيكل معدني',
                'description_ar' => 'بناء بالهيكل المعدني',
                'name_zh' => '金属结构',
                'description_zh' => '金属结构建筑',
                'name_ja' => '金属構造',
                'description_ja' => '金属構造建築',
                'price' => 90.00,
                'isActive' => true,
                'sortOrder' => 3
            ],
            // Équipements
            [
                'groupCode' => 'equipements',
                'code' => 'fenetres-pvc',
                'name_fr' => 'Fenêtres PVC',
                'description_fr' => 'Fenêtres en PVC double vitrage',
                'name_en' => 'PVC Windows',
                'description_en' => 'Double glazed PVC windows',
                'name_es' => 'Ventanas PVC',
                'description_es' => 'Ventanas de PVC con doble acristalamiento',
                'name_de' => 'PVC-Fenster',
                'description_de' => 'PVC-Fenster mit Doppelverglasung',
                'name_it' => 'Finestre PVC',
                'description_it' => 'Finestre in PVC a doppio vetro',
                'name_pt' => 'Janelas PVC',
                'description_pt' => 'Janelas de PVC com vidro duplo',
                'name_ar' => 'نوافذ PVC',
                'description_ar' => 'نوافذ PVC بزجاج مزدوج',
                'name_zh' => 'PVC窗户',
                'description_zh' => '双层玻璃PVC窗户',
                'name_ja' => 'PVC窓',
                'description_ja' => '複層ガラスPVC窓',
                'price' => 200.00,
                'isActive' => true,
                'sortOrder' => 1
            ],
            [
                'groupCode' => 'equipements',
                'code' => 'fenetres-bois',
                'name_fr' => 'Fenêtres Bois',
                'description_fr' => 'Fenêtres en bois de qualité',
                'name_en' => 'Wood Windows',
                'description_en' => 'Quality wood windows',
                'name_es' => 'Ventanas de Madera',
                'description_es' => 'Ventanas de madera de calidad',
                'name_de' => 'Holzfenster',
                'description_de' => 'Qualität-Holzfenster',
                'name_it' => 'Finestre in Legno',
                'description_it' => 'Finestre in legno di qualità',
                'name_pt' => 'Janelas de Madeira',
                'description_pt' => 'Janelas de madeira de qualidade',
                'name_ar' => 'نوافذ خشبية',
                'description_ar' => 'نوافذ خشبية عالية الجودة',
                'name_zh' => '木窗',
                'description_zh' => '优质木窗',
                'name_ja' => '木製の窓',
                'description_ja' => '高品質木製窓',
                'price' => 280.00,
                'isActive' => true,
                'sortOrder' => 2
            ],
            [
                'groupCode' => 'equipements',
                'code' => 'isolation-extra',
                'name_fr' => 'Isolation Extra',
                'description_fr' => 'Isolation thermique renforcée',
                'name_en' => 'Extra Insulation',
                'description_en' => 'Enhanced thermal insulation',
                'name_es' => 'Aislamiento Extra',
                'description_es' => 'Aislamiento térmico mejorado',
                'name_de' => 'Extra-Isolierung',
                'description_de' => 'Verstärkte Wärmeisolierung',
                'name_it' => 'Isolamento Extra',
                'description_it' => 'Isolamento termico migliorato',
                'name_pt' => 'Isolamento Extra',
                'description_pt' => 'Isolamento térmico melhorado',
                'name_ar' => 'عزل إضافي',
                'description_ar' => 'عزل حراري محسن',
                'name_zh' => '额外保温',
                'description_zh' => '增强的热保温',
                'name_ja' => '追加断熱',
                'description_ja' => '強化された熱断熱',
                'price' => 150.00,
                'isActive' => true,
                'sortOrder' => 3
            ]
        ];

        $languages = $this->entityManager->getRepository(Language::class)->findAll();
        
        if (empty($languages)) {
            $io->error('Aucune langue trouvée. Assurez-vous que les langues sont chargées avant les options.');
            return Command::FAILURE;
        }
        
        $createdGroups = 0;
        $createdOptions = 0;
        $updatedTranslations = 0;

        // Créer les groupes d'options
        foreach ($optionGroups as $groupData) {
            $group = $this->entityManager->getRepository(ProductOptionGroup::class)
                ->findOneBy(['code' => $groupData['code']]);

            if (!$group) {
                $group = new ProductOptionGroup();
                $group->setCode($groupData['code']);
                $group->setInputType($groupData['inputType']);
                $group->setIsRequired($groupData['isRequired']);
                $group->setSortOrder($groupData['sortOrder']);
                $group->setIsActive($groupData['isActive']);

                $this->entityManager->persist($group);
                $createdGroups++;
                $io->writeln("✓ Groupe d'options créé: {$groupData['code']}");
            }

            // Créer les traductions pour chaque langue
            foreach ($languages as $language) {
                $code = $language->getCode();
                
                $nameKey = "name_$code";
                $descKey = "description_$code";
                
                // Vérifier qu'il y a des données de traduction (langue demandée OU français par défaut)
                $hasTranslation = false;
                $translationName = null;
                $translationDesc = null;
                
                if (isset($groupData[$nameKey]) && !empty(trim($groupData[$nameKey]))) {
                    $hasTranslation = true;
                    $translationName = $groupData[$nameKey];
                    $translationDesc = isset($groupData[$descKey]) && !empty(trim($groupData[$descKey])) ? $groupData[$descKey] : null;
                } elseif (isset($groupData['name_fr']) && !empty(trim($groupData['name_fr']))) {
                    // Fallback vers le français
                    $hasTranslation = true;
                    $translationName = $groupData['name_fr'];
                    $translationDesc = isset($groupData['description_fr']) && !empty(trim($groupData['description_fr'])) ? $groupData['description_fr'] : null;
                }
                
                // Ne créer la traduction que si on a des données valides
                if (!$hasTranslation) {
                    continue;
                }

                // CORRECTION: Utiliser le bon nom de propriété dans findOneBy
                $translation = $this->entityManager->getRepository(ProductOptionGroupTranslation::class)
                    ->findOneBy([
                        'optionGroup' => $group,
                        'language' => $language
                    ]);

                if (!$translation) {
                    $translation = new ProductOptionGroupTranslation();
                    $translation->setOptionGroup($group);
                    $translation->setLanguage($language);
                    $this->entityManager->persist($translation);
                    $updatedTranslations++;
                    $io->writeln("  ✓ Traduction créée pour {$language->getCode()}: {$translationName}");
                }

                // Assigner les traductions avec validation
                $translation->setName($translationName);
                
                if ($translationDesc !== null) {
                    $translation->setDescription($translationDesc);
                }
            }
        }

        // Créer les options individuelles
        $groupMap = [];
        $existingGroups = $this->entityManager->getRepository(ProductOptionGroup::class)->findAll();
        foreach ($existingGroups as $group) {
            $groupMap[$group->getCode()] = $group;
        }

        foreach ($options as $optionData) {
            if (!isset($groupMap[$optionData['groupCode']])) {
                $io->warning("Groupe d'options '{$optionData['groupCode']}' non trouvé");
                continue;
            }

            $group = $groupMap[$optionData['groupCode']];
            $option = $this->entityManager->getRepository(ProductOption::class)
                ->findOneBy(['code' => $optionData['code']]);

            if (!$option) {
                $option = new ProductOption();
                $option->setCode($optionData['code']);
                $option->setOptionGroup($group);
                $option->setIsActive($optionData['isActive']);
                $option->setSortOrder($optionData['sortOrder']);
                $option->setPrice($optionData['price']);

                $this->entityManager->persist($option);
                $createdOptions++;
                $io->writeln("✓ Option créée: {$optionData['code']}");
            }

            // Créer les traductions pour chaque langue
            foreach ($languages as $language) {
                $code = $language->getCode();
                
                $nameKey = "name_$code";
                $descKey = "description_$code";
                
                // Vérifier qu'il y a des données de traduction (langue demandée OU français par défaut)
                $hasTranslation = false;
                $translationName = null;
                $translationDesc = null;
                
                if (isset($optionData[$nameKey]) && !empty(trim($optionData[$nameKey]))) {
                    $hasTranslation = true;
                    $translationName = $optionData[$nameKey];
                    $translationDesc = isset($optionData[$descKey]) && !empty(trim($optionData[$descKey])) ? $optionData[$descKey] : null;
                } elseif (isset($optionData['name_fr']) && !empty(trim($optionData['name_fr']))) {
                    // Fallback vers le français
                    $hasTranslation = true;
                    $translationName = $optionData['name_fr'];
                    $translationDesc = isset($optionData['description_fr']) && !empty(trim($optionData['description_fr'])) ? $optionData['description_fr'] : null;
                }
                
                // Ne créer la traduction que si on a des données valides
                if (!$hasTranslation) {
                    continue;
                }

                $translation = $this->entityManager->getRepository(ProductOptionTranslation::class)
                    ->findOneBy([
                        'option' => $option,
                        'language' => $language
                    ]);

                if (!$translation) {
                    $translation = new ProductOptionTranslation();
                    $translation->setOption($option);
                    $translation->setLanguage($language);
                    $this->entityManager->persist($translation);
                    $updatedTranslations++;
                    $io->writeln("  ✓ Traduction option créée pour {$language->getCode()}: {$translationName}");
                }

                // Assigner les traductions avec validation
                $translation->setName($translationName);
                
                if ($translationDesc !== null) {
                    $translation->setDescription($translationDesc);
                }
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            '%d groupes d\'options, %d options et %d traductions créées/mises à jour avec succès dans les %d langues supportées (fr, en, es, de, it, pt, ar, zh, ja)',
            $createdGroups,
            $createdOptions,
            $updatedTranslations,
            count($languages)
        ));

        return Command::SUCCESS;
    }
}