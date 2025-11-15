# 🚀 Commandes de Chargement des Données MODUSCAP

## 📋 Vue d'ensemble

Les fixtures ont été supprimées et remplacées par des **commandes Symfony flexibles** permettant de charger les données indépendamment ou ensemble.

## 🎯 Avantages des Commandes vs Fixtures

| Fixtures | Commandes |
|----------|-----------|
| ❌ Ordre d'exécution complexe | ✅ Contrôle total de l'ordre |
| ❌ Conflits entre fixtures | ✅ Indépendance totale |
| ❌ Rechargement complexe | ✅ Rechargement granulaire |
| ❌ Debug difficile | ✅ Messages détaillés |
| ❌ Utilisation unique | ✅ Réutilisables |

## 🔧 Commandes Disponibles

### 1. `php bin/console app:load-products`
**Charge uniquement les produits, catégories et options**

```bash
php bin/console app:load-products
```

**Ce qu'elle fait :**
- ✅ Création des 5 catégories produits
- ✅ Création des produits (Capsule House, Apple Cabin, etc.)
- ✅ Création des groupes d'options (bardage, couverture, équipements)
- ✅ Création des options (original, terre, aluminium, etc.)
- ✅ Support multilingue (FR/EN)
- ✅ Caractéristiques techniques (prix, surface, dimensions)

**Sortie typique :**
```
🚀 Chargement des produits MODUSCAP
📦 Création des produits et catégories...
[████████████████████████████████████████] 100%
🔧 Création des groupes d'options...
⚙️  Création des options...
🏠 Création des produits...
[████████████████████████████████████████] 100%
✅ 5 catégories, 24 options et 5 produits créés
✅ Produits chargés avec succès !
```

### 2. `php bin/console app:load-product-media`
**Charge uniquement les médias (images) pour les produits existants**

```bash
php bin/console app:load-product-media
```

**Prérequis :** Produits déjà chargés (exécutez d'abord `app:load-products`)

**Ce qu'elle fait :**
- ✅ Association de 36 images aux produits existants
- ✅ Images principales + galeries
- ✅ Support multilingue (FR/EN)
- ✅ Validation des fichiers images
- ✅ Chemins de fichiers optimisés

**Sortie typique :**
```
🖼️  Chargement des médias produits MODUSCAP
📊 Vérification des produits existants...
📦 5 produits trouvés pour l'association des médias
[████████████████████████████████████████] 100%
✅ 36 médias créés au total pour 5 produits
✅ Médias chargés avec succès !
```

### 3. `php bin/console app:load-all-data`
**Charge TOUT en une seule commande (équivalent ancien fixture)**

```bash
php bin/console app:load-all-data
```

**Ce qu'elle fait :**
- 🔄 Exécute d'abord `app:load-products`
- 🔄 Puis exécute `app:load-product-media`
- ✅ Ordre garanti et optimal

**Sortie typique :**
```
🚀 Chargement complet des données MODUSCAP
📦 ÉTAPE 1: Chargement des produits
[... sortie app:load-products ...]
🖼️  ÉTAPE 2: Chargement des médias
[... sortie app:load-product-media ...]
✅ Toutes les données ont été chargées avec succès !

💡 Utilisations possibles:
  • php bin/console app:load-products (charge seulement les produits)
  • php bin/console app:load-product-media (charge seulement les médias)
  • php bin/console app:load-all-data (charge tout)
```

## 🧪 Utilisation Recommandée

### Cas 1: Première installation
```bash
# Chargez tout d'un coup
php bin/console app:load-all-data
```

### Cas 2: Développement/Debug
```bash
# Chargez seulement les produits (plus rapide)
php bin/console app:load-products

# Puis testez que ça fonctionne...

# Si vous modifiez les images, rechargez seulement les médias
php bin/console app:load-product-media
```

### Cas 3: Réinitialisation complète
```bash
# Supprimez les données
php bin/console doctrine:schema:drop --force

# Recréez la structure
php bin/console doctrine:schema:update --force

# Rechargez tout
php bin/console app:load-all-data
```

## 📁 Structure des Données

### Images Configurées par Produit

**Capsule House** (4 images):
- `o_1j3q9vbbdsgf22hil31kue7sda_15074.png` (principale)
- `o_1j3qafmjg81m589fpk23b1sutk_23664.jpg` (intérieur)
- `o_1j47ae3vvv1eck1q5kbvk1p478_64516_12425.jpg` (extérieur)
- `o_1j47ag4v1a1i1jupin6m27g6c_71300_52290_39204_13379.jpg` (détails)

**Apple Cabin** (3 images):
- `o_1j3qbs92qof98h11oj2s551edf9.jpg` (principale)
- `o_1j3qc5q8h1hgh1dv56fs1hdk10m9k.jpg` (intérieur)
- `o_1j3qf369j1fvtg4h1jv114v51aqc8.jpg` (ambiance)

**Natural House** (8 images):
- `o_1j3qbcbqgpmc14r51ac41sghotsg.jpg` (principale)
- `o_1j3qfjorkedkj92pjuao8vr8.jpg`, `o_1j3qg0nr8hj010lb1p36lv16ia8.jpg`
- `o_1j3qg2kpupkt4tprdk198d1g269_28910.jpg`, `Natural.jpg@v=20251003.jpg`
- `A1_left side.jpg@v=20251003.jpg`, `D1_right side.jpg@v=20251003.jpg`, `E8_left side.jpg@v=20251003.jpg`

**Dome House** (4 images):
- `o_1igb671gubt3b68135i17appagb_33825.jpg` (principale)
- `o_1igb6fo0j76m1cmmko11ke41r0c8_71287.jpg`, `o_1j3qavh2815l09jcntfolqirhb.jpg`, `o_1j3qb0q9718d01sv61op81339r6cb.jpg`

**Model Double** (3 images):
- `o_1iou50gg5k8l1lsfhrtqdd1b61t_12826_67060_47166_74242_54459.jpg` (principale)
- `o_1j3qev1bh6si13va1nac1b6v1nd98.jpg`, `o_1j3qfb97l1scngf01h7g151s1h6a8.jpg`

**📊 Total : 22 images configurées (1 principale + 21 galerie)**

## 🔧 Configuration Avancée

### Modifier les Images
Éditez le fichier : `src/Command/LoadProductMediaCommand.php`

```php
private const MEDIA_CONFIG = [
    'capsule-house' => [
        'main' => 'nouvelle-image-principale.jpg',
        'gallery' => [
            'image-1.jpg',
            'image-2.jpg'
        ],
        // ...
    ]
];
```

### Ajouter un Nouveau Produit
1. Ajoutez-le dans `app:load-products` (section `categoriesData`)
2. Ajoutez ses images dans `app:load-product-media` (section `MEDIA_CONFIG`)
3. Exécutez `php bin/console app:load-all-data`

## ✅ Avantages de cette Approche

1. **🎯 Précision** : Chargez exactement ce dont vous avez besoin
2. **⚡ Performance** : Pas besoin de recharger tout pour tester une modification
3. **🔧 Maintenance** : Code plus lisible et débogable
4. **🔄 Réutilisabilité** : Commandes autonomes et flexibles
5. **📊 Visibilité** : Messages détaillés à chaque étape
6. **🛡️ Robustesse** : Gestion d'erreurs avancée

## 🚀 Migration depuis les Fixtures

**Avant (fixtures) :**
```bash
php bin/console doctrine:fixtures:load --no-interaction
```

**Après (commandes) :**
```bash
php bin/console app:load-all-data
```

**Résultat identique, mais avec plus de contrôle !** 🎉