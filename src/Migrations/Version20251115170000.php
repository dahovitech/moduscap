<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour supprimer la colonne basePrice de la table product_categories
 * 
 * Cette migration supprime le champ base_price de la table product_categories suite à 
 * la suppression de la redondance entre ProductCategory et Product pour les prix.
 * 
 * Contexte :
 * - Le champ basePrice existait dans both ProductCategory et Product
 * - Cela créait une confusion sur la source de vérité des prix
 * - Décision : utiliser Product.basePrice comme unique source
 * - ProductCategory doit uniquement organiser les produits, pas définir les prix
 * 
 * Impact :
 * - Simplification de l'architecture
 * - Élimination de la redondance des données
 * - Évolution du code : ProductFixtures modifié pour assigner les prix directement aux produits
 * - Templates et formulaires mis à jour pour ne plus référencer category.basePrice
 */
final class Version20251115170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Supprimer la colonne basePrice de product_categories - Éliminer la redondance des prix';
    }

    public function up(Schema $schema): void
    {
        // La table product_categories doit exister
        $this->abortIf(
            !$schema->hasTable('product_categories'),
            'Migration can only be executed if a table named "product_categories" exists'
        );

        // Vérification que la colonne existe avant de la supprimer
        $table = $schema->getTable('product_categories');
        
        if ($table->hasColumn('base_price')) {
            // Suppression de la colonne base_price
            $this->addSql('ALTER TABLE product_categories DROP COLUMN base_price');
            
            // Ajout d'un commentaire explicatif sur l'architecture
            $this->addSql("COMMENT ON TABLE product_categories IS 'Table des catégories de produits - ne contient plus de prix (basePrice supprimé). Les prix sont maintenant définis uniquement dans Product.basePrice.'");
        } else {
            // La colonne n'existe peut-être pas, ce qui est normal si elle a été supprimée différemment
            $this->addSql("COMMENT ON TABLE product_categories IS 'Table des catégories de produits - basePrice non présent (architecture simplifiée). Les prix sont définis dans Product.basePrice.'");
        }
    }

    public function down(Schema $schema): void
    {
        // IMPORTANT: Cette migration est considérée comme "downgrade" irréversible
        // car les données basePrice originales ont été perdues lors de la suppression
        // du champ dans l'entité ProductCategory
        
        $this->addSql('ALTER TABLE product_categories ADD base_price NUMERIC(10, 2) DEFAULT NULL COMMENT \'Colonne rétablie - Architecture simplifiée : utiliser Product.basePrice comme source de vérité\'');
        
        $this->addSql("COMMENT ON TABLE product_categories IS 'Table des catégories de produits - basePrice rétablie pour compatibilité mais REDONDANTE avec Product.basePrice. Préférer utiliser Product.basePrice.'");
        
        $this->addSql("COMMENT ON COLUMN product_categories.base_price IS 'Cette colonne a été rétablie lors du rollback. IMPORTANT : redondante avec product.base_price. Utiliser Product.basePrice comme source de vérité.'");
    }

    /**
     * Validation des prérequis avant la migration
     */
    public function preUp(Schema $schema): void
    {
        // Vérifier que l'entité ProductCategory n'a plus de propriété basePrice
        // (Cette vérification nécessiterait l'analyse du code PHP, non disponible dans la migration)
        
        // Vérifier que ProductFixtures a été mis à jour pour assigner les prix directement
        // Note: Cette vérification devrait être faite manuellement avant d'exécuter la migration
        
        $this->write('INFO: Vérification des prérequis pour la migration basePrice...');
        $this->write('Vérifiez que :');
        $this->write('1. L\'entité ProductCategory n\'a plus de propriété $basePrice');
        $this->write('2. ProductFixtures assigne les prix directement aux produits');
        $this->write('3. Les templates admin ne référencent plus category.basePrice');
        $this->write('4. Les formulaires ProductCategoryType n\'ont plus de champ basePrice');
    }

    /**
     * Validation post-migration
     */
    public function postUp(Schema $schema): void
    {
        // Vérification que la colonne a bien été supprimée
        $table = $schema->getTable('product_categories');
        if (!$table->hasColumn('base_price')) {
            $this->write('SUCCESS: Colonne base_price supprimée avec succès de product_categories');
            $this->write('Architecture simplifiée : Product.basePrice est maintenant la seule source de vérité pour les prix.');
        } else {
            $this->abort('ERROR: La colonne base_price existe toujours dans product_categories');
        }
    }

    /**
     * Validation post-rollback
     */
    public function postDown(Schema $schema): void
    {
        $this->write('WARNING: Migration rollback exécutée');
        $this->write('ATTENTION: La colonne base_price a été rétablie dans product_categories');
        $this->write('IMPORTANT: Cette colonne est REDONDANTE avec product.base_price');
        $this->write('RECOMMANDATION: Re-supprimer cette colonne et utiliser uniquement Product.basePrice');
    }
}