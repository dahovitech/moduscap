<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour créer la structure de la base de données MODUSCAP
 * 
 * Créée automatiquement pour synchroniser les entités avec la base de données
 */
final class Version20251114163849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la structure complète de la base de données MODUSCAP avec toutes les tables, relations et contraintes';
    }

    public function up(Schema $schema): void
    {
        // Table des langues
        $this->addSql('CREATE TABLE languages (
            id INTEGER AUTO_INCREMENT NOT NULL, 
            code VARCHAR(10) NOT NULL, 
            name VARCHAR(100) NOT NULL, 
            native_name VARCHAR(100) NOT NULL, 
            is_active BOOLEAN NOT NULL, 
            is_default BOOLEAN NOT NULL, 
            sort_order INTEGER NOT NULL, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D4A6D6C9F5C97E3F ON languages (code)');
        
        // Table des utilisateurs
        $this->addSql('CREATE TABLE users (
            id INTEGER AUTO_INCREMENT NOT NULL, 
            email VARCHAR(180) NOT NULL, 
            password VARCHAR(255) NOT NULL, 
            first_name VARCHAR(100) DEFAULT NULL, 
            last_name VARCHAR(100) DEFAULT NULL, 
            is_active BOOLEAN NOT NULL, 
            last_login_at DATETIME DEFAULT NULL, 
            created_at DATETIME DEFAULT NULL, 
            updated_at DATETIME DEFAULT NULL, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        
        // Table des médias
        $this->addSql('CREATE TABLE media (
            id INTEGER AUTO_INCREMENT NOT NULL, 
            filename VARCHAR(255) NOT NULL, 
            original_filename VARCHAR(255) NOT NULL, 
            mime_type VARCHAR(100) NOT NULL, 
            file_size INTEGER NOT NULL, 
            alt_text VARCHAR(255) DEFAULT NULL, 
            caption TEXT DEFAULT NULL, 
            width INTEGER DEFAULT NULL, 
            height INTEGER DEFAULT NULL, 
            path VARCHAR(500) NOT NULL, 
            is_active BOOLEAN NOT NULL, 
            created_at DATETIME DEFAULT NULL, 
            updated_at DATETIME DEFAULT NULL, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Table des paramètres
        $this->addSql('CREATE TABLE settings (
            id INTEGER AUTO_INCREMENT NOT NULL, 
            category VARCHAR(100) NOT NULL, 
            key_name VARCHAR(100) NOT NULL, 
            value TEXT DEFAULT NULL, 
            description TEXT DEFAULT NULL, 
            data_type VARCHAR(20) NOT NULL DEFAULT \'string\', 
            is_translatable BOOLEAN NOT NULL DEFAULT 0, 
            is_public BOOLEAN NOT NULL DEFAULT 0, 
            sort_order INTEGER DEFAULT 0, 
            created_at DATETIME DEFAULT NULL, 
            updated_at DATETIME DEFAULT NULL, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E19D9AD2F5C97E3F ON settings (key_name)');
        $this->addSql('CREATE INDEX IDX_E19D9AD24B0973C7 ON settings (category)');
        
        // Table des catégories de produits
        $this->addSql('CREATE TABLE product_categories (
            id INTEGER AUTO_INCREMENT NOT NULL, 
            code VARCHAR(100) NOT NULL, 
            base_price NUMERIC(10, 2) DEFAULT NULL, 
            is_active BOOLEAN NOT NULL, 
            is_featured BOOLEAN NOT NULL, 
            sort_order INTEGER NOT NULL, 
            created_at DATETIME DEFAULT NULL, 
            updated_at DATETIME DEFAULT NULL, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A73AF8BD5F5C97E3F ON product_categories (code)');
        
        // Table des traductions de catégories de produits
        $this->addSql('CREATE TABLE product_category_translations (
            id INTEGER AUTO_INCREMENT NOT NULL, 
            product_category_id INTEGER NOT NULL, 
            language_id INTEGER NOT NULL, 
            name VARCHAR(255) NOT NULL, 
            description TEXT DEFAULT NULL, 
            short_description TEXT DEFAULT NULL, 
            seo_title VARCHAR(255) DEFAULT NULL, 
            seo_description TEXT DEFAULT NULL, 
            seo_keywords VARCHAR(500) DEFAULT NULL, 
            created_at DATETIME DEFAULT NULL, 
            updated_at DATETIME DEFAULT NULL, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B68F52B3F5C97E3F35C4F9EF ON product_category_translations (product_category_id, language_id)');
        $this->addSql('CREATE INDEX IDX_B68F52B33C6E7B87 ON product_category_translations (language_id)');
        
        // Table des produits
        $this->addSql('CREATE TABLE products (
            id INTEGER AUTO_INCREMENT NOT NULL, 
            category_id INTEGER NOT NULL, 
            code VARCHAR(100) NOT NULL, 
            base_price NUMERIC(10, 2) DEFAULT NULL, 
            surface NUMERIC(10, 2) DEFAULT NULL, 
            dimensions VARCHAR(100) DEFAULT NULL, 
            rooms INTEGER DEFAULT NULL, 
            height INTEGER DEFAULT NULL, 
            materials TEXT DEFAULT NULL, 
            equipment TEXT DEFAULT NULL, 
            specifications TEXT DEFAULT NULL, 
            advantages TEXT DEFAULT NULL, 
            technical_specs TEXT DEFAULT NULL, 
            assembly_time INTEGER DEFAULT NULL, 
            energy_class VARCHAR(20) DEFAULT NULL, 
            warranty_structure INTEGER DEFAULT NULL, 
            warranty_equipment INTEGER DEFAULT NULL, 
            is_active BOOLEAN NOT NULL, 
            is_featured BOOLEAN NOT NULL, 
            is_customizable BOOLEAN NOT NULL, 
            sort_order INTEGER NOT NULL, 
            created_at DATETIME DEFAULT NULL, 
            updated_at DATETIME DEFAULT NULL, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B6BD307F5F5C97E3F ON products (code)');
        $this->addSql('CREATE INDEX IDX_B6BD307F12469DE2 ON products (category_id)');
        
        // Table des traductions de produits
        $this->addSql('CREATE TABLE product_translations (
            id INTEGER AUTO_INCREMENT NOT NULL, 
            product_id INTEGER NOT NULL, 
            language_id INTEGER NOT NULL, 
            name VARCHAR(255) NOT NULL, 
            description TEXT DEFAULT NULL, 
            concept TEXT DEFAULT NULL, 
            short_description TEXT DEFAULT NULL, 
            materials_detail TEXT DEFAULT NULL, 
            equipment_detail TEXT DEFAULT NULL, 
            performance_details TEXT DEFAULT NULL, 
            created_at DATETIME DEFAULT NULL, 
            updated_at DATETIME DEFAULT NULL, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8F5C6C93C6E7B87 ON product_translations (product_id, language_id)');
        $this->addSql('CREATE INDEX IDX_8F5C6C93C6E7B8735C4F9EF ON product_translations (language_id)');
        
        // Table des options de produits
        $this->addSql('CREATE TABLE product_options (
            id INTEGER AUTO_INCREMENT NOT NULL, 
            code VARCHAR(100) NOT NULL, 
            option_type VARCHAR(50) NOT NULL, 
            price_modifier NUMERIC(10, 2) DEFAULT NULL, 
            is_required BOOLEAN NOT NULL DEFAULT 0, 
            sort_order INTEGER DEFAULT 0, 
            is_active BOOLEAN NOT NULL, 
            created_at DATETIME DEFAULT NULL, 
            updated_at DATETIME DEFAULT NULL, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5C73E9DFF5C97E3F ON product_options (code)');
        
        // Table des traductions d\'options de produits
        $this->addSql('CREATE TABLE product_option_translations (
            id INTEGER AUTO_INCREMENT NOT NULL, 
            product_option_id INTEGER NOT NULL, 
            language_id INTEGER NOT NULL, 
            name VARCHAR(255) NOT NULL, 
            description TEXT DEFAULT NULL, 
            created_at DATETIME DEFAULT NULL, 
            updated_at DATETIME DEFAULT NULL, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B1C52A09F5C97E3F ON product_option_translations (product_option_id, language_id)');
        $this->addSql('CREATE INDEX IDX_B1C52A0935C4F9EF ON product_option_translations (language_id)');
        
        // Table des groupes d'options de produits
        $this->addSql('CREATE TABLE product_option_groups (
            id INTEGER AUTO_INCREMENT NOT NULL, 
            code VARCHAR(100) NOT NULL, 
            min_select INTEGER DEFAULT 1, 
            max_select INTEGER DEFAULT 1, 
            is_required BOOLEAN NOT NULL DEFAULT 0, 
            sort_order INTEGER DEFAULT 0, 
            is_active BOOLEAN NOT NULL, 
            created_at DATETIME DEFAULT NULL, 
            updated_at DATETIME DEFAULT NULL, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8F5C6C93F5C97E3F ON product_option_groups (code)');
        
        // Table des traductions de groupes d'options
        $this->addSql('CREATE TABLE product_option_group_translations (
            id INTEGER AUTO_INCREMENT NOT NULL, 
            product_option_group_id INTEGER NOT NULL, 
            language_id INTEGER NOT NULL, 
            name VARCHAR(255) NOT NULL, 
            description TEXT DEFAULT NULL, 
            created_at DATETIME DEFAULT NULL, 
            updated_at DATETIME DEFAULT NULL, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7A73E0A9F5C97E3F ON product_option_group_translations (product_option_group_id, language_id)');
        $this->addSql('CREATE INDEX IDX_7A73E0A935C4F9EF ON product_option_group_translations (language_id)');
        
        // Table des associations options-groupes
        $this->addSql('CREATE TABLE product_option_group_options (
            product_option_group_id INTEGER NOT NULL, 
            product_option_id INTEGER NOT NULL, 
            PRIMARY KEY(product_option_group_id, product_option_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Table des médias de produits
        $this->addSql('CREATE TABLE product_media (
            id INTEGER AUTO_INCREMENT NOT NULL, 
            product_id INTEGER NOT NULL, 
            media_id INTEGER NOT NULL, 
            media_type VARCHAR(50) NOT NULL DEFAULT \'gallery\', 
            is_main_image BOOLEAN NOT NULL DEFAULT 0, 
            sort_order INTEGER DEFAULT 0, 
            created_at DATETIME DEFAULT NULL, 
            updated_at DATETIME DEFAULT NULL, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2F87ACD1F5C97E3F ON product_media (media_id)');
        $this->addSql('CREATE INDEX IDX_2F87ACD14584665A ON product_media (product_id)');
        $this->addSql('CREATE INDEX IDX_2F87ACD1460D4F2E ON product_media (media_type)');
        
        // Table des options disponibles pour les produits
        $this->addSql('CREATE TABLE product_available_options (
            product_id INTEGER NOT NULL, 
            product_option_id INTEGER NOT NULL, 
            PRIMARY KEY(product_id, product_option_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Table des options sélectionnées pour les produits
        $this->addSql('CREATE TABLE product_selected_options (
            product_id INTEGER NOT NULL, 
            product_option_id INTEGER NOT NULL, 
            PRIMARY KEY(product_id, product_option_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Clés étrangères
        $this->addSql('ALTER TABLE product_category_translations ADD CONSTRAINT FK_B68F52B3F5C97E3F FOREIGN KEY (product_category_id) REFERENCES product_categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_category_translations ADD CONSTRAINT FK_B68F52B33C6E7B87 FOREIGN KEY (language_id) REFERENCES languages (id)');
        
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B6BD307F12469DE2 FOREIGN KEY (category_id) REFERENCES product_categories (id)');
        
        $this->addSql('ALTER TABLE product_translations ADD CONSTRAINT FK_8F5C6C93C6E7B87 FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_translations ADD CONSTRAINT FK_8F5C6C93C6E7B8735C4F9EF FOREIGN KEY (language_id) REFERENCES languages (id)');
        
        $this->addSql('ALTER TABLE product_option_translations ADD CONSTRAINT FK_B1C52A09F5C97E3F FOREIGN KEY (product_option_id) REFERENCES product_options (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_option_translations ADD CONSTRAINT FK_B1C52A0935C4F9EF FOREIGN KEY (language_id) REFERENCES languages (id)');
        
        $this->addSql('ALTER TABLE product_option_group_translations ADD CONSTRAINT FK_7A73E0A9F5C97E3F FOREIGN KEY (product_option_group_id) REFERENCES product_option_groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_option_group_translations ADD CONSTRAINT FK_7A73E0A935C4F9EF FOREIGN KEY (language_id) REFERENCES languages (id)');
        
        $this->addSql('ALTER TABLE product_option_group_options ADD CONSTRAINT FK_5C73E9DFF5C97E3F FOREIGN KEY (product_option_group_id) REFERENCES product_option_groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_option_group_options ADD CONSTRAINT FK_5C73E9DF35C4F9EF FOREIGN KEY (product_option_id) REFERENCES product_options (id) ON DELETE CASCADE');
        
        $this->addSql('ALTER TABLE product_media ADD CONSTRAINT FK_2F87ACD14584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_media ADD CONSTRAINT FK_2F87ACD1F5C97E3F FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
        
        $this->addSql('ALTER TABLE product_available_options ADD CONSTRAINT FK_5A5C1C934584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_available_options ADD CONSTRAINT FK_5A5C1C9335C4F9EF FOREIGN KEY (product_option_id) REFERENCES product_options (id) ON DELETE CASCADE');
        
        $this->addSql('ALTER TABLE product_selected_options ADD CONSTRAINT FK_7C7E7C934584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_selected_options ADD CONSTRAINT FK_7C7E7C9335C4F9EF FOREIGN KEY (product_option_id) REFERENCES product_options (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Supprimer toutes les tables dans l'ordre inverse des dépendances
        $this->addSql('DROP TABLE product_selected_options');
        $this->addSql('DROP TABLE product_available_options');
        $this->addSql('DROP TABLE product_media');
        $this->addSql('DROP TABLE product_option_group_options');
        $this->addSql('DROP TABLE product_option_group_translations');
        $this->addSql('DROP TABLE product_option_groups');
        $this->addSql('DROP TABLE product_option_translations');
        $this->addSql('DROP TABLE product_options');
        $this->addSql('DROP TABLE product_translations');
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE product_category_translations');
        $this->addSql('DROP TABLE product_categories');
        $this->addSql('DROP TABLE settings');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE languages');
    }
}