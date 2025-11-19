# 🧪 Guide de Test et Validation - ModusCap

> **Auteur**: MiniMax Agent  
> **Date**: 2025-11-19  
> **Objectif**: Valider toutes les fonctionnalités après les corrections critiques

---

## 📋 Table des Matières

1. [Préparation de l'Environnement](#1-préparation-de-lenvironnement)
2. [Exécution des Migrations](#2-exécution-des-migrations)
3. [Tests de Sécurité et Authentification](#3-tests-de-sécurité-et-authentification)
4. [Tests du Formulaire de Contact](#4-tests-du-formulaire-de-contact)
5. [Tests des Emails](#5-tests-des-emails)
6. [Tests des Traductions](#6-tests-des-traductions)
7. [Tests du Système de Devis](#7-tests-du-système-de-devis)
8. [Checklist de Validation Finale](#8-checklist-de-validation-finale)
9. [Résolution des Problèmes](#9-résolution-des-problèmes)

---

## 1. Préparation de l'Environnement

### 1.1 Récupérer les dernières modifications

```bash
cd /chemin/vers/moduscap
git checkout dev-edge
git pull origin dev-edge
```

### 1.2 Vérifier les dépendances Composer

```bash
composer install --no-dev --optimize-autoloader
```

### 1.3 Configurer l'environnement (.env.local)

Créez ou modifiez le fichier `.env.local` :

```env
# Configuration de la base de données
DATABASE_URL="mysql://user:password@127.0.0.1:3306/moduscap?serverVersion=8.0&charset=utf8mb4"

# Secret de l'application (IMPORTANT: Générez une valeur unique)
APP_SECRET=CHANGEZ_MOI_PAR_UNE_VALEUR_SECURISEE_LONGUE

# Configuration du mailer (exemple avec Gmail)
MAILER_DSN=smtp://votre-email@gmail.com:votre-mot-de-passe-app@smtp.gmail.com:587

# Ou avec Mailtrap pour les tests
# MAILER_DSN=smtp://username:password@smtp.mailtrap.io:2525

# Email de l'administrateur
ADMIN_EMAIL=admin@moduscap.com

# Environnement
APP_ENV=dev
```

### 1.4 Générer un APP_SECRET sécurisé

```bash
# Génère une chaîne aléatoire de 32 caractères
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```

Copiez le résultat dans votre `.env.local` pour `APP_SECRET`.

---

## 2. Exécution des Migrations

### 2.1 Vérifier l'état de la base de données

```bash
# Vérifier la connexion à la base
php bin/console doctrine:database:create --if-not-exists

# Vérifier l'état des migrations
php bin/console doctrine:migrations:status
```

### 2.2 Créer la migration pour ContactMessage

```bash
# Générer automatiquement la migration
php bin/console make:migration

# Le fichier de migration sera créé dans migrations/
# Exemple: migrations/Version20251119085000.php
```

### 2.3 Examiner la migration générée

```bash
# Ouvrir le fichier de migration pour vérification
cat migrations/Version*.php
```

**Attendez-vous à voir** :
- Création de la table `contact_message`
- Colonnes : id, name, email, phone, company, subject, message, status, created_at, updated_at, answered_at, admin_notes
- Index sur email et status

### 2.4 Exécuter la migration

```bash
# Exécuter la migration
php bin/console doctrine:migrations:migrate --no-interaction

# Vérifier que la table a été créée
php bin/console doctrine:schema:validate
```

**Résultat attendu** :
```
[OK] The database schema is in sync with the mapping files.
```

### 2.5 Vérifier les tables créées

```bash
# MySQL
mysql -u user -p -e "USE moduscap; SHOW TABLES;"

# PostgreSQL
psql -U user -d moduscap -c "\dt"
```

---

## 3. Tests de Sécurité et Authentification

### 3.1 Vérifier la configuration de sécurité

```bash
# Valider la configuration sans erreurs
php bin/console debug:config security

# Vérifier les firewalls configurés
php bin/console debug:firewall
```

**Résultat attendu** :
- Firewall `main` configuré avec `form_login`
- Provider `app_user_provider` pointant vers `App\Entity\User`
- Remember me activé
- Logout configuré

### 3.2 Créer un utilisateur de test via la console (optionnel)

Si vous voulez tester rapidement sans passer par le formulaire :

```bash
php bin/console doctrine:query:sql "INSERT INTO user (email, roles, password, first_name, last_name, is_verified, created_at, updated_at) VALUES ('test@example.com', '[\"ROLE_USER\"]', '\$2y\$13\$somehashedpassword', 'Test', 'User', 1, NOW(), NOW())"
```

### 3.3 Démarrer le serveur de développement

```bash
symfony server:start
# Ou
php -S localhost:8000 -t public/
```

### 3.4 Tests manuels de l'authentification

**Test 1: Inscription d'un nouvel utilisateur**

1. Ouvrir : `http://localhost:8000/fr/register`
2. Remplir le formulaire :
   - Prénom : Jean
   - Nom : Dupont
   - Email : jean.dupont@example.com
   - Mot de passe : Test1234!
   - Confirmation : Test1234!
3. Soumettre le formulaire
4. ✅ **Vérifier** :
   - Redirection vers `/fr/profile`
   - Message de succès affiché
   - Email de confirmation envoyé (vérifier logs ou boîte mail)

**Test 2: Connexion avec les identifiants créés**

1. Se déconnecter : `http://localhost:8000/fr/logout`
2. Aller à : `http://localhost:8000/fr/login`
3. Entrer :
   - Email : jean.dupont@example.com
   - Mot de passe : Test1234!
   - Cocher "Se souvenir de moi"
4. Soumettre
5. ✅ **Vérifier** :
   - Redirection vers `/fr/profile`
   - Nom d'utilisateur affiché
   - Cookie remember_me créé (F12 > Application > Cookies)

**Test 3: Protection des routes**

1. Se déconnecter
2. Essayer d'accéder directement : `http://localhost:8000/fr/profile`
3. ✅ **Vérifier** :
   - Redirection automatique vers `/fr/login`
   - Message "Vous devez être connecté pour accéder à cette page"

**Test 4: Remember Me**

1. Se connecter avec "Se souvenir de moi" coché
2. Fermer le navigateur complètement
3. Rouvrir le navigateur et aller à `http://localhost:8000/fr/profile`
4. ✅ **Vérifier** :
   - Toujours connecté (pas de redirection)
   - Session restaurée automatiquement

**Test 5: Modification du profil**

1. Se connecter
2. Aller à : `http://localhost:8000/fr/profile/edit`
3. Modifier le prénom : "Jean-Pierre"
4. Soumettre
5. ✅ **Vérifier** :
   - Message de succès
   - Prénom mis à jour affiché sur `/fr/profile`

### 3.5 Vérifier les logs de sécurité

```bash
# Voir les logs en temps réel
tail -f var/log/dev.log | grep security

# Rechercher les tentatives de connexion
grep "Authenticated" var/log/dev.log
grep "login" var/log/dev.log
```

---

## 4. Tests du Formulaire de Contact

### 4.1 Test du formulaire de contact complet

**Test 1: Soumission réussie**

1. Aller à : `http://localhost:8000/fr/contact`
2. Remplir le formulaire :
   - Nom complet : Martin Durand
   - Email : martin.durand@example.com
   - Téléphone : +33612345678
   - Entreprise : Test Corp
   - Sujet : Demande d'information
   - Message : "Je souhaite obtenir plus d'informations sur vos services."
3. Soumettre
4. ✅ **Vérifier** :
   - Message de confirmation affiché
   - Pas d'erreur PHP
   - Redirection ou message de succès

**Test 2: Validation des champs obligatoires**

1. Aller à : `http://localhost:8000/fr/contact`
2. Laisser tous les champs vides
3. Soumettre
4. ✅ **Vérifier** :
   - Messages d'erreur pour champs obligatoires :
     - "Le nom est obligatoire"
     - "L'email est obligatoire"
     - "Le message est obligatoire"
   - Formulaire reste rempli avec les valeurs (pas de perte de données)

**Test 3: Validation du format email**

1. Remplir le formulaire avec un email invalide : "pas-un-email"
2. Soumettre
3. ✅ **Vérifier** :
   - Message : "Veuillez saisir une adresse email valide"

**Test 4: Validation de la longueur du message**

1. Remplir le formulaire avec un message de moins de 10 caractères : "Test"
2. Soumettre
3. ✅ **Vérifier** :
   - Message : "Le message doit contenir au moins 10 caractères"

### 4.2 Vérifier l'enregistrement en base de données

```bash
# Vérifier que le message a été enregistré
php bin/console doctrine:query:sql "SELECT * FROM contact_message ORDER BY created_at DESC LIMIT 5"
```

**Résultat attendu** :
- Une ligne avec les données du formulaire
- Status = "new"
- created_at = date/heure actuelle
- updated_at = date/heure actuelle

### 4.3 Tester les méthodes du repository

Créer un fichier de test temporaire `tests/Repository/ContactMessageRepositoryTest.php` :

```php
<?php

namespace App\Tests\Repository;

use App\Repository\ContactMessageRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ContactMessageRepositoryTest extends KernelTestCase
{
    private ContactMessageRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = static::getContainer()->get(ContactMessageRepository::class);
    }

    public function testFindNewMessages(): void
    {
        $newMessages = $this->repository->findNewMessages();
        $this->assertIsArray($newMessages);
        // Devrait retourner les messages avec status = 'new'
    }

    public function testCountNewMessages(): void
    {
        $count = $this->repository->countNewMessages();
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testFindRecent(): void
    {
        $recentMessages = $this->repository->findRecent(5);
        $this->assertIsArray($recentMessages);
        $this->assertLessThanOrEqual(5, count($recentMessages));
    }
}
```

Exécuter le test :

```bash
php bin/phpunit tests/Repository/ContactMessageRepositoryTest.php
```

---

## 5. Tests des Emails

### 5.1 Configuration de Mailtrap (recommandé pour les tests)

1. Créer un compte gratuit sur [Mailtrap.io](https://mailtrap.io)
2. Récupérer les credentials SMTP
3. Configurer dans `.env.local` :

```env
MAILER_DSN=smtp://username:password@smtp.mailtrap.io:2525
```

### 5.2 Test d'envoi d'email de contact

**Test 1: Email de notification admin**

1. Remplir et soumettre le formulaire de contact
2. Aller sur Mailtrap > Inbox
3. ✅ **Vérifier** :
   - Un email reçu à `ADMIN_EMAIL`
   - Sujet : "Nouveau message de contact - [Sujet du formulaire]"
   - Contenu contient :
     - Nom du contact
     - Email du contact
     - Téléphone
     - Entreprise
     - Message
   - Template HTML correctement rendu

**Test 2: Email de confirmation au client**

1. Même formulaire que Test 1
2. Aller sur Mailtrap > Inbox
3. ✅ **Vérifier** :
   - Un email envoyé à l'adresse du formulaire
   - Sujet : "Confirmation de réception de votre message"
   - Contenu contient :
     - Salutation personnalisée avec le nom
     - Récapitulatif du message
     - Délai de réponse indiqué
   - Template HTML correctement rendu

### 5.3 Test d'envoi d'email d'inscription

**Test 1: Email de bienvenue**

1. S'inscrire avec un nouveau compte : `http://localhost:8000/fr/register`
2. Aller sur Mailtrap > Inbox
3. ✅ **Vérifier** :
   - Un email reçu à l'adresse d'inscription
   - Sujet : "Bienvenue sur ModusCap"
   - Contenu contient :
     - Prénom de l'utilisateur
     - Lien vers le profil
     - Informations utiles
   - Template HTML correctement rendu

### 5.4 Vérifier les logs du mailer

```bash
# Voir les emails envoyés en dev
tail -f var/log/dev.log | grep mailer

# Ou rechercher tous les envois
grep "Email" var/log/dev.log
```

### 5.5 Test en mode production (simulation)

```bash
# Passer temporairement en mode prod
APP_ENV=prod php bin/console cache:clear

# Tester l'envoi
# Les emails doivent être envoyés de manière asynchrone si configuré
```

---

## 6. Tests des Traductions

### 6.1 Test des traductions françaises

**Test 1: Page de contact en français**

1. Aller à : `http://localhost:8000/fr/contact`
2. ✅ **Vérifier les libellés traduits** :
   - "Nom complet"
   - "Adresse email"
   - "Numéro de téléphone"
   - "Votre entreprise"
   - "Sujet de votre message"
   - "Votre message"
   - "Envoyer le message"

**Test 2: Messages de validation en français**

1. Soumettre le formulaire vide
2. ✅ **Vérifier les messages** :
   - "Le nom est obligatoire"
   - "L'adresse email est obligatoire"
   - "Le message est obligatoire"

**Test 3: Messages de succès en français**

1. Soumettre un formulaire valide
2. ✅ **Vérifier** :
   - "Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais."

### 6.2 Test des traductions anglaises

**Test 1: Changer de langue**

1. Cliquer sur le sélecteur de langue
2. Choisir "English"
3. ✅ **Vérifier** :
   - URL devient `http://localhost:8000/en/contact`
   - Tous les libellés en anglais

**Test 2: Page de contact en anglais**

1. Aller à : `http://localhost:8000/en/contact`
2. ✅ **Vérifier les libellés traduits** :
   - "Full Name"
   - "Email Address"
   - "Phone Number"
   - "Your Company"
   - "Subject"
   - "Your Message"
   - "Send Message"

**Test 3: Messages de validation en anglais**

1. Soumettre le formulaire vide
2. ✅ **Vérifier les messages** :
   - "Name is required"
   - "Email is required"
   - "Message is required"

### 6.3 Vérifier les traductions manquantes

```bash
# Lister toutes les clés de traduction utilisées
php bin/console debug:translation --only-missing fr

# Vérifier les traductions dupliquées
php bin/console debug:translation --only-unused fr
```

**Résultat attendu** :
- Aucune clé manquante (thanks to the 36 keys added!)

### 6.4 Test des traductions dans les emails

**Test 1: Email en français**

1. Soumettre le formulaire de contact en français
2. Vérifier l'email reçu sur Mailtrap
3. ✅ **Vérifier** :
   - Contenu entièrement en français
   - Pas de clés de traduction brutes (ex: `contact.email.greeting`)

**Test 2: Email en anglais**

1. Aller à `http://localhost:8000/en/contact`
2. Soumettre le formulaire
3. Vérifier l'email reçu
4. ✅ **Vérifier** :
   - Contenu entièrement en anglais

---

## 7. Tests du Système de Devis

### 7.1 Test de création de devis

**Test 1: Devis simple**

1. Se connecter avec un compte utilisateur
2. Aller sur une page produit : `http://localhost:8000/fr/products/1`
3. Personnaliser le produit :
   - Choisir une couleur
   - Indiquer une quantité : 100
   - Ajouter des options
4. Cliquer sur "Demander un devis"
5. ✅ **Vérifier** :
   - Message de succès : "Votre devis a été créé avec succès"
   - Redirection vers la page de détail du devis

**Test 2: Vérifier les détails du devis**

1. Après création, vérifier la page de détail du devis
2. ✅ **Vérifier** :
   - Status : "En attente" (pending)
   - Indicateurs de progression affichés :
     - ✓ En attente - Votre demande est en cours de traitement
     - ○ En préparation
     - ○ Envoyé
     - ○ Validé
   - Informations produit correctes
   - Quantité correcte
   - Personnalisations affichées

**Test 3: Sans personnalisation**

1. Aller sur une page produit
2. Cliquer directement sur "Demander un devis" sans personnaliser
3. ✅ **Vérifier** :
   - Message d'erreur : "Aucune personnalisation trouvée pour ce devis"
   - Pas de création de devis en base

### 7.2 Vérifier l'enregistrement en base

```bash
php bin/console doctrine:query:sql "SELECT * FROM quote ORDER BY created_at DESC LIMIT 5"
```

**Résultat attendu** :
- Ligne avec user_id, product_id, status='pending', created_at

### 7.3 Test des traductions des status

**Test en français** :
1. Voir un devis : `http://localhost:8000/fr/quotes/1`
2. ✅ **Vérifier les labels** :
   - "En attente" / "Votre demande est en cours de traitement"
   - "En préparation" / "Nous préparons votre devis personnalisé"
   - "Envoyé" / "Le devis a été envoyé à votre adresse email"
   - "Validé" / "Votre devis a été accepté"

**Test en anglais** :
1. Voir un devis : `http://localhost:8000/en/quotes/1`
2. ✅ **Vérifier les labels** :
   - "Pending" / "Your request is being processed"
   - "In Progress" / "We are preparing your custom quote"
   - "Sent" / "The quote has been sent to your email"
   - "Validated" / "Your quote has been accepted"

---

## 8. Checklist de Validation Finale

### ✅ Configuration

- [ ] `.env.local` configuré avec APP_SECRET sécurisé
- [ ] DATABASE_URL configuré correctement
- [ ] MAILER_DSN configuré (Mailtrap ou SMTP)
- [ ] `app.supported_locales` défini dans `services.yaml`
- [ ] `security.yaml` chargé sans erreur
- [ ] Cache Symfony vidé : `php bin/console cache:clear`

### ✅ Base de données

- [ ] Migration créée pour `ContactMessage`
- [ ] Migration exécutée sans erreur
- [ ] Table `contact_message` créée avec toutes les colonnes
- [ ] Table `user` existe et fonctionnelle
- [ ] Schéma validé : `php bin/console doctrine:schema:validate`

### ✅ Authentification

- [ ] Inscription d'un nouvel utilisateur fonctionne
- [ ] Email de bienvenue envoyé
- [ ] Connexion avec email/password fonctionne
- [ ] "Se souvenir de moi" fonctionne
- [ ] Déconnexion fonctionne
- [ ] Protection des routes `/profile` fonctionne
- [ ] Modification du profil fonctionne

### ✅ Formulaire de Contact

- [ ] Formulaire affiché sans erreur
- [ ] Validation des champs obligatoires fonctionne
- [ ] Validation du format email fonctionne
- [ ] Validation de la longueur du message fonctionne
- [ ] Soumission réussie enregistre en base
- [ ] Email de notification admin envoyé
- [ ] Email de confirmation client envoyé
- [ ] Status "new" assigné par défaut

### ✅ Traductions

- [ ] Traductions françaises complètes (0 clé manquante)
- [ ] Traductions anglaises complètes (0 clé manquante)
- [ ] Changement de langue fonctionne
- [ ] Emails traduits selon la locale
- [ ] Messages de validation traduits
- [ ] Messages de succès/erreur traduits

### ✅ Système de Devis

- [ ] Création de devis avec personnalisation fonctionne
- [ ] Erreur si aucune personnalisation
- [ ] Status "pending" par défaut
- [ ] Indicateurs de progression affichés
- [ ] Traductions des status en FR et EN
- [ ] Page de détail du devis fonctionnelle

### ✅ Templates

- [ ] Pas de bouton WhatsApp dupliqué sur `products/show.html.twig`
- [ ] Templates d'emails correctement rendus
- [ ] Responsive design fonctionnel
- [ ] CSRF tokens présents sur tous les formulaires

### ✅ Logs et Débogage

- [ ] Pas d'erreur PHP dans `var/log/dev.log`
- [ ] Pas d'erreur SQL dans les logs
- [ ] Profiler Symfony accessible (barre de débogage)
- [ ] Aucun warning Twig

---

## 9. Résolution des Problèmes

### Problème : "Class 'App\Entity\ContactMessage' not found"

**Solution** :
```bash
composer dump-autoload
php bin/console cache:clear
```

### Problème : "Table 'contact_message' doesn't exist"

**Solution** :
```bash
php bin/console doctrine:migrations:migrate
```

### Problème : "Parameter 'app.supported_locales' not found"

**Solution** : ✅ **Déjà corrigé !** Le paramètre est maintenant défini dans `config/services.yaml`.

### Problème : Emails non envoyés

**Solution** :
1. Vérifier `MAILER_DSN` dans `.env.local`
2. Tester la connexion SMTP :
   ```bash
   php bin/console messenger:consume async -vv
   ```
3. Vérifier les logs :
   ```bash
   tail -f var/log/dev.log | grep mailer
   ```

### Problème : Connexion impossible

**Solution** :
1. Vérifier que l'utilisateur existe :
   ```bash
   php bin/console doctrine:query:sql "SELECT * FROM user WHERE email='votre-email@example.com'"
   ```
2. Vérifier le hash du mot de passe :
   ```bash
   php bin/console security:hash-password
   ```
3. Vider le cache :
   ```bash
   php bin/console cache:clear
   ```

### Problème : "Access denied for user"

**Solution** :
1. Vérifier DATABASE_URL dans `.env.local`
2. Vérifier les permissions MySQL :
   ```bash
   mysql -u root -p
   GRANT ALL PRIVILEGES ON moduscap.* TO 'user'@'localhost';
   FLUSH PRIVILEGES;
   ```

### Problème : Traductions non affichées

**Solution** :
```bash
# Vider le cache des traductions
php bin/console cache:clear
php bin/console cache:warmup

# Vérifier les traductions
php bin/console debug:translation fr
```

### Problème : "Remember me" ne fonctionne pas

**Solution** :
1. Vérifier que `APP_SECRET` est défini dans `.env.local`
2. Vider les cookies du navigateur
3. Vérifier la configuration dans `security.yaml` :
   ```yaml
   remember_me:
       secret: '%kernel.secret%'
       lifetime: 604800
   ```

### Problème : CSRF token invalide

**Solution** :
```bash
# Vider le cache et les sessions
php bin/console cache:clear
rm -rf var/cache/* var/sessions/*
```

---

## 📊 Résumé des Tests

| Catégorie | Tests | Status |
|-----------|-------|--------|
| Configuration | 6 | ⏳ À tester |
| Base de données | 5 | ⏳ À tester |
| Authentification | 7 | ⏳ À tester |
| Contact | 8 | ⏳ À tester |
| Traductions | 6 | ⏳ À tester |
| Devis | 3 | ⏳ À tester |
| Emails | 5 | ⏳ À tester |
| **TOTAL** | **40 tests** | **⏳ En attente** |

---

## 🎯 Prochaines Étapes Recommandées

1. **IMMÉDIAT** : Exécuter les migrations
   ```bash
   php bin/console make:migration
   php bin/console doctrine:migrations:migrate
   ```

2. **PRIORITAIRE** : Tester l'authentification complète (inscription, connexion, profil)

3. **IMPORTANT** : Vérifier l'envoi des emails avec Mailtrap

4. **VALIDATION** : Parcourir toute la checklist section par section

5. **PRODUCTION** : Une fois tous les tests verts, préparer le déploiement

---

## 📞 Support

Si vous rencontrez des problèmes non documentés ici :

1. Vérifier les logs : `tail -f var/log/dev.log`
2. Activer le mode verbose : `APP_ENV=dev APP_DEBUG=1`
3. Consulter le Symfony Profiler : `http://localhost:8000/_profiler`

---

**Bonne chance avec les tests ! 🚀**
