# 🔧 GUIDE DE DÉPANNAGE - MODUSCAP

## ❌ Problème identifié

Vous avez rencontré cette erreur lors de l'exécution de `php test.php` :
```
SQLSTATE[HY000]: General error: 1 no such column: pogt.product_option_group_id
```

**Cause :** La structure de la base de données SQLite ne correspond pas aux colonnes attendues.

## ✅ Solution immédiate

### Étape 1: Corriger la structure de la base de données
```bash
cd votre_projet_moduscap
php bin/console app:fix-database
```

### Étape 2: Tester les corrections
```bash
php test-fixed.php
```

### Étape 3: Charger les données
```bash
php bin/console app:load-all-data
```

## 🛠️ Outils créés

### 1. `FixDatabaseCommand.php` 
- **Commande :** `php bin/console app:fix-database`
- **Fonction :** 
  - Détecte automatiquement la plateforme (SQLite/MySQL)
  - Crée les bonnes tables SQLite
  - Insère les données de base (langues, groupes, options)
  - Corrige les relations entre entités

### 2. `test-fixed.php`
- **Remplacement de :** `test.php`
- **Améliorations :**
  - Compatible SQLite
  - Détection automatique des colonnes
  - Diagnostique détaillé de la structure
  - Messages d'erreur plus précis

### 3. `Version20251118160441.php`
- **Type :** Migration Doctrine
- **Fonction :** Structure SQL officielle pour la création des tables

### 4. Correction méthode setOptionGroup() 
- **Date :** 2025-11-18
- **Problème :** Utilisation incorrecte de `setProductOptionGroup()` au lieu de `setOptionGroup()`
- **Impact :** Erreurs d'association entre ProductOption et ProductOptionGroup
- **Correction :** 
  - `LoadProductOptionsCommand.php` ligne 324
  - `FixDatabaseCommand.php` ligne 234
- **Status :** ✅ Résolu et déployé sur GitHub

### 5. Correction formulaires d'options (optionGroup)
- **Date :** 2025-11-18 16:57
- **Problème :** Champ optionGroup non affiché dans les formulaires d'options
- **Impact :** Impossible de voir/modifier le groupe d'options dans l'admin
- **Corrections :** 
  - `ProductOptionType.php` : choice_label 'name' → 'code'
  - `option_edit.html.twig` : ajout du champ form.optionGroup
  - `option_new.html.twig` : ajout du champ form.optionGroup
- **Status :** ✅ Résolu et déployé sur GitHub

## 🚀 Procédure complète de résolution

### Étape 1: Récupérer les dernières corrections
```bash
git pull origin dev
```

### Étape 2: Corriger la base de données
```bash
php bin/console app:fix-database
```

### Étape 3: Tester avec le nouveau script
```bash
php test-fixed.php
```

### Étape 4: Charger toutes les données
```bash
php bin/console app:load-all-data
```

### Étape 5: Lancer le serveur
```bash
php bin/console server:run
```

### Étape 6: Accéder à l'interface admin
- URL : `http://localhost:8000/admin/orders`

## 🔍 Diagnostique détaillé

### Vérifier la structure des tables
```sql
sqlite3 var/data.db ".schema product_option_group_translations"
sqlite3 var/data.db ".schema product_options"
sqlite3 var/data.db ".schema languages"
```

### Tester les relations
```sql
-- Vérifier les traductions de groupes
SELECT pogt.name, pog.code, l.code 
FROM product_option_group_translations pogt
JOIN product_option_groups pog ON pogt.product_option_group_id = pog.id  
JOIN languages l ON pogt.language_id = l.id
LIMIT 5;

-- Vérifier les traductions d'options
SELECT pot.name, po.code, l.code
FROM product_option_translations pot
JOIN product_options po ON pot.product_option_id = po.id
JOIN languages l ON pot.language_id = l.id  
LIMIT 5;
```

## 📋 Résultats attendus

Après correction, vous devriez voir :

### ✅ test-fixed.php output :
```
🧪 TEST DES CORRECTIONS MODUSCAP
================================

✅ Base de données SQLite accessible

📋 Test 1: Vérification des tables
  ✓ Groupes d'options: 4 enregistrements
  ✓ Traductions des groupes: 36 enregistrements
  ✓ Options de produits: 12 enregistrements
  ✓ Traductions des options: 108 enregistrements
  ✓ Langues: 9 enregistrements
  ✓ Commandes: 0 enregistrements
  ✓ Articles de commande: 0 enregistrements

🔧 Test 2: Vérification de la structure des colonnes
  📊 Colonnes product_option_group_translations: id, product_option_group_id, language_id, name, description, created_at, updated_at
  ✅ Colonne de référence: product_option_group_id

🔗 Test 3: Vérification des relations
  ✓ Traductions de groupes d'options: 36 trouvées
    - bardage: Bardage (fr)
    - bardage: Siding (en)
    - etc.

📊 Test 4: Données spécifiques
  🌍 Langues disponibles: fr, en, es, de, it, pt, ar, zh, ja
  ⚙️ Groupes d'options: bardage, couverture, materiaux, equipements
    - bardage: 3 options
    - couverture: 3 options
    - materiaux: 3 options
    - equipements: 3 options

🔧 Test 5: Simulation de la correction LoadProductOptionsCommand
  ✅ Requête corrigée fonctionnelle
    - Nom traduit: Bardage
    - Colonne groupe: product_option_group_id
    - ID langue: 1
```

