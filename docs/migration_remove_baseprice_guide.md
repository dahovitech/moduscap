# Migration - Suppression de basePrice de ProductCategory

## 🎯 Objectif de cette Migration

Cette migration supprime la colonne `base_price` de la table `product_categories` pour éliminer la redondance des prix entre les entités ProductCategory et Product.

### Contexte
- **Problème identifié** : Les prix existaient à la fois dans `ProductCategory` et `Product`
- **Conséquence** : Confusion sur la source de vérité des prix
- **Solution** : Conserver uniquement `Product.basePrice` comme source unique

### Impact de l'Architecture
- **Avant** : Ambiguïté sur quel prix utiliser (catégorie vs produit)
- **Après** : Clarification - `Product.basePrice` = unique source de vérité
- **Bénéfice** : Simplification et élimination de la redondance

## 📋 Fichiers de Migration Disponibles

### 1. Migration Doctrine (Recommandée)
- **Fichier** : `src/Migrations/Version20251115170000.php`
- **Avantages** :
  - Intégration native Symfony
  - Rollback possible
  - Validation automatique
  - Logs de migration

### 2. SQL Direct (Alternative)
- **Fichier** : `migration_remove_baseprice.sql`
- **Avantages** :
  - Exécution immédiate
  - Compatible avec tous les SGBD
  - Contrôle total sur la commande

## 🚀 Exécution de la Migration

### Option 1 : Migration Doctrine (Recommandée)

```bash
# 1. Vérifier le statut des migrations
php bin/console doctrine:migrations:status

# 2. Voir le contenu de la migration (optionnel)
php bin/console doctrine:migrations:diff

# 3. Exécuter la migration
php bin/console doctrine:migrations:migrate --no-interaction

# 4. Vérifier le statut après migration
php bin/console doctrine:migrations:status
```

### Option 2 : SQL Direct

```bash
# MySQL / MariaDB
mysql -u username -p database_name < migration_remove_baseprice.sql

# PostgreSQL
psql -U username -d database_name -f migration_remove_baseprice.sql

# Ou via Doctrine DBAL
php bin/console dbal:run-sql "$(cat migration_remove_baseprice.sql)"
```

## ⚠️ Vérifications Pré-Migration

Avant d'exécuter la migration, vérifiez que ces conditions sont remplies :

### 1. Code Mis à Jour
```bash
# Vérifier que ProductCategory n'a plus de propriété basePrice
grep -n "basePrice" src/Entity/ProductCategory.php

# Vérifier que ProductFixtures assigne les prix directement
grep -n "setBasePrice" src/DataFixtures/ProductFixtures.php

# Vérifier que les formulaires n'ont plus le champ basePrice
grep -n "basePrice" src/Form/ProductCategoryType.php
```

### 2. Templates Mis à Jour
```bash
# Vérifier que les templates admin ne référencent plus category.basePrice
grep -r "category\.basePrice" templates/admin/category/
```

### 3. Fixtures Testées
```bash
# Tester le chargement des fixtures
php bin/console doctrine:fixtures:load --no-interaction
```

## ✅ Vérifications Post-Migration

### 1. Vérification de la Structure
```sql
-- MySQL
DESCRIBE product_categories;

-- PostgreSQL
\d product_categories;

-- Via Doctrine
php bin/console doctrine:schema:validate
```

### 2. Vérification des Données
```sql
-- Vérifier qu'aucune donnée n'est perdue
SELECT COUNT(*) as category_count FROM product_categories;

-- Vérifier que les produits ont toujours leurs prix
SELECT p.code, p.base_price FROM products WHERE base_price IS NOT NULL;
```

### 3. Test de l'Application
```bash
# Tester l'interface admin
php bin/console server:start --env=dev

# Vérifier l'accès aux pages catégories
curl http://localhost:8000/admin/category/
```

## 🔄 Rollback (Si Nécessaire)

### Via Doctrine
```bash
# Rollback de la dernière migration
php bin/console doctrine:migrations:migrate prev --no-interaction

# Vérifier le rollback
php bin/console doctrine:migrations:status
```

### Via SQL Direct
```sql
-- Si vous devez rétablir la colonne (non recommandé)
ALTER TABLE product_categories 
ADD COLUMN base_price NUMERIC(10, 2) DEFAULT NULL;
```

## 📊 Impact sur l'Application

### Pages Affectées
- ✅ Interface admin des catégories (plus de champ prix)
- ✅ Liste des catégories (plus d'affichage de prix)
- ✅ Détail d'une catégorie (plus d'affichage de prix)
- ✅ Création/édition de catégorie (plus de champ prix)

### Fonctionnalités Conservées
- ✅ Prix des produits (Product.basePrice)
- ✅ Affichage des prix sur le site public
- ✅ Calculs et totalisations
- ✅ Recherche par prix
- ✅ Filtres par prix

### Changements pour les Utilisateurs
- **Admin** : Plus de gestion des prix au niveau des catégories
- **Visiteurs** : Aucun changement visible
- **Développeurs** : API simplifiée, une seule source de prix

## 🚨 Points d'Attention

### ⚠️ Migration Irréversible
- Cette migration supprime définitivement la colonne `base_price`
- Les données de prix au niveau catégorie sont perdues
- **Solution** : Utiliser uniquement `Product.basePrice`

### ⚠️ Dépendances
- Vérifiez qu'aucun rapport ou analytics ne dépend de `category.base_price`
- Contrôlez les intégrations externes qui pourraient utiliser cette colonne

### ⚠️ Sauvegarde Recommandée
```bash
# Sauvegarde avant migration
mysqldump -u username -p database_name > backup_before_baseprice_removal.sql

# Ou avec Doctrine
php bin/console doctrine:query:sql "SELECT * FROM product_categories" > categories_backup.json
```

## 📈 Bénéfices Attendus

Après cette migration :

1. **🎯 Architecture Clarifiée**
   - Une seule source de vérité pour les prix
   - Élimination de la confusion
   - Code plus maintenable

2. **⚡ Performance Améliorée**
   - Requêtes simplifiées
   - Moins de jointures nécessaires
   - Indexation optimisée

3. **🔧 Maintenance Facilitée**
   - Moins de champs à gérer
   - Validation simplifiée
   - Code plus propre

4. **🛡️ Réduction des Erreurs**
   - Plus de divergence entre prix catégorie/produit
   - Évolution cohérente des prix
   - Moins de bugs liés aux prix

## 🆘 Support en Cas de Problème

### Problème : Colonne Encore Utilisée
```bash
# Identifier les références restantes
grep -r "basePrice\|base_price" src/ templates/ config/
```

### Problème : Erreur de Migration
```bash
# Voir les logs détaillés
php bin/console doctrine:migrations:status --show-versions

# Exécuter en mode verbose
php bin/console doctrine:migrations:migrate --no-interaction -vvv
```

### Problème : Données Manquantes
```bash
# Restaurer depuis la sauvegarde
mysql -u username -p database_name < backup_before_baseprice_removal.sql
```

---

**Note Importante** : Cette migration fait partie d'un ensemble de modifications visant à simplifier l'architecture MODUSCAP. Elle doit être exécutée après avoir mis à jour tout le code associé.