<?php

namespace App\Command;

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
use Symfony\Component\Console\Helper\ProgressBar;
use App\Entity\Language;

/**
 * Commande pour charger les options de produits
 */
#[AsCommand(
    name: 'app:load-product-options',
    description: 'Charge les options et groupes d\'options de produits en base de données',
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
        $io->title('🔧 Chargement des options de produits MODUSCAP');

        try {
            // Vérifier si des options existent déjà
            $existingOptions = $this->entityManager->getRepository(ProductOption::class)->findAll();
            if (!empty($existingOptions)) {
                $io->warning('⚠️  Des options de produits existent déjà en base. Suppression...');
                foreach ($existingOptions as $option) {
                    $this->entityManager->remove($option);
                }
                $this->entityManager->flush();
                $io->note('📊 Options existantes supprimées');
            }

            // Récupérer les langues actives
            $languages = $this->entityManager->getRepository(Language::class)->findBy(['isActive' => true]);
            $defaultLanguage = $this->entityManager->getRepository(Language::class)->findOneBy(['isDefault' => true]);
            
            if (!$defaultLanguage) {
                throw new \Exception('Aucune langue par défaut trouvée');
            }

            // Configuration des groupes d'options et options
            $optionGroupsData = [
                [
                    'code' => 'material',
                    'isRequired' => true,
                    'inputType' => 'select',
                    'isActive' => true,
                    'sortOrder' => 1,
                    'translations' => [
                        'fr' => ['name' => 'Matériau', 'description' => 'Sélectionnez le matériau principal'],
                        'en' => ['name' => 'Material', 'description' => 'Select the main material'],
                    ],
                    'options' => [
                        [
                            'value' => 'wood',
                            'isAvailable' => true,
                            'sortOrder' => 1,
                            'translations' => [
                                'fr' => ['name' => 'Bois', 'description' => 'Bois naturel écologique'],
                                'en' => ['name' => 'Wood', 'description' => 'Natural ecological wood'],
                            ],
                        ],
                        [
                            'value' => 'metal',
                            'isAvailable' => true,
                            'sortOrder' => 2,
                            'translations' => [
                                'fr' => ['name' => 'Métal', 'description' => 'Structure métallique robuste'],
                                'en' => ['name' => 'Metal', 'description' => 'Robust metal structure'],
                            ],
                        ],
                        [
                            'value' => 'concrete',
                            'isAvailable' => true,
                            'sortOrder' => 3,
                            'translations' => [
                                'fr' => ['name' => 'Béton', 'description' => 'Béton armé moderne'],
                                'en' => ['name' => 'Concrete', 'description' => 'Modern reinforced concrete'],
                            ],
                        ],
                    ],
                ],
                [
                    'code' => 'color',
                    'isRequired' => false,
                    'inputType' => 'select',
                    'isActive' => true,
                    'sortOrder' => 2,
                    'translations' => [
                        'fr' => ['name' => 'Couleur', 'description' => 'Choisissez la couleur extérieure'],
                        'en' => ['name' => 'Color', 'description' => 'Choose the exterior color'],
                    ],
                    'options' => [
                        [
                            'value' => 'natural',
                            'isAvailable' => true,
                            'sortOrder' => 1,
                            'translations' => [
                                'fr' => ['name' => 'Naturel', 'description' => 'Couleur naturelle du matériau'],
                                'en' => ['name' => 'Natural', 'description' => 'Natural material color'],
                            ],
                        ],
                        [
                            'value' => 'white',
                            'isAvailable' => true,
                            'sortOrder' => 2,
                            'translations' => [
                                'fr' => ['name' => 'Blanc', 'description' => 'Blanc pur moderne'],
                                'en' => ['name' => 'White', 'description' => 'Modern pure white'],
                            ],
                        ],
                        [
                            'value' => 'gray',
                            'isAvailable' => true,
                            'sortOrder' => 3,
                            'translations' => [
                                'fr' => ['name' => 'Gris', 'description' => 'Gris urbain contemporain'],
                                'en' => ['name' => 'Gray', 'description' => 'Contemporary urban gray'],
                            ],
                        ],
                    ],
                ],
                [
                    'code' => 'accessories',
                    'isRequired' => false,
                    'inputType' => 'multiselect',
                    'isActive' => true,
                    'sortOrder' => 3,
                    'translations' => [
                        'fr' => ['name' => 'Accessoires', 'description' => 'Sélectionnez des accessoires optionnels'],
                        'en' => ['name' => 'Accessories', 'description' => 'Select optional accessories'],
                    ],
                    'options' => [
                        [
                            'value' => 'solar_panels',
                            'isAvailable' => true,
                            'sortOrder' => 1,
                            'translations' => [
                                'fr' => ['name' => 'Panneaux solaires', 'description' => 'Installation de panneaux solaires'],
                                'en' => ['name' => 'Solar panels', 'description' => 'Solar panel installation'],
                            ],
                        ],
                        [
                            'value' => 'smart_home',
                            'isAvailable' => true,
                            'sortOrder' => 2,
                            'translations' => [
                                'fr' => ['name' => 'Domotique', 'description' => 'Système de maison intelligente'],
                                'en' => ['name' => 'Smart home', 'description' => 'Smart home system'],
                            ],
                        ],
                        [
                            'value' => 'vegetable_garden',
                            'isAvailable' => true,
                            'sortOrder' => 3,
                            'translations' => [
                                'fr' => ['name' => 'Jardin potager', 'description' => 'Zone de culture de légumes'],
                                'en' => ['name' => 'Vegetable garden', 'description' => 'Vegetable growing area'],
                            ],
                        ],
                    ],
                ],
            ];

            // Configuration de la barre de progression (groupes + options + traductions)
            $totalItems = 0;
            foreach ($optionGroupsData as $group) {
                $totalItems++; // Pour le groupe lui-même
                $totalItems += count($group['options']); // Pour chaque option
                foreach ($group['options'] as $option) {
                    $totalItems += count($option['translations']); // Pour chaque traduction d'option
                }
                $totalItems += count($group['translations']); // Pour chaque traduction de groupe
            }

            $progressBar = new ProgressBar($output, $totalItems);
            $progressBar->setFormat('  %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
            $progressBar->start();

            $createdGroups = 0;
            $createdOptions = 0;
            $createdTranslations = 0;

            foreach ($optionGroupsData as $groupData) {
                // Créer le groupe d'options
                $group = new ProductOptionGroup();
                $group->setCode($groupData['code'])
                      ->setIsRequired($groupData['isRequired'])
                      ->setInputType($groupData['inputType'])
                      ->setIsActive($groupData['isActive'])
                      ->setSortOrder($groupData['sortOrder']);

                $this->entityManager->persist($group);
                $createdGroups++;

                $progressBar->advance();

                // Créer les traductions du groupe
                foreach ($groupData['translations'] as $langCode => $translations) {
                    $language = $this->findLanguageByCode($languages, $langCode);
                    if ($language) {
                        $translation = new ProductOptionGroupTranslation();
                        $translation->setLanguage($language)
                                    ->setOptionGroup($group)
                                    ->setName($translations['name'])
                                    ->setDescription($translations['description']);

                        $this->entityManager->persist($translation);
                        $createdTranslations++;

                        $progressBar->advance();
                    }
                }

                // Créer les options du groupe
                foreach ($groupData['options'] as $optionData) {
                    $option = new ProductOption();
                    $option->setOptionGroup($group)
                           ->setValue($optionData['value'])
                           ->setIsAvailable($optionData['isAvailable'])
                           ->setSortOrder($optionData['sortOrder']);

                    $this->entityManager->persist($option);
                    $createdOptions++;

                    $progressBar->advance();

                    // Créer les traductions de l'option
                    foreach ($optionData['translations'] as $langCode => $translations) {
                        $language = $this->findLanguageByCode($languages, $langCode);
                        if ($language) {
                            $translation = new ProductOptionTranslation();
                            $translation->setLanguage($language)
                                        ->setOption($option)
                                        ->setName($translations['name'])
                                        ->setDescription($translations['description']);

                            $this->entityManager->persist($translation);
                            $createdTranslations++;

                            $progressBar->advance();
                        }
                    }
                }
            }

            $this->entityManager->flush();
            $progressBar->finish();
            $output->writeln(''); // Ligne vide après la barre de progression

            $io->success("✅ {$createdGroups} groupes d'options, {$createdOptions} options et {$createdTranslations} traductions créés avec succès !");
            
            // Afficher un résumé
            $io->section('📋 Groupes d\'options créés:');
            foreach ($optionGroupsData as $groupData) {
                $required = $groupData['isRequired'] ? ' (Requis)' : ' (Optionnel)';
                $inputType = $groupData['inputType'] === 'multiselect' ? ' [Multiple]' : ' [Unique]';
                $io->writeln("   • {$groupData['translations']['fr']['name']} ({$groupData['code']}){$required}{$inputType}");
            }

            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('❌ Erreur lors du chargement des options de produits: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function findLanguageByCode(array $languages, string $code): ?Language
    {
        foreach ($languages as $language) {
            if ($language->getCode() === $code) {
                return $language;
            }
        }
        return null;
    }
}