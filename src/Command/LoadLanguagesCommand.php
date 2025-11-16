<?php

namespace App\Command;

use App\Entity\Language;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Commande pour charger les langues système
 */
#[AsCommand(
    name: 'app:load-languages',
    description: 'Charge les langues supportées en base de données',
)]
class LoadLanguagesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('🌐 Chargement des langues MODUSCAP');

        try {
            // Vérifier si des langues existent déjà
            $existingLanguages = $this->entityManager->getRepository(Language::class)->findAll();
            if (!empty($existingLanguages)) {
                $io->warning('⚠️  Des langues existent déjà en base. Suppression...');
                foreach ($existingLanguages as $language) {
                    $this->entityManager->remove($language);
                }
                $this->entityManager->flush();
                $io->note('📊 Langues existantes supprimées');
            }

            // Configuration des langues supportées (utilisant la vraie structure de l'entité Language)
            $languagesData = [
                [
                    'code' => 'fr',
                    'name' => 'Française',
                    'nativeName' => 'Français',
                    'isDefault' => true,
                    'isActive' => true,
                    'sortOrder' => 1
                ],
                [
                    'code' => 'en',
                    'name' => 'Anglais',
                    'nativeName' => 'English',
                    'isDefault' => true,
                    'isActive' => true,
                    'sortOrder' => 2
                ],
                [
                    'code' => 'pt',
                    'name' => 'Portugaise',
                    'nativeName' => 'Português',
                    'isDefault' => false,
                    'isActive' => true,
                    'sortOrder' => 3
                ],
                [
                    'code' => 'de',
                    'name' => 'Allemande',
                    'nativeName' => 'Deutsch',
                    'isDefault' => false,
                    'isActive' => true,
                    'sortOrder' => 4
                ],
                [
                    'code' => 'it',
                    'name' => 'Italienne',
                    'nativeName' => 'Italiano',
                    'isDefault' => false,
                    'isActive' => true,
                    'sortOrder' => 5
                ],
                [
                    'code' => 'no',
                    'name' => 'Norvégienne',
                    'nativeName' => 'Norsk',
                    'isDefault' => false,
                    'isActive' => true,
                    'sortOrder' => 6
                ],
                [
                    'code' => 'lt',
                    'name' => 'Lithuanienne',
                    'nativeName' => 'Lietuvių',
                    'isDefault' => false,
                    'isActive' => true,
                    'sortOrder' => 7
                ],
                [
                    'code' => 'es',
                    'name' => 'Espagnole',
                    'nativeName' => 'Español',
                    'isDefault' => false,
                    'isActive' => true,
                    'sortOrder' => 8
                ],
                [
                    'code' => 'nl',
                    'name' => 'Néerlandaise',
                    'nativeName' => 'Nederlands',
                    'isDefault' => false,
                    'isActive' => true,
                    'sortOrder' => 9
                ]
            ];

            // Configuration de la barre de progression
            $progressBar = new ProgressBar($output, count($languagesData));
            $progressBar->setFormat('  %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
            $progressBar->start();

            $createdLanguages = 0;

            foreach ($languagesData as $languageData) {
                $language = new Language();
                $language->setCode($languageData['code'])
                         ->setName($languageData['name'])
                         ->setNativeName($languageData['nativeName'])
                         ->setIsDefault($languageData['isDefault'])
                         ->setIsActive($languageData['isActive'])
                         ->setSortOrder($languageData['sortOrder']);

                $this->entityManager->persist($language);
                $createdLanguages++;

                $progressBar->advance();
            }

            $this->entityManager->flush();
            $progressBar->finish();
            $output->writeln(''); // Ligne vide après la barre de progression

            $io->success("✅ {$createdLanguages} langues créées avec succès !");
            
            // Afficher un résumé
            $io->section('📋 Langues créées:');
            foreach ($languagesData as $languageData) {
                $status = $languageData['isActive'] ? '✅ Active' : '❌ Inactive';
                $default = $languageData['isDefault'] ? ' (Défaut)' : '';
                $sortOrder = $languageData['sortOrder'];
                $io->writeln("   • [{$sortOrder}] {$languageData['nativeName']} ({$languageData['code']}) - {$status}{$default}");
            }

            $defaultLanguage = array_filter($languagesData, fn($lang) => $lang['isDefault']);
            $activeLanguages = array_filter($languagesData, fn($lang) => $lang['isActive']);
            
            $io->section('📊 Statistiques:');
            $io->writeln("   • Langue par défaut: " . reset($defaultLanguage)['name'] . " (" . reset($defaultLanguage)['code'] . ")");
            $io->writeln("   • Langues actives: " . count($activeLanguages) . "/" . count($languagesData));

            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('❌ Erreur lors du chargement des langues: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}