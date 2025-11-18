<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour corriger la structure SQLite de MODUSCAP
 */
final class Version20251118160441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Correction de la structure SQLite pour les options de produits';
    }

    public function up(Schema $schema): void
    {
        // Créer les tables pour SQLite si elles n'existent pas
        $this->addSql('CREATE TABLE IF NOT EXISTS product_option_groups (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code VARCHAR(255) UNIQUE NOT NULL,
            input_type VARCHAR(50) NOT NULL,
            is_required BOOLEAN NOT NULL DEFAULT 0,
            sort_order INTEGER NOT NULL DEFAULT 0,
            is_active BOOLEAN NOT NULL DEFAULT 1,
            created_at DATETIME,
            updated_at DATETIME
        )');
        
        $this->addSql('CREATE TABLE IF NOT EXISTS product_option_group_translations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_option_group_id INTEGER NOT NULL,
            language_id INTEGER NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            created_at DATETIME,
            updated_at DATETIME,
            UNIQUE(product_option_group_id, language_id)
        )');
        
        $this->addSql('CREATE TABLE IF NOT EXISTS product_options (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code VARCHAR(255) UNIQUE NOT NULL,
            product_option_group_id INTEGER NOT NULL,
            is_active BOOLEAN NOT NULL DEFAULT 1,
            sort_order INTEGER NOT NULL DEFAULT 0,
            price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            created_at DATETIME,
            updated_at DATETIME
        )');
        
        $this->addSql('CREATE TABLE IF NOT EXISTS product_option_translations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_option_id INTEGER NOT NULL,
            language_id INTEGER NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            created_at DATETIME,
            updated_at DATETIME,
            UNIQUE(product_option_id, language_id)
        )');
        
        // Créer les clés étrangères
        $this->addSql('CREATE FOREIGN KEY (product_option_group_translations, product_option_group_id) REFERENCES product_option_groups(id) ON DELETE CASCADE');
        $this->addSql('CREATE FOREIGN KEY (product_option_group_translations, language_id) REFERENCES languages(id)');
        $this->addSql('CREATE FOREIGN KEY (product_options, product_option_group_id) REFERENCES product_option_groups(id) ON DELETE CASCADE');
        $this->addSql('CREATE FOREIGN KEY (product_option_translations, product_option_id) REFERENCES product_options(id) ON DELETE CASCADE');
        $this->addSql('CREATE FOREIGN KEY (product_option_translations, language_id) REFERENCES languages(id)');
    }

    public function down(Schema $schema): void
    {
        // Cette migration n'est pas réversible car elle corrige la structure
        // En production, il faudrait une migration de retour
        $this->abortIf(false, 'Cette migration n\'est pas réversible');
    }
}