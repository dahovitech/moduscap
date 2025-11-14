<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour refactoriser la logique de traduction des produits
 * 
 * Cette migration :
 * 1. Ajoute les colonnes specifications et advantages à product_translations
 * 2. Migre les données existantes depuis products vers product_translations  
 * 3. Supprime les colonnes redondantes de products
 * 
 * Cette refactorisation permet d'avoir une architecture cohérente où tous 
 * les contenus marketing/produit sont traduits, séparés des données techniques.
 */
final class Version20251114223015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Refactor Product multilingual content - migrate materials, equipment, specifications, advantages to ProductTranslation entity for consistent multilingual architecture';
    }

    public function up(Schema $schema): void
    {
        // Étape 1: Ajouter les nouvelles colonnes dans product_translations
        $this->addSql('ALTER TABLE product_translations ADD specifications TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_translations ADD advantages TEXT DEFAULT NULL');
        
        // Étape 2: Migrer les données existantes
        // Pour chaque produit, copier les champs depuis products vers product_translations
        
        // Migration de materials vers materialsDetail (existant)
        $this->addSql("
            UPDATE product_translations pt 
            INNER JOIN products p ON pt.product_id = p.id 
            SET pt.materials_detail = p.materials 
            WHERE p.materials IS NOT NULL AND pt.materials_detail IS NULL
        ");
        
        // Migration de equipment vers equipmentDetail (existant)
        $this->addSql("
            UPDATE product_translations pt 
            INNER JOIN products p ON pt.product_id = p.id 
            SET pt.equipment_detail = p.equipment 
            WHERE p.equipment IS NOT NULL AND pt.equipment_detail IS NULL
        ");
        
        // Migration de specifications vers specifications (nouveau)
        $this->addSql("
            UPDATE product_translations pt 
            INNER JOIN products p ON pt.product_id = p.id 
            SET pt.specifications = p.specifications 
            WHERE p.specifications IS NOT NULL AND pt.specifications IS NULL
        ");
        
        // Migration de advantages vers advantages (nouveau)
        $this->addSql("
            UPDATE product_translations pt 
            INNER JOIN products p ON pt.product_id = p.id 
            SET pt.advantages = p.advantages 
            WHERE p.advantages IS NOT NULL AND pt.advantages IS NULL
        ");
        
        // Étape 3: Supprimer les colonnes redondantes de products
        $this->addSql('ALTER TABLE products DROP materials');
        $this->addSql('ALTER TABLE products DROP equipment');
        $this->addSql('ALTER TABLE products DROP specifications');
        $this->addSql('ALTER TABLE products DROP advantages');
    }

    public function down(Schema $schema): void
    {
        // Cette opération est complexe car nous devons:
        // 1. Recréer les colonnes dans products
        // 2. Migrer les données depuis product_translations vers products
        // 3. Supprimer les colonnes de product_translations
        
        // Étape 1: Recréer les colonnes dans products
        $this->addSql('ALTER TABLE products ADD materials TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE products ADD equipment TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE products ADD specifications TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE products ADD advantages TEXT DEFAULT NULL');
        
        // Étape 2: Récupérer les données depuis product_translations (une seule langue par produit)
        // Pour matériaux
        $this->addSql("
            INSERT INTO products (id, materials) 
            SELECT DISTINCT p.id, pt.materials_detail
            FROM products p
            INNER JOIN product_translations pt ON p.id = pt.product_id
            WHERE pt.materials_detail IS NOT NULL
            ON DUPLICATE KEY UPDATE materials = VALUES(materials)
        ");
        
        // Pour equipment
        $this->addSql("
            INSERT INTO products (id, equipment) 
            SELECT DISTINCT p.id, pt.equipment_detail
            FROM products p
            INNER JOIN product_translations pt ON p.id = pt.product_id
            WHERE pt.equipment_detail IS NOT NULL
            ON DUPLICATE KEY UPDATE equipment = VALUES(equipment)
        ");
        
        // Pour specifications
        $this->addSql("
            INSERT INTO products (id, specifications) 
            SELECT DISTINCT p.id, pt.specifications
            FROM products p
            INNER JOIN product_translations pt ON p.id = pt.product_id
            WHERE pt.specifications IS NOT NULL
            ON DUPLICATE KEY UPDATE specifications = VALUES(specifications)
        ");
        
        // Pour advantages  
        $this->addSql("
            INSERT INTO products (id, advantages) 
            SELECT DISTINCT p.id, pt.advantages
            FROM products p
            INNER JOIN product_translations pt ON p.id = pt.product_id
            WHERE pt.advantages IS NOT NULL
            ON DUPLICATE KEY UPDATE advantages = VALUES(advantages)
        ");
        
        // Étape 3: Supprimer les colonnes de product_translations
        $this->addSql('ALTER TABLE product_translations DROP specifications');
        $this->addSql('ALTER TABLE product_translations DROP advantages');
    }
}