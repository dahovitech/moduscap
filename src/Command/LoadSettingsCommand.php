<?php

namespace App\Command;

use App\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Commande pour charger les paramètres système
 */
#[AsCommand(
    name: 'app:load-settings',
    description: 'Charge les paramètres système en base de données',
)]
class LoadSettingsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('⚙️  Chargement des paramètres MODUSCAP');

        try {
            // Vérifier si des paramètres existent déjà
            $existingSettings = $this->entityManager->getRepository(Setting::class)->findAll();
            if (!empty($existingSettings)) {
                $io->warning('⚠️  Des paramètres existent déjà en base. Suppression...');
                foreach ($existingSettings as $setting) {
                    $this->entityManager->remove($setting);
                }
                $this->entityManager->flush();
                $io->note('📊 Paramètres existants supprimés');
            }

            // Configuration des paramètres système
            $settingsData = [
                // Paramètres généraux du site
                ['key' => 'site_name', 'value' => 'ModusCap', 'type' => 'string', 'category' => 'general'],
                ['key' => 'site_description', 'value' => 'Solutions innovantes pour l\'habitat modulaire', 'type' => 'string', 'category' => 'general'],
                ['key' => 'site_language', 'value' => 'fr', 'type' => 'string', 'category' => 'general'],
                ['key' => 'default_currency', 'value' => 'EUR', 'type' => 'string', 'category' => 'general'],
                ['key' => 'default_currency_symbol', 'value' => '€', 'type' => 'string', 'category' => 'general'],
                
                // Paramètres de contact
                ['key' => 'contact_email', 'value' => 'contact@moduscap.com', 'type' => 'string', 'category' => 'contact'],
                ['key' => 'phone_number', 'value' => '+33 1 23 45 67 89', 'type' => 'string', 'category' => 'contact'],
                ['key' => 'address', 'value' => '123 Rue de l\'Innovation, 75001 Paris', 'type' => 'string', 'category' => 'contact'],
                ['key' => 'business_hours', 'value' => 'Lundi - Vendredi: 9h00 - 18h00', 'type' => 'string', 'category' => 'contact'],
                
                // Paramètres des produits
                ['key' => 'products_per_page', 'value' => '12', 'type' => 'integer', 'category' => 'products'],
                ['key' => 'enable_product_reviews', 'value' => 'true', 'type' => 'boolean', 'category' => 'products'],
                ['key' => 'product_image_quality', 'value' => 'high', 'type' => 'string', 'category' => 'products'],
                ['key' => 'max_image_size_mb', 'value' => '5', 'type' => 'integer', 'category' => 'products'],
                
                // Paramètres SEO
                ['key' => 'seo_title', 'value' => 'ModusCap - Habitats Modulaires Innovants', 'type' => 'string', 'category' => 'seo'],
                ['key' => 'seo_description', 'value' => 'Découvrez nos solutions d\'habitat modulaire écologique et innovant', 'type' => 'string', 'category' => 'seo'],
                ['key' => 'seo_keywords', 'value' => 'habitat modulaire, maison container, architecture écologique', 'type' => 'string', 'category' => 'seo'],
                
                // Paramètres de performance
                ['key' => 'cache_enabled', 'value' => 'true', 'type' => 'boolean', 'category' => 'performance'],
                ['key' => 'image_optimization', 'value' => 'true', 'type' => 'boolean', 'category' => 'performance'],
                ['key' => 'enable_cdn', 'value' => 'false', 'type' => 'boolean', 'category' => 'performance'],
            ];

            // Configuration de la barre de progression
            $progressBar = new ProgressBar($output, count($settingsData));
            $progressBar->setFormat('  %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
            $progressBar->start();

            $createdSettings = 0;

            foreach ($settingsData as $settingData) {
                $setting = new Setting();
                $setting->setKey($settingData['key'])
                        ->setValue($settingData['value'])
                        ->setType($settingData['type'])
                        ->setCategory($settingData['category']);

                $this->entityManager->persist($setting);
                $createdSettings++;

                $progressBar->advance();
            }

            $this->entityManager->flush();
            $progressBar->finish();
            $output->writeln(''); // Ligne vide après la barre de progression

            $io->success("✅ {$createdSettings} paramètres créés avec succès !");
            
            // Grouper les paramètres par catégorie pour l'affichage
            $settingsByCategory = [];
            foreach ($settingsData as $setting) {
                $settingsByCategory[$setting['category']][] = $setting['key'];
            }

            $io->section('📋 Paramètres créés par catégorie:');
            foreach ($settingsByCategory as $category => $keys) {
                $io->writeln("   <comment>{$category}:</comment>");
                foreach ($keys as $key) {
                    $io->writeln("     • {$key}");
                }
            }

            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('❌ Erreur lors du chargement des paramètres: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}