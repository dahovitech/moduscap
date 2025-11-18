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
    name: 'app:fix-database',
    description: 'Corrige la structure de la base de données SQLite',
)]
class FixDatabaseCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('🔧 Correction de la structure de base de données MODUSCAP');

        try {
            $connection = $this->entityManager->getConnection();
            
            // Vérifier la plateforme
            $platform = $connection->getDatabasePlatform();
            $io->note("Plateforme de base de données: " . $platform->getName());
            
            // Créer les tables si elles n'existent pas (pour SQLite)
            if ($platform->getName() === 'sqlite') {
                $io->writeln('Création de la structure SQLite...');
                
                // Tables pour SQLite
                $this->createSqliteTables($connection, $io);
                
                // Insérer les langues de base si nécessaire
                $this->ensureLanguages($io);
                
                // Insérer les groupes d'options si nécessaire
                $this->ensureOptionGroups($io);
                
                // Insérer les options si nécessaire
                $this->ensureOptions($io);
            } else {
                $io->note("Structure MySQL/InnoDB détectée, utilisant les migrations Doctrine");
            }
            
            $io->success('✅ Structure de base de données corrigée avec succès');
            $io->note([
                'Vous pouvez maintenant exécuter: php bin/console app:load-product-options',
                'Et pour charger toutes les données: php bin/console app:load-all-data'
            ]);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('❌ Erreur lors de la correction: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    private function createSqliteTables($connection, $io): void
    {
        // Table des groupes d'options
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS product_option_groups (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code VARCHAR(255) UNIQUE NOT NULL,
                input_type VARCHAR(50) NOT NULL,
                is_required BOOLEAN NOT NULL DEFAULT 0,
                sort_order INTEGER NOT NULL DEFAULT 0,
                is_active BOOLEAN NOT NULL DEFAULT 1,
                created_at DATETIME,
                updated_at DATETIME
            )
        ');
        $io->writeln('✓ Table product_option_groups créée/corrigée');
        
        // Table des traductions de groupes
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS product_option_group_translations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_option_group_id INTEGER NOT NULL,
                language_id INTEGER NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                created_at DATETIME,
                updated_at DATETIME,
                UNIQUE(product_option_group_id, language_id)
            )
        ');
        $io->writeln('✓ Table product_option_group_translations créée/corrigée');
        
        // Table des options
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS product_options (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code VARCHAR(255) UNIQUE NOT NULL,
                product_option_group_id INTEGER NOT NULL,
                is_active BOOLEAN NOT NULL DEFAULT 1,
                sort_order INTEGER NOT NULL DEFAULT 0,
                price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                created_at DATETIME,
                updated_at DATETIME
            )
        ');
        $io->writeln('✓ Table product_options créée/corrigée');
        
        // Table des traductions d'options
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS product_option_translations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_option_id INTEGER NOT NULL,
                language_id INTEGER NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                created_at DATETIME,
                updated_at DATETIME,
                UNIQUE(product_option_id, language_id)
            )
        ');
        $io->writeln('✓ Table product_option_translations créée/corrigée');
    }
    
    private function ensureLanguages($io): void
    {
        $languageRepo = $this->entityManager->getRepository(Language::class);
        $languages = [
            ['code' => 'fr', 'name' => 'Français', 'native' => 'Français'],
            ['code' => 'en', 'name' => 'Anglais', 'native' => 'English'],
            ['code' => 'es', 'name' => 'Espagnol', 'native' => 'Español'],
            ['code' => 'de', 'name' => 'Allemand', 'native' => 'Deutsch'],
            ['code' => 'it', 'name' => 'Italien', 'native' => 'Italiano'],
            ['code' => 'pt', 'name' => 'Portugais', 'native' => 'Português'],
            ['code' => 'ar', 'name' => 'Arabe', 'native' => 'العربية'],
            ['code' => 'zh', 'name' => 'Chinois', 'native' => '中文'],
            ['code' => 'ja', 'name' => 'Japonais', 'native' => '日本語']
        ];
        
        $created = 0;
        foreach ($languages as $langData) {
            $language = $languageRepo->findOneBy(['code' => $langData['code']]);
            if (!$language) {
                $language = new Language();
                $language->setCode($langData['code']);
                $language->setName($langData['name']);
                $language->setNativeName($langData['native']);
                $this->entityManager->persist($language);
                $created++;
            }
        }
        
        if ($created > 0) {
            $this->entityManager->flush();
            $io->writeln("✓ $created nouvelles langues ajoutées");
        } else {
            $io->writeln('✓ Toutes les langues sont déjà présentes');
        }
    }
    
    private function ensureOptionGroups($io): void
    {
        $groupRepo = $this->entityManager->getRepository(ProductOptionGroup::class);
        $groups = [
            ['code' => 'bardage', 'inputType' => 'select', 'isRequired' => true, 'sortOrder' => 1],
            ['code' => 'couverture', 'inputType' => 'select', 'isRequired' => true, 'sortOrder' => 2],
            ['code' => 'materiaux', 'inputType' => 'multiselect', 'isRequired' => false, 'sortOrder' => 3],
            ['code' => 'equipements', 'inputType' => 'multiselect', 'isRequired' => false, 'sortOrder' => 4]
        ];
        
        $created = 0;
        foreach ($groups as $groupData) {
            $group = $groupRepo->findOneBy(['code' => $groupData['code']]);
            if (!$group) {
                $group = new ProductOptionGroup();
                $group->setCode($groupData['code']);
                $group->setInputType($groupData['inputType']);
                $group->setIsRequired($groupData['isRequired']);
                $group->setSortOrder($groupData['sortOrder']);
                $group->setIsActive(true);
                $this->entityManager->persist($group);
                $created++;
            }
        }
        
        if ($created > 0) {
            $this->entityManager->flush();
            $io->writeln("✓ $created nouveaux groupes d'options ajoutés");
        } else {
            $io->writeln('✓ Tous les groupes d\'options sont déjà présents');
        }
    }
    
    private function ensureOptions($io): void
    {
        $optionRepo = $this->entityManager->getRepository(ProductOption::class);
        $groupRepo = $this->entityManager->getRepository(ProductOptionGroup::class);
        
        $options = [
            ['groupCode' => 'bardage', 'code' => 'bardage-bois', 'price' => 50.00],
            ['groupCode' => 'bardage', 'code' => 'bardage-metal', 'price' => 40.00],
            ['groupCode' => 'bardage', 'code' => 'bardage-composite', 'price' => 60.00],
            ['groupCode' => 'couverture', 'code' => 'toiture-tuile', 'price' => 80.00],
            ['groupCode' => 'couverture', 'code' => 'toiture-tole', 'price' => 45.00],
            ['groupCode' => 'couverture', 'code' => 'toiture-vegetale', 'price' => 120.00],
            ['groupCode' => 'materiaux', 'code' => 'bois-massif', 'price' => 100.00],
            ['groupCode' => 'materiaux', 'code' => 'bois-moderne', 'price' => 85.00],
            ['groupCode' => 'materiaux', 'code' => 'structure-metal', 'price' => 90.00],
            ['groupCode' => 'equipements', 'code' => 'fenetres-pvc', 'price' => 200.00],
            ['groupCode' => 'equipements', 'code' => 'fenetres-bois', 'price' => 280.00],
            ['groupCode' => 'equipements', 'code' => 'isolation-extra', 'price' => 150.00]
        ];
        
        $created = 0;
        foreach ($options as $optionData) {
            $option = $optionRepo->findOneBy(['code' => $optionData['code']]);
            if (!$option) {
                $group = $groupRepo->findOneBy(['code' => $optionData['groupCode']]);
                if ($group) {
                    $option = new ProductOption();
                    $option->setCode($optionData['code']);
                    $option->setOptionGroup($group);
                    $option->setPrice($optionData['price']);
                    $option->setIsActive(true);
                    $option->setSortOrder(1);
                    $this->entityManager->persist($option);
                    $created++;
                }
            }
        }
        
        if ($created > 0) {
            $this->entityManager->flush();
            $io->writeln("✓ $created nouvelles options ajoutées");
        } else {
            $io->writeln('✓ Toutes les options sont déjà présentes');
        }
    }
}