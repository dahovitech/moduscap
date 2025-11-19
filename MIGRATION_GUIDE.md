# ✅ Migration et Configuration - Guide Complet

## 📦 Résumé des Corrections Appliquées

Tous les bugs critiques ont été corrigés et le code a été poussé avec succès vers GitHub!

**Commit:** `ff4c079`  
**Branche:** `dev-edge`  
**Fichiers modifiés:** 6  
**Insertions:** +423 lignes  
**Suppressions:** -12 lignes

---

## 🗄️ ÉTAPE 1: Migrations Base de Données (OBLIGATOIRE)

La table `contact_message` doit être créée dans la base de données.

### Commandes à Exécuter

```bash
# 1. Générer la migration pour ContactMessage
php bin/console make:migration

# 2. Vérifier le fichier de migration généré
# Il devrait contenir CREATE TABLE contact_message avec tous les champs

# 3. Exécuter la migration
php bin/console doctrine:migrations:migrate --no-interaction

# 4. Vérifier que la table existe
php bin/console doctrine:schema:validate
```

### Structure Attendue de la Table

```sql
CREATE TABLE contact_message (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL,
    phone VARCHAR(30) NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'new',
    created_at TIMESTAMP NOT NULL,
    read_at TIMESTAMP NULL,
    replied_at TIMESTAMP NULL,
    admin_notes TEXT NULL,
    ip_address VARCHAR(45) NULL,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);
```

---

## 🔐 ÉTAPE 2: Configuration Environnement

### Fichier .env.local

Créez ou mettez à jour votre fichier `.env.local` avec:

```bash
###> symfony/framework-bundle ###
APP_ENV=dev
APP_SECRET=CHANGEZ_CETTE_VALEUR_PAR_UNE_CHAINE_ALEATOIRE_SECURISEE
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
# ou pour MySQL:
# DATABASE_URL="mysql://user:password@127.0.0.1:3306/moduscap?serverVersion=8.0.32&charset=utf8mb4"
###< doctrine/doctrine-bundle ###

###> symfony/mailer ###
MAILER_DSN=smtp://localhost:1025
# Pour production:
# MAILER_DSN=smtp://user:pass@smtp.example.com:587
###< symfony/mailer ###
```

### Générer APP_SECRET Sécurisé

```bash
# Méthode 1: Symfony
php bin/console secrets:generate-keys

# Méthode 2: OpenSSL
openssl rand -base64 32

# Méthode 3: PHP
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```

---

## 🧪 ÉTAPE 3: Tests des Fonctionnalités

### Test 1: Vider le Cache

```bash
php bin/console cache:clear
php bin/console cache:warmup
```

### Test 2: Vérifier Security Configuration

```bash
# Afficher les firewalls configurés
php bin/console debug:firewall

# Vérifier le provider
php bin/console debug:config security providers

# Lister toutes les routes protégées
php bin/console debug:router | grep profile
```

**Sortie attendue:**
```
app_user_login      ANY    ANY    /fr/login
app_user_register   ANY    ANY    /fr/register  
app_user_profile    ANY    ANY    /fr/profile
app_user_logout     ANY    ANY    /fr/logout
```

### Test 3: Inscription Utilisateur

**1. Interface Web:**
- Accéder à `http://localhost/fr/register`
- Remplir le formulaire
- Vérifier la redirection vers `/fr/login`
- Vérifier l'email de confirmation (si MAILER_DSN configuré)

**2. SQL Direct:**
```sql
-- Vérifier l'utilisateur créé
SELECT id, email, first_name, last_name, roles, is_active, created_at 
FROM user 
ORDER BY created_at DESC 
LIMIT 1;
```

### Test 4: Connexion

**1. Interface Web:**
- Accéder à `http://localhost/fr/login`
- Email: `test@example.com`
- Mot de passe: celui créé à l'inscription
- Cocher "Se souvenir de moi"
- Vérifier redirection vers `/fr/profile`

**2. Remember Me:**
- Se connecter avec "remember me" coché
- Fermer le navigateur
- Rouvrir - vous devriez être toujours connecté (7 jours)

### Test 5: Formulaire Contact

```bash
# 1. Accéder au formulaire
curl http://localhost/fr/contact

# 2. Soumettre un message
curl -X POST http://localhost/fr/contact \
  -d "first_name=Test" \
  -d "last_name=User" \
  -d "email=test@example.com" \
  -d "phone=0123456789" \
  -d "subject=Test" \
  -d "message=Message de test"

# 3. Vérifier en BDD
```

**SQL Vérification:**
```sql
SELECT * FROM contact_message ORDER BY created_at DESC LIMIT 5;
```

### Test 6: Traductions

```bash
# Vérifier qu'il n'y a pas de traductions manquantes
php bin/console debug:translation fr --only-missing
php bin/console debug:translation en --only-missing

# Compiler les traductions
php bin/console translation:extract --force --format=yaml fr
```

---

## 🐛 ÉTAPE 4: Résolution Problèmes Courants

### Problème 1: "Table 'contact_message' doesn't exist"

**Solution:**
```bash
php bin/console doctrine:migrations:migrate
# Si ça ne marche pas:
php bin/console doctrine:schema:update --force
```

### Problème 2: "The security configuration is empty"

**Cause:** Le fichier `config/packages/security.yaml` n'est pas chargé

**Solution:**
```bash
# Vérifier que le fichier existe
ls -la config/packages/security.yaml

# Vérifier les permissions
chmod 644 config/packages/security.yaml

# Clear cache
php bin/console cache:clear --no-warmup
```

### Problème 3: "Unable to connect to the database"

**Solution:**
```bash
# Vérifier la connexion
php bin/console dbal:run-sql "SELECT 1"

# Créer la base si elle n'existe pas
php bin/console doctrine:database:create

# Vérifier DATABASE_URL dans .env.local
```

### Problème 4: "Class 'App\Entity\ContactMessage' not found"

**Solution:**
```bash
# Régénérer l'autoload
composer dump-autoload

# Clear cache
php bin/console cache:clear
```

### Problème 5: Emails non envoyés

**Solutions:**

**A. Utiliser MailHog (Développement):**
```bash
# Installer MailHog
brew install mailhog  # macOS
# ou
sudo apt-get install golang-go
go install github.com/mailhog/MailHog@latest

# Lancer MailHog
mailhog

# Dans .env.local
MAILER_DSN=smtp://localhost:1025
```

**B. Utiliser Mailtrap (Développement):**
```bash
# Dans .env.local
MAILER_DSN=smtp://username:password@smtp.mailtrap.io:2525
```

**C. Gmail (Production):**
```bash
# Dans .env.local
MAILER_DSN=gmail+smtp://username:password@default
```

---

## 📊 ÉTAPE 5: Vérifications Finales

### Checklist Avant Mise en Production

- [ ] ✅ Migrations exécutées
- [ ] ✅ APP_SECRET généré et sécurisé
- [ ] ✅ DATABASE_URL configuré
- [ ] ✅ MAILER_DSN configuré
- [ ] ✅ Cache vidé et régénéré
- [ ] ✅ Tests formulaire contact OK
- [ ] ✅ Tests inscription/connexion OK
- [ ] ✅ Remember me fonctionne
- [ ] ✅ Emails envoyés (dev ou prod)
- [ ] ✅ Traductions complètes
- [ ] ✅ Pas d'erreurs dans les logs

### Commandes de Vérification

```bash
# 1. Vérifier la configuration complète
php bin/console debug:config

# 2. Vérifier les services
php bin/console debug:autowiring EmailService
php bin/console debug:autowiring ContactMessageRepository

# 3. Vérifier les routes
php bin/console debug:router | grep -E "(login|register|profile|contact)"

# 4. Lister les entités
php bin/console doctrine:mapping:info

# 5. Valider le schéma BDD
php bin/console doctrine:schema:validate
```

**Sortie attendue (schema validate):**
```
[OK] The mapping files are correct.
[OK] The database schema is in sync with the mapping files.
```

---

## 🚀 ÉTAPE 6: Déploiement Production

### 1. Variables d'Environnement

```bash
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=[GÉNÉRER_NOUVELLE_CLÉ_SÉCURISÉE]
DATABASE_URL=postgresql://prod_user:secure_password@db.example.com:5432/moduscap_prod
MAILER_DSN=smtp://api_key@smtp.sendgrid.net:587
```

### 2. Optimisations

```bash
# Installer les dépendances production
composer install --no-dev --optimize-autoloader

# Vider le cache
APP_ENV=prod php bin/console cache:clear

# Compiler les assets
npm run build

# Optimiser Doctrine
php bin/console doctrine:cache:clear-metadata
php bin/console doctrine:cache:clear-query
php bin/console doctrine:cache:clear-result
```

### 3. Permissions

```bash
# Définir les bonnes permissions
chown -R www-data:www-data var/
chmod -R 775 var/
chown -R www-data:www-data public/uploads/
chmod -R 775 public/uploads/
```

---

## 📝 ÉTAPE 7: Monitoring et Logs

### Logs Applicatifs

```bash
# Voir les logs en temps réel
tail -f var/log/dev.log

# Filtrer par erreurs
grep "ERROR" var/log/prod.log

# Voir les dernières erreurs
tail -100 var/log/prod.log | grep -A 5 "ERROR"
```

### Logs Symfony Mailer

```bash
# Voir les emails envoyés
php bin/console messenger:consume async -vv
```

### Logs Base de Données

```bash
# Activer le logger SQL (dev uniquement)
# Dans config/packages/dev/doctrine.yaml
doctrine:
    dbal:
        logging: true
        profiling: true
```

---

## 🎯 Récapitulatif des Commits

### Commit 1: ff9fd07
**Titre:** Fix: Correction de la route app_products_show vers app_product_show

**Changements:**
- Correction route dans products/index.html.twig
- Changement paramètre slug → code

### Commit 2: cbab4e6
**Titre:** Feature: Implémentation complète des fonctionnalités frontend

**Changements:**
- 22 fichiers créés/modifiés
- ContactMessage entity
- AuthController complet
- FrontController avec contact
- EmailService enrichi
- 10 templates
- 280 traductions

### Commit 3: ff4c079 (ACTUEL)
**Titre:** Fix: Corrections critiques - Auth locale, Security config, traductions manquantes

**Changements:**
- ✅ AuthController locale fix
- ✅ Security.yaml création
- ✅ Duplication WhatsApp supprimée
- ✅ +72 traductions controller
- ✅ Rapport bugfixes complet

---

## 📚 Documentation Technique

### Fichiers de Configuration Créés

1. **config/packages/security.yaml** (2.4 KB)
   - Form login avec CSRF
   - Logout configuration
   - Remember me (7 jours)
   - Access control

2. **config/packages/mailer.yaml** (Déjà existant)
   - Configuration MAILER_DSN

### Entités Créées

1. **ContactMessage**
   - 14 champs
   - 4 status constants
   - Validation complète
   - Lifecycle callbacks

### Contrôleurs Créés/Modifiés

1. **AuthController** (Nouveau)
   - /register
   - /login  
   - /profile
   - /profile/edit
   - /logout

2. **FrontController** (Modifié)
   - POST /contact

### Services Modifiés

1. **EmailService**
   - sendContactNotification()
   - sendContactConfirmation()
   - sendRegistrationConfirmation()

---

## ✨ Prochaines Améliorations Recommandées

### Court Terme
1. Ajouter CAPTCHA au formulaire contact (Google reCAPTCHA)
2. Implémenter rate limiting pour éviter spam
3. Ajouter logs détaillés pour debugging
4. Créer commande console pour nettoyer vieux messages

### Moyen Terme
1. Dashboard admin pour gérer messages contact
2. Système de notifications par email admin
3. Export CSV des messages contact
4. Statistiques avancées

### Long Terme
1. API REST pour mobile app
2. Websockets pour chat temps réel
3. Intégration CRM (Salesforce, HubSpot)
4. Analytics avancés

---

**🎉 Félicitations! Toutes les corrections sont appliquées et pushées vers GitHub!**

**👉 Prochaine étape:** Exécuter les migrations avec `php bin/console make:migration && php bin/console doctrine:migrations:migrate`

---

*Document généré automatiquement par MiniMax Agent*  
*Date: 2025-11-19*  
*Commit: ff4c079*
