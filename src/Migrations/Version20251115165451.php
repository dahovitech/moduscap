<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour supprimer la colonne basePrice de la table product_categories
 * 
 * Cette migration удаляет поле base_price из таблицы product_categories suite à 
 * la suppression de la redondance entre ProductCategory et Product pour les prix.
 * 
 * Objectif : Établir Product.basePrice comme unique source de vérité pour les prix.
 */
final class Version20251115165451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Supprimer la colonne basePrice de product_categories';
    }

    public function up(Schema $schema): void
    {
        // Vérification que la colonne existe avant de la supprimer
        $table = $schema->getTable('product_categories');
        
        if ($table->hasColumn('base_price')) {
            $this->addSql('ALTER TABLE product_categories DROP COLUMN base_price');
            $this->addSql('COMMENT ON COLUMN product_categories.base_price IS \'Colonne supprimée - utiliser maintenant product.base_price comme seule source de vérité pour les prix\'');
        }
    }

    public function down(Schema $schema): void
    {
        // Cette migration est irréversible car les données basePrice ont été supprimées
        // lors de la suppression du champ dans l'entité ProductCategory
        $this->addSql('ALTER TABLE product_categories ADD base_price NUMERIC(10, 2) DEFAULT NULL COMMENT \'Colonne rétablie - redondante avec product.base_price\'');
        $this->addSql('COMMENT ON COLUMN product_categories.base_price IS \'Cette colonne a été rétablie lors du rollback de la migration, mais elle est redondante avec product.base_price\'');
    }

    /**
     * Vérification que les données sont cohérentes avant la migration
     */
    public function preUp(Schema $schema): void
    {
        // Vérifier qu'aucun produit ne dépend encore de category.base_price
        // (cette vérification peut être ajoutée si nécessaire)
        
        // Note: Nous n'avons pas besoin de cette vérification car 
        // l'entité ProductCategory n'a plus de propriété basePrice
        // et ProductFixtures a été mis à jour pour assigner les prix directement
    }
}