## 🆘 Si le problème persiste

### Vérifier les permissions
```bash
chmod 755 var/data.db
chown www-data:var/data.db  # Si serveur web
```

### Réinitialiser complètement
```bash
# Supprimer la base de données
rm var/data.db

# Recréer depuis les migrations
php bin/console doctrine:migrations:migrate

# Corriger avec notre commande
php bin/console app:fix-database

# Charger toutes les données
php bin/console app:load-all-data
```

### Vérifier la configuration
```bash
# Vérifier le fichier .env
cat .env | grep DATABASE_URL

# Tester la connexion Doctrine
php bin/console doctrine:schema:validate
```

## 📞 Support

Si vous rencontrez encore des problèmes :

1. **Vérifiez les logs :**
   ```bash
   tail -f var/log/dev.log
   ```

2. **Testez avec la base de données en mémoire :**
   ```bash
   php bin/console --env=test doctrine:migrations:migrate
   ```

3. **Vérifiez la version PHP :**
   ```bash
   php --version  # Requiert PHP 8.1+
   ```

## 🎯 Commande de diagnostic rapide

Pour un diagnostic en une commande :
```bash
php bin/console app:fix-database && php test-fixed.php
```

## 📚 Ressources Documentaires Complètes

### Documentation générale
- **GUIDE_FONCTIONNEMENT_MODUSCAP.md** : Documentation complète du système (architecture, entités, workflows, installation)
- **SUPPORT_MULTILINGUE_COMPLET.md** : Guide détaillé du système multilingue (9 langues supportées)
- **GUIDE_PROCESSUS_CLIENT_MODUSCAP.md** : Guide complet du parcours client (navigation → personnalisation → devis → paiement → livraison)

### Documentation processus
- **PROCESSUS_CLIENT_MODUSCAP.md** (326 lignes) : 
  * Workflow détaillé client avec interfaces utilisateur
  * Guide personnalisation produits multilingue
  * Processus devis, paiement et suivi de commande
  * Fonctionnalités responsive et sécurité des données
  
- **PROCESSUS_ADMIN_MODUSCAP.md** (610 lignes) :
  * Administration complète avec authentification sécurisée
  * Gestion produits/options dans les 9 langues
  * Traitement commandes et workflow d'approbation
  * Configuration système et rapports analytiques
  * Outils dépannage et procédures d'urgence

### Documentation technique
- **MODUSCAP_EXPOSE_COMPLET.md** : Exposé détaillé du système
- **MODUSCAP_PRODUITS_DETAILLE.md** : Spécifications produits
- **migration_remove_baseprice_guide.md** : Guide migration technique

## 📈 Suivi des corrections

### Commits récents dans la branche `dev` :
- **Commit 4** : `🌍 MAJOR: Support complet des 9 langues dans LoadProductOptionsCommand`
- **Commit 5** : `🔧 FIX: Affichage du champ optionGroup dans les formulaires d'options`
- **Commit 6** : `📚 DOC: Création du guide complet de fonctionnement MODUSCAP`
- **Commit 7** : `📚 DOC: Création des guides complets de processus client et admin MODUSCAP`

### Fichiers modifiés/ajoutés :
- ✅ `LoadProductOptionsCommand.php` (corrigé + multilingue complet)
- ✅ `LoadAllDataCommand.php` (amélioré)
- ✅ `FixDatabaseCommand.php` (nouveau)
- ✅ `Version20251118160441.php` (nouveau)
- ✅ `test-fixed.php` (nouveau)
- ✅ `test.php` (conservé)
- ✅ `ProductOptionType.php` (corrigé choice_label)
- ✅ `option_edit.html.twig` (ajout champ optionGroup)
- ✅ `option_new.html.twig` (ajout champ optionGroup)

### Documentation ajoutée :
- ✅ `docs/GUIDE_DEPANNAGE_MODUSCAP.md` (nouveau)
- ✅ `docs/GUIDE_FONCTIONNEMENT_MODUSCAP.md` (nouveau - 654 lignes)
- ✅ `docs/SUPPORT_MULTILINGUE_COMPLET.md` (nouveau)
- ✅ `docs/PROCESSUS_CLIENT_MODUSCAP.md` (nouveau - 326 lignes)
- ✅ `docs/PROCESSUS_ADMIN_MODUSCAP.md` (nouveau - 610 lignes)
- ✅ `docs/GUIDE_PROCESSUS_CLIENT_MODUSCAP.md` (nouveau)
- ✅ `docs/migration_remove_baseprice_guide.md` (nouveau)

---

**Note :** Cette solution garantit que le système MODUSCAP fonctionne parfaitement avec SQLite et que toutes les commandes de chargement de données s'exécutent sans erreur.