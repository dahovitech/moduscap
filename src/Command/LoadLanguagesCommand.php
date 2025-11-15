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

            // Configuration des langues supportées
            $languagesData = [
                [
                    'code' => 'fr',
                    'name' => 'Français',
                    'nativeName' => 'Français',
                    'locale' => 'fr_FR',
                    'direction' => 'ltr',
                    'isDefault' => true,
                    'isActive' => true
                ],
                [
                    'code' => 'en',
                    'name' => 'English',
                    'nativeName' => 'English',
                    'locale' => 'en_US',
                    'direction' => 'ltr',
                    'isDefault' => false,
                    'isActive' => true
                ],
                [
                    'code' => 'es',
                    'name' => 'Español',
                    'nativeName' => 'Español',
                    'locale' => 'es_ES',
                    'direction' => 'ltr',
                    'isDefault' => false,
                    'isActive' => false // Inactive par défaut
                ],
                [
                    'code' => 'de',
                    'name' => 'Deutsch',
                    'nativeName' => 'Deutsch',
                    'locale' => 'de_DE',
                    'direction' => 'ltr',
                    'isDefault' => false,
                    'isActive' => false // Inactive par défaut
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
                         ->setLocale($languageData['locale'])
                         ->setDirection($languageData['direction'])
                         ->setIsDefault($languageData['isDefault'])
                         ->setIsActive($languageData['isActive']);

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
                $io->writeln("   • {$languageData['nativeName']} ({$languageData['code']}) - {$status}{$default}");
            }

            $defaultLanguage = array_filter($languagesData, fn($lang) => $lang['isDefault']);
            $activeLanguages = array_filter($languagesData, fn($lang) => $lang['isActive']);
            
            $io->section('📊 Statistiques:');
            $io->writeln("   • Langue par défaut: " . reset($defaultLanguage)['name']);
            $io->writeln("   • Langues actives: " . count($activeLanguages) . "/" . count($languagesData));

            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('❌ Erreur lors du chargement des langues: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}