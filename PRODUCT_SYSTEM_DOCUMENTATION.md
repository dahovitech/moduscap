# 🏠 Système de Gestion des Produits MODUSCAP

## Vue d'Ensemble

Le système de gestion des produits MODUSCAP a été entièrement conçu pour gérer les maisons en capsule préconçues avec support multilingue complet et système d'options personnalisables.

## 📋 Architecture des Entités

### 1. Entité Product (Produit Principal)
- **Fonction** : Représente chaque maison en capsule
- **Attributs clés** : Code unique, prix de base, surface, dimensions, nombre de pièces
- **Spécifications** : Matériaux, équipements, avantages, performances énergétiques
- **Statuts** : Actif, mis en avant, personnalisable
- **Relations** : Catégorie, options disponibles, traductions, médias

### 2. Entité ProductCategory (Catégorie)
- **Fonction** : Organise les produits par modèles
- **Catégories créées** :
  - Capsule House (38 000 €)
  - Apple Cabin (45 000 €)
  - Natural House (48 000 €)
  - Dome House (52 000 €)
  - Model Double (68 000 €)

### 3. Système de Traductions Multilingues
- **ProductTranslation** : Contenu multilingue des produits
- **ProductCategoryTranslation** : Contenu multilingue des catégories
- **ProductOptionGroupTranslation** : Contenu multilingue des groupes d'options
- **ProductOptionTranslation** : Contenu multilingue des options
- **Langues supportées** : Français (défaut), Anglais

### 4. Système d'Options Personnalisables
- **ProductOptionGroup** : Groupes d'options (bardage, couverture, équipements)
- **ProductOption** : Options individuelles avec prix
- **Types d'input** : Select, Radio, Checkbox, Multiselect

### 5. Gestion des Médias
- **ProductMedia** : Relation many-to-many entre produits et médias
- **Types de médias** : Image principale, galerie, images techniques, lifestyle
- **Support** : Classification et ordre d'affichage

## 🎯 Fonctionnalités Implémentées

### Système Multilingue
```php
// Accès aux traductions
$product->getTranslation('fr'); // Traduction française
$product->getName('fr'); // Nom en français
$product->getName('en'); // Nom en anglais

// Gestion automatique des langues
$language = $entityManager->getRepository(Language::class)->findBy(['code' => 'fr']);
```

### Gestion des Médias
```php
// Image principale
$mainImage = $product->getMainImage();

// Galerie d'images
$gallery = $product->getGalleryImages();

// Images techniques
$technical = $product->getTechnicalImages();
```

### Options Personnalisables
```php
// Options disponibles pour un produit
$availableOptions = $product->getAvailableOptions();

// Options sélectionnées
$selectedOptions = $product->getOptions();

// Groupes d'options actifs
$optionGroups = $optionGroupRepository->findActiveOptionGroups('fr');
```

## 🚀 Utilisation

### 1. Installation et Configuration

```bash
# Mettre à jour la base de données
php bin/console doctrine:schema:update --force

# Charger les données de test
php bin/console doctrine:fixtures:load
```

### 2. Requêtes Avancées

#### Produits par Catégorie
```php
$products = $productRepository->findByCategory($category, 'fr');
```

#### Produits en Mise en Avant
```php
$featuredProducts = $productRepository->findFeaturedProducts('fr');
```

#### Produits Personnalisables
```php
$customizableProducts = $productRepository->findCustomizableProducts('fr');
```

#### Recherche par Fourchette de Prix
```php
$products = $productRepository->findByPriceRange(35000, 50000, 'fr');
```

### 3. Administration

#### Formulaires Symfony Disponibles
- `ProductType` : Gestion complète des produits
- `ProductCategoryType` : Gestion des catégories
- `ProductOptionType` : Gestion des options
- `ProductOptionGroupType` : Gestion des groupes d'options

#### Contrôleurs Suggérés
```php
// Admin/ProductController.php
// - CRUD complet des produits
// - Gestion des traductions
// - Upload d'images
// - Configuration des options
```

## 📊 Données Incluses

### Catégories de Produits
1. **Capsule House** - 38 000 € - 28 m² - 1 pièce
2. **Apple Cabin** - 45 000 € - 35 m² - 2 pièces - Classe énergétique B
3. **Natural House** - 48 000 € - 38 m² - 2 pièces - Classe énergétique A+
4. **Dome House** - 52 000 € - 42 m² - 3 pièces - Classe énergétique A+
5. **Model Double** - 68 000 € - 62 m² - 4 pièces - 2 niveaux - Classe énergétique A

### Options Disponibles
- **Bardage** : Original, Terre (+2500€), Aluminium (+1800€)
- **Couverture** : Tuiles terre cuite, Végétale (+3200€), Étanchéité renforcée (+1500€)
- **Équipements** : Climatisation (+3500€), Domotique (+2800€), Cheminée insert (+4200€)

## 🔧 Configuration Requise

### Base de Données
```sql
-- Tables créées automatiquement via migrations
- product_categories
- product_category_translations
- products
- product_translations
- product_option_groups
- product_option_group_translations
- product_options
- product_option_translations
- product_media
```

### Configuration Symfony
```yaml
# config/packages/stof_doctrine_extensions.yaml
stof_doctrine_extensions:
    default_locale: fr
```

## 📁 Structure des Fichiers

```
src/
├── Entity/
│   ├── Product.php
│   ├── ProductCategory.php
│   ├── ProductTranslation.php
│   ├── ProductCategoryTranslation.php
│   ├── ProductOption.php
│   ├── ProductOptionGroup.php
│   ├── ProductOptionTranslation.php
│   ├── ProductOptionGroupTranslation.php
│   └── ProductMedia.php
├── Repository/
│   ├── ProductRepository.php
│   ├── ProductCategoryRepository.php
│   ├── ProductOptionRepository.php
│   └── [autres repositories...]
├── Form/
│   ├── ProductType.php
│   ├── ProductCategoryType.php
│   ├── ProductOptionType.php
│   └── [autres formulaires...]
└── DataFixtures/
    └── ProductFixtures.php
```

## 🎨 Support Multilingue

### Principe
Chaque entité traduisible a sa propre entité de traduction liée par une relation Many-to-One avec l'entité Language.

### Utilisation
```php
// Dans les templates Twig
{{ product.name(app.request.locale) }}
{{ product.description(app.request.locale) }}

// Dans les contrôleurs
$locale = $request->getLocale();
$productName = $product->getName($locale);
```

## 🔄 Relations entre Entités

```
Product (1) ←→ (1) ProductCategory
Product (1) ←→ (N) ProductTranslation
Product (M) ←→ (N) ProductOption (availableOptions)
Product (M) ←→ (N) ProductOption (selectedOptions)
Product (1) ←→ (N) ProductMedia
ProductMedia (1) ←→ (1) Media

ProductOptionGroup (1) ←→ (N) ProductOption
ProductOption (1) ←→ (N) ProductOptionTranslation
ProductOptionGroup (1) ←→ (N) ProductOptionGroupTranslation
```

## 📋 Prochaines Étapes Recommandées

1. **Contrôleurs d'Administration** : Créer les contrôleurs CRUD pour l'interface admin
2. **API REST** : Endpoints pour les clients et applications mobiles
3. **Interface Frontend** : Templates Twig pour l'affichage des produits
4. **Système de Panier** : Gestion des options sélectionnées et calculs de prix
5. **Système de Favoris** : Sauvegarde des produits personnalisés
6. **Système de Recherche** : Recherche avancée avec filtres par prix, surface, options

## 📞 Support

**Auteur** : jprud67 - Prudence Dieudonné ASSOGBA
**Version** : 1.0
**Date** : 2025-11-14

Ce système est prêt à l'emploi et peut être étendu selon les besoins spécifiques de MODUSCAP.