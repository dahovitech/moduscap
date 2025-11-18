# 🏠 MODUSCAP - Guide de Fonctionnement Complet

## 📋 Table des Matières

1. [Présentation Technique](#présentation-technique)
2. [Architecture du Système](#architecture-du-système)
3. [Entités et Modèle de Données](#entités-et-modèle-de-données)
4. [Gestion Multilingue](#gestion-multilingue)
5. [Interface d'Administration](#interface-dadministration)
6. [Commandes Système](#commandes-système)
7. [Gestion des Produits](#gestion-des-produits)
8. [Gestion des Options](#gestion-des-options)
9. [Processus de Commande](#processus-de-commande)
10. [Configuration et Déploiement](#configuration-et-déploiement)
11. [Dépannage](#dépannage)

---

## 🎯 Présentation Technique

### Vue d'ensemble
**MODUSCAP** est une application web Symfony basée sur le framework Symfony 6.x, conçue pour gérer un catalogue de produits d'habitations modulaires avec un système complet de gestion multilingue et de personnalisation d'options.

### Caractéristiques Techniques
- **Framework** : Symfony 6.x (PHP 8.x+)
- **Base de données** : SQLite/MySQL avec Doctrine ORM
- **Frontend** : Twig + Bootstrap 5 + Webpack Encore
- **Internationalisation** : Système multilingue complet (9 langues)
- **Architecture** : MVC + Entity-Repository pattern

---

## 🏗️ Architecture du Système

### Structure des Répertoires
```
moduscap/
├── src/
│   ├── Controller/           # Contrôleurs (Admin, Front, API)
│   ├── Entity/              # Entités Doctrine (modèle de données)
│   ├── Repository/          # Repositories pour l'accès aux données
│   ├── Form/                # Formulaires Symfony
│   ├── Service/             # Services métier (calcul prix, email, etc.)
│   ├── Command/             # Commandes console (import, migration, etc.)
│   └── EventListener/       # Écouteurs d'événements
├── templates/               # Templates Twig
│   ├── admin/              # Interface d'administration
│   ├── theme/              # Interface publique
│   └── components/         # Composants réutilisables
├── config/                  # Configuration Symfony
├── migrations/             # Migrations base de données
├── docs/                   # Documentation
└── assets/                 # Ressources frontend (CSS, JS)
```

### Patterns Utilisés
- **Repository Pattern** : Accès aux données via repositories spécialisés
- **Service Layer** : Logique métier dans des services dédiés
- **Event Listener** : Gestion des événements système
- **Command Pattern** : Tâches automatisées via commandes console
- **Form Component** : Gestion des formulaires avec validation

---

## 📊 Entités et Modèle de Données

### 1. Entités Principales

#### Product (Produit)
```php
// Propriétés principales
- id : Identifiant unique
- code : Code produit unique (ex: "apple-cabin")
- basePrice : Prix de base
- surface : Surface en m²
- dimensions : Dimensions (ex: "30m²")
- rooms : Nombre de pièces
- height : Hauteur sous plafond
- isActive : Statut actif/inactif
- createdAt, updatedAt : Timestamps
```

**Relations :**
- `category` : ManyToOne → ProductCategory
- `options` : ManyToMany → ProductOption
- `media` : OneToMany → ProductMedia
- `translations` : OneToMany → ProductTranslation

#### ProductOption (Option de Produit)
```php
// Propriétés principales
- id : Identifiant unique
- code : Code option unique
- price : Prix de l'option
- isActive : Statut actif
- sortOrder : Ordre d'affichage
```

**Relations :**
- `optionGroup` : ManyToOne → ProductOptionGroup
- `translations` : OneToMany → ProductOptionTranslation
- `products` : ManyToMany → Product

#### ProductOptionGroup (Groupe d'Options)
```php
// Propriétés principales
- id : Identifiant unique
- code : Code groupe unique
- inputType : Type d'input (select, multiselect, checkbox, radio)
- isRequired : Obligatoire ou non
- sortOrder : Ordre d'affichage
```

**Relations :**
- `translations` : OneToMany → ProductOptionGroupTranslation
- `options` : OneToMany → ProductOption

### 2. Entités de Traduction

Chaque entité principale possède une entité de traduction dédiée :
- **ProductTranslation** : Nom, description, caractéristiques
- **ProductOptionTranslation** : Nom et description de l'option
- **ProductOptionGroupTranslation** : Nom et description du groupe
- **ProductCategoryTranslation** : Nom et description de la catégorie

### 3. Entités Système

#### Language (Langue)
```php
// Propriétés principales
- id : Identifiant unique
- code : Code langue (fr, en, es, de, it, pt, ar, zh, ja)
- name : Nom de la langue
- nativeName : Nom dans la langue native
- isDefault : Langue par défaut
```

#### User (Utilisateur)
```php
// Propriétés principales
- id : Identifiant unique
- email : Adresse email
- roles : Rôles utilisateur (ROLE_ADMIN, ROLE_USER)
- isActive : Statut actif
```

#### Order (Commande)
```php
// Propriétés principales
- id : Identifiant unique
- user : Relation vers User
- status : Statut (pending, approved, rejected, completed)
- totalPrice : Prix total calculé
- selectedOptions : Options choisies (JSON)
```

---

## 🌍 Gestion Multilingue

### Système de Traductions
MODUSCAP supporte **9 langues** nativement :
- 🇫🇷 **Français** (fr) - Langue par défaut
- 🇬🇧 **Anglais** (en)
- 🇪🇸 **Espagnol** (es)
- 🇩🇪 **Allemand** (de)
- 🇮🇹 **Italien** (it)
- 🇵🇹 **Portugais** (pt)
- 🇸🇦 **Arabe** (ar)
- 🇨🇳 **Chinois** (zh)
- 🇯🇵 **Japonais** (ja)

### Architecture Multilingue

#### 1. Entités de Traduction
Chaque contenu traduisible a sa propre entité de traduction :
```php
ProductOptionGroupTranslation {
    optionGroup: ManyToOne ProductOptionGroup
    language: ManyToOne Language
    name: string
    description: text
}
```

#### 2. Repository Pattern
Les repositories implémentent des méthodes de récupération par langue :
```php
// Dans ProductOptionGroupRepository
public function findWithTranslations(?string $locale = null)
{
    return $this->createQueryBuilder('pog')
        ->leftJoin('pog.translations', 'translation')
        ->addSelect('translation')
        ->getQuery()
        ->getResult();
}
```

#### 3. Helper Methods
Les entités contiennent des méthodes d'aide :
```php
// Dans ProductOptionGroup
public function getName(?string $locale = null): string
{
    $translation = $this->getTranslation($locale);
    return $translation?->getName() ?: $this->getDefaultTranslation()?->getName() ?: '';
}
```

### Configuration des Langues
```yaml
# config/packages/translation.yaml
framework:
    default_locale: fr
    enabled_locales: ['fr', 'en', 'es', 'de', 'it', 'pt', 'ar', 'zh', 'ja']
```

---

## 🎛️ Interface d'Administration

### Structure de l'Admin

#### 1. Dashboard Principal
- **URL** : `/admin/`
- **Contrôleur** : `AdminController`
- **Fonctionnalités** :
  - Vue d'ensemble des statistiques
  - Accès rapide aux modules
  - Monitoring des commandes

#### 2. Gestion des Produits
- **URL** : `/admin/products`
- **Contrôleur** : `ProductController`
- **Fonctionnalités** :
  - Liste des produits avec filtres
  - Création/édition de produits
  - Gestion des médias
  - Configuration des options

#### 3. Gestion des Options
- **URL** : `/admin/options`
- **Contrôleur** : `OptionController`
- **Fonctionnalités** :
  - Gestion des groupes d'options
  - Gestion des options individuelles
  - Traductions des options
  - Configuration des prix

#### 4. Gestion des Langues
- **URL** : `/admin/languages`
- **Contrôleur** : `LanguageController`
- **Fonctionnalités** :
  - Activation/désactivation des langues
  - Configuration des langues par défaut
  - Gestion des traductions

#### 5. Gestion des Commandes
- **URL** : `/admin/orders`
- **Contrôleur** : `OrderManagementController`
- **Fonctionnalités** :
  - Liste des commandes
  - Approbation/rejet de commandes
  - Suivi du statut

#### 6. Gestion des Utilisateurs
- **URL** : `/admin/users`
- **Contrôleur** : `UserController`
- **Fonctionnalités** :
  - Création/édition d'utilisateurs
  - Gestion des rôles
  - Activation/désactivation

### Templates Twig
- **Base admin** : `templates/admin/base.html.twig`
- **Composants réutilisables** : `templates/components/`
- **Styles** : Bootstrap 5 avec personnalisations
- **JavaScript** : Components interactifs (sélecteur de médias, etc.)

---

## ⚡ Commandes Système

### Commandes de Chargement de Données

#### 1. Chargement des Langues
```bash
php bin/console app:load-languages
```
**Fonction :** Charge les 9 langues supportées dans la base de données.

#### 2. Chargement des Catégories
```bash
php bin/console app:load-categories
```
**Fonction :** Charge les catégories de produits avec leurs traductions.

#### 3. Chargement des Options
```bash
php bin/console app:load-product-options
```
**Fonction :** Charge les groupes d'options et options individuelles avec traductions dans les 9 langues.

#### 4. Chargement des Produits
```bash
php bin/console app:load-products
```
**Fonction :** Charge les produits avec leurs traductions et associations.

#### 5. Chargement Complet
```bash
php bin/console app:load-all-data
```
**Fonction :** Exécute toutes les commandes de chargement dans l'ordre correct.

### Commandes de Maintenance

#### 1. Correction de Base de Données
```bash
php bin/console app:fix-database
```
**Fonction :** Corrige automatiquement la structure de la base de données SQLite.

#### 2. Chargement des Utilisateurs
```bash
php bin/console app:load-users
```
**Fonction :** Charge les utilisateurs administrateurs par défaut.

#### 3. Chargement des Paramètres
```bash
php bin/console app:load-settings
```
**Fonction :** Charge les paramètres système par défaut.

### Commandes de Serveur
```bash
# Démarrage du serveur de développement
php bin/console server:run

# Démarrage du serveur sur un port spécifique
php bin/console server:run --port=8080
```

---

## 🏷️ Gestion des Produits

### Structure des Produits

#### 1. Informations de Base
```php
Product {
    code: "apple-cabin"          # Code unique
    basePrice: "45000.00"       # Prix de base en EUR
    surface: "25.50"            # Surface en m²
    dimensions: "5m x 5m"       # Dimensions
    rooms: 2                     # Nombre de pièces
    height: 280                  # Hauteur en cm
    isActive: true               # Statut
}
```

#### 2. Associations
- **Catégorie** : Chaque produit appartient à une catégorie
- **Options** : Association ManyToMany avec les options
- **Médias** : Images, vidéos, documents associés
- **Traductions** : Nom, description, caractéristiques par langue

### Cycle de Vie des Produits

#### 1. Création
```php
// Dans ProductController::new()
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $product = new Product();
    $form = $this->createForm(ProductType::class, $product);
    // ... logique de création
}
```

#### 2. Édition
- Gestion des traductions via onglets
- Upload et gestion des médias
- Configuration des options disponibles
- Validation des données

#### 3. Validation
- Contraintes sur les propriétés (NotBlank, Length, etc.)
- Validation des relations
- Vérification de l'unicité des codes

---

## ⚙️ Gestion des Options

### Types d'Options

#### 1. Select (Sélection unique)
```php
ProductOptionGroup {
    inputType: "select"
    isRequired: true
    // Exemple : Type de bardage
    // Options : Bois, Métal, Composite
}
```

#### 2. Multiselect (Sélection multiple)
```php
ProductOptionGroup {
    inputType: "multiselect"
    isRequired: false
    // Exemple : Matériaux
    // Options : Bois Massif, Bois Traité, Métal, etc.
}
```

#### 3. Checkbox (Cases à cocher)
```php
ProductOptionGroup {
    inputType: "checkbox"
    isRequired: false
    // Exemple : Équipements optionnels
}
```

### Structure des Options

#### 1. Groupe d'Options
```php
ProductOptionGroup {
    code: "bardage"              # Code unique
    inputType: "select"          # Type d'input
    isRequired: true             # Obligatoire
    sortOrder: 1                 # Ordre d'affichage
    isActive: true               # Statut
}
```

#### 2. Option Individuelle
```php
ProductOption {
    code: "bardage-bois"         # Code unique
    price: "50.00"               # Prix supplémentaire
    isActive: true               # Statut
    sortOrder: 1                 # Ordre dans le groupe
}
```

### Traductions des Options
Chaque option et groupe d'options est traduit dans les 9 langues :
```php
ProductOptionTranslation {
    name: "Bardage Bois"         # Nom traduit
    description: "Description..." # Description traduite
    language: French             # Langue de la traduction
}
```

---

## 📋 Processus de Commande

### Workflow de Commande

#### 1. Sélection de Produit
- L'utilisateur parcourt le catalogue
- Sélectionne un produit de base
- Consulte les détails et options disponibles

#### 2. Personnalisation
- Choix des options via les groupes configurés
- Calcul automatique du prix final
- Visualisation en temps réel des modifications

#### 3. Soumission
- Informations personnelles du client
- Validation des données
- Création de la commande en statut "pending"

#### 4. Traitement
- Notification email automatique
- Examen par l'équipe commerciale
- Approbation/rejet avec justification

#### 5. Suivi
- Mise à jour du statut de commande
- Notifications email aux étapes importantes
- Historique complet des modifications

### Gestion des Statuts
```php
OrderStatus {
    PENDING: "En attente d'approbation"
    APPROVED: "Approuvée"
    REJECTED: "Rejetée"
    COMPLETED: "Terminée"
}
```

---

## ⚙️ Configuration et Déploiement

### Variables d'Environnement
```bash
# .env.local
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
MAILER_DSN=smtp://localhost
APP_ENV=prod
APP_SECRET=your-secret-key
```

### Base de Données
```bash
# Migration
php bin/console doctrine:migrations:migrate

# Fix automatique (pour SQLite)
php bin/console app:fix-database
```

### Assets et Frontend
```bash
# Installation des dépendances
composer install
npm install

# Build des assets
npm run build

# Watch en développement
npm run watch
```

### Serveur de Développement
```bash
# Démarrage
php bin/console server:run

# Ou avec Docker
docker-compose up -d
```

---

## 🛠️ Dépannage

### Problèmes Courants

#### 1. Erreur de Base de Données
**Problème :** `no such column: product_option_group_id`
**Solution :**
```bash
php bin/console app:fix-database
php bin/console doctrine:schema:update --force
```

#### 2. Erreur de Traductions
**Problème :** Champs de traduction non chargés
**Solution :**
```bash
php bin/console app:load-product-options
php bin/console app:load-all-data
```

#### 3. Erreur d'Associations
**Problème :** `Unrecognized field` erreurs
**Solution :**
- Vérifier les noms des propriétés dans les entités
- Utiliser `setOptionGroup()` au lieu de `setProductOptionGroup()`

### Tests
```bash
# Test de base de données
php test-fixed.php

# Test PHPUnit
php bin/phpunit
```

### Logs
```bash
# Logs Symfony
tail -f var/log/dev.log

# Logs système
tail -f var/log/prod.log
```

---

## 📊 Statistiques et Monitoring

### Métriques Importantes
- **Produits** : Nombre total, par catégorie, actifs/inactifs
- **Options** : Nombre de groupes, options par groupe
- **Traductions** : Couverture par langue
- **Commandes** : Volume, statuts, tendances
- **Utilisateurs** : Actifs, rôles, permissions

### Points de Surveillance
- **Performance** : Temps de réponse, requêtes DB
- **Erreurs** : Logs, exceptions, erreurs 404/500
- **Sécurité** : Tentatives d'intrusion, validation des données
- **Disque** : Taille de la base, logs, cache

---

## 🔮 Évolutions Possibles

### Améliorations Court Terme
- **API REST** : Endpoints pour applications mobiles
- **Cache** : Optimisation des performances
- **Search** : Moteur de recherche avancé
- **Analytics** : Tableaux de bord détaillés

### Évolutions Moyen Terme
- **E-commerce** : Panier et paiement intégré
- **CRM** : Gestion client avancée
- **BIM** : Intégration modèles 3D
- **Mobile** : Application native

### Vision Long Terme
- **IA** : Recommandations personnalisées
- **IoT** : Capteurs et monitoring
- **Marketplace** : Plateforme multi-vendeurs
- **Sustainability** : Impact environnemental

---

## 📞 Support et Contact

### Équipe Technique
- **Développeur Principal** : Prudence Dieudonné ASSOGBA
- **Email** : jprud67@gmail.com
- **GitHub** : https://github.com/dahovitech/moduscap

### Documentation
- **Guide de Dépannage** : `docs/GUIDE_DEPANNAGE_MODUSCAP.md`
- **Support Multilingue** : `docs/SUPPORT_MULTILINGUE_COMPLET.md`
- **Processus Client** : `docs/GUIDE_PROCESSUS_CLIENT_MODUSCAP.md`

### Ressources
- **Code Source** : GitHub repository
- **Documentation** : Dossier `docs/`
- **Tests** : Scripts de test intégrés

---

*Document généré le 2025-11-18 17:21 - Version 1.0*