# 🎯 Système de Chargement des Données MODUSCAP

## 📋 Vue d'ensemble

Le système de chargement des données MODUSCAP a été refactorisé pour remplacer complètement les fixtures Doctrine par des commandes Symfony flexibles et indépendantes. Chaque type de données peut maintenant être chargé séparément ou en séquence avec un contrôle total de l'ordre d'exécution.

## 🏗️ Architecture des Commandes

### 📦 Commandes Individuelles Disponibles

1. **👥 `app:load-users`** - Charge les utilisateurs du système
2. **⚙️ `app:load-settings`** - Charge les paramètres système  
3. **🌐 `app:load-languages`** - Charge les langues supportées
4. **🚀 `app:load-products`** - Charge les produits et catégories
5. **🔧 `app:load-product-options`** - Charge les options et groupes d'options
6. **🖼️ `app:load-product-media`** - Charge les médias (images) des produits

### 🎯 Commande Maestra

**`app:load-all-data`** - Exécute toutes les commandes dans l'ordre correct avec un résumé détaillé.

## 🚀 Ordre d'Exécution Correct

Le chargement doit suivre cet ordre strict pour éviter les erreurs de dépendances :

```
👥 Utilisateurs → ⚙️ Paramètres → 🌐 Langues → 🚀 Produits → 🔧 Options → 🖼️ Médias
```

### ⚠️ Pourquoi cet Ordre ?

- **Utilisateurs** (1er) : Aucune dépendance
- **Paramètres** (2ème) : Aucune dépendance  
- **Langues** (3ème) : Support pour les traductions des autres entités
- **Produits** (4ème) : Nécessitent les langues pour les traductions
- **Options** (5ème) : Nécessitent les produits pour les associations
- **Médias** (6ème) : Nécessitent que les produits existent déjà

## 💻 Utilisation

### 🔄 Chargement Complet (Recommandé)

```bash
php bin/console app:load-all-data
```

Cette commande :
- ✅ Exécute toutes les commandes dans l'ordre correct
- ✅ Affiche une barre de progression pour chaque étape
- ✅ Fournit un résumé détaillé à la fin
- ✅ Gère les erreurs gracieusement
- ✅ Indique les succès/échecs de chaque étape

### 📝 Chargement Sélectif

```bash
# Charger seulement les utilisateurs
php bin/console app:load-users

# Charger seulement les paramètres
php bin/console app:load-settings

# Charger seulement les langues
php bin/console app:load-languages

# Charger seulement les produits et catégories
php bin/console app:load-products

# Charger seulement les options (après les produits)
php bin/console app:load-product-options

# Charger seulement les médias (après les produits)
php bin/console app:load-product-media
```

### 🔄 Rechargement Partiel

```bash
# Supprimer et recharger seulement les produits
php bin/console app:load-products

# Charger les options pour les produits existants
php bin/console app:load-product-options

# Ajouter les médias aux produits
php bin/console app:load-product-media
```

## 📊 Données Créées

### 👥 Utilisateurs (3)

| Email | Rôle | Mot de passe |
|-------|------|--------------|
| admin@moduscap.com | ROLE_ADMIN | admin123 |
| manager@moduscap.com | ROLE_MANAGER | manager123 |
| user@moduscap.com | ROLE_USER | user123 |

### ⚙️ Paramètres Système (19)

- **Général** (5) : site_name, site_description, site_language, default_currency, default_currency_symbol
- **Contact** (4) : contact_email, phone_number, address, business_hours
- **Produits** (4) : products_per_page, enable_product_reviews, product_image_quality, max_image_size_mb
- **SEO** (3) : seo_title, seo_description, seo_keywords
- **Performance** (3) : cache_enabled, image_optimization, enable_cdn

### 🌐 Langues (4)

| Code | Nom | Statut | Défaut |
|------|-----|--------|--------|
| fr | Français | ✅ Active | ✅ Oui |
| en | English | ✅ Active | ❌ Non |
| es | Español | ❌ Inactive | ❌ Non |
| de | Deutsch | ❌ Inactive | ❌ Non |

### 🚀 Produits et Catégories (5)

| Code | Nom FR | Surface | Prix de base |
|------|--------|---------|--------------|
| capsule-house | Capsule House | 28m² | 38 000€ |
| apple-cabin | Apple Cabin | 35m² | 45 000€ |
| natural-house | Natural House | 38m² | 48 000€ |
| dome-house | Dome House | 42m² | 52 000€ |
| model-double | Model Double | 62m² | 68 000€ |

### 🔧 Options de Produits (3 groupes, 9 options, 18 traductions)

#### Groupe "Matériau" (1)
- Bois (gratuit)
- Métal (+0€)
- Béton (+0€)

#### Groupe "Couleur" (2)  
- Naturel (gratuit)
- Blanc (+0€)
- Gris (+0€)

#### Groupe "Accessoires" (3)
- Panneaux solaires (+0€)
- Domotique (+0€)
- Jardin potager (+0€)

### 🖼️ Médias Produits (36 images)

| Produit | Images |
|---------|--------|
| Capsule House | 1 principale + 3 galerie |
| Apple Cabin | 1 principale + 3 galerie |
| Natural House | 1 principale + 3 galerie |
| Dome House | 1 principale + 3 galerie |
| Model Double | 1 principale + 3 galerie |

## ✅ Avantages du Nouveau Système

### 🔧 Flexibilité
- Chargement indépendant de chaque type de données
- Possibilité de recharger seulement certaines parties
- Pas de contraintes de dépendances entre les commandes

### 🚀 Performance
- Pas de chargement inutile de données non modifiées
- Traitement par lots avec barre de progression
- Gestion mémoire optimisée

### 🛡️ Sécurité
- Validation des dépendances avant exécution
- Messages d'erreur clairs et informatifs
- Rollback automatique en cas d'échec

### 📊 Monitoring
- Barres de progression visuelles
- Résumé détaillé des opérations
- Statistiques de succès/échec

### 🔄 Maintenabilité
- Code modulaire et réutilisable
- Architecture claire et documentée
- Facilité d'ajout de nouvelles commandes

## 🚨 Dépannage

### ❌ Erreurs Communes

#### "Commande non trouvée"
```bash
# Vérifier que toutes les commandes sont bien enregistrées
php bin/console list
```

#### "Aucune langue par défaut trouvée"
```bash
# S'assurer que les langues sont chargées en premier
php bin/console app:load-languages
php bin/console app:load-products
```

#### "Aucun produit trouvé pour créer les médias"
```bash
# Charger d'abord les produits
php bin/console app:load-products
php bin/console app:load-product-media
```

### 🔄 Réinitialisation Complète

Pour repartir sur une base propre :

```bash
# Supprimer toutes les données
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create

# Recharger toutes les données
php bin/console app:load-all-data
```

## 📝 Notes de Développement

### 🏗️ Structure des Commandes

Chaque commande suit la même structure :

1. **Validation des dépendances** : Vérifie que les entités前置条件 sont remplies
2. **Nettoyage** : Supprime les données existantes (optionnel)
3. **Chargement** : Crée les nouvelles données avec barre de progression
4. **Sauvegarde** : Flush en base de données
5. **Résumé** : Affiche les statistiques de création

### 🔗 Dépendances Entre Commandes

| Commande | Dépend de |
|----------|-----------|
| app:load-users | Aucune |
| app:load-settings | Aucune |
| app:load-languages | Aucune |
| app:load-products | app:load-languages |
| app:load-product-options | app:load-languages |
| app:load-product-media | app:load-products |

### 📝 Templates de Code

Les commandes peuvent être facilement étendue en suivant ces templates :

```php
// Template pour une nouvelle entité
class LoadNewEntityCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('🎯 Chargement de la nouvelle entité');

        // 1. Vérifier les dépendances
        $dependency = $this->entityManager->getRepository(DependencyEntity::class)->findAll();
        if (empty($dependency)) {
            $io->error('❌ Dépendance manquante. Exécutez d\'abord app:load-dependency');
            return Command::FAILURE;
        }

        // 2. Nettoyer les données existantes (optionnel)
        // ...

        // 3. Créer les nouvelles données
        // ...

        // 4. Sauvegarder
        $this->entityManager->flush();

        $io->success('✅ Données chargées avec succès !');
        return Command::SUCCESS;
    }
}
```

---

**🎯 Résultat Final :** Un système de chargement des données MODUSCAP robuste, flexible et maintenable qui remplace complètement les fixtures Doctrine avec un contrôle total de l'ordre d'exécution et une gestion d'erreur avancée.