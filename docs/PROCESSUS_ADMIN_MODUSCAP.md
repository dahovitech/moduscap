# Guide du Processus Admin - MODUSCAP

## Vue d'ensemble de l'administration

L'interface d'administration MODUSCAP offre un contrôle complet sur tous les aspects du système : gestion des produits, traitement des commandes, configuration multilingue, et monitoring des performances. L'accès admin nécessite une authentification sécurisée avec différents niveaux de permissions.

---

## 1. Authentification et Sécurité Admin

### 1.1 Connexion administrateur
**URL d'accès** : `/admin/login`
**Méthodes d'authentification :**
- Nom d'utilisateur + mot de passe
- Authentification à deux facteurs (2FA) - Optionnel
- Session sécurisée avec timeout automatique

**Rôles administrateur :**
- **Super Admin** : Accès complet au système
- **Admin Produits** : Gestion des produits et options uniquement
- **Admin Commandes** : Traitement des commandes uniquement
- **Admin Support** : Consultation et communication client uniquement

### 1.2 Sécurité et permissions
**Mesures de sécurité :**
- Chiffrement des mots de passe (bcrypt)
- Protection CSRF sur tous les formulaires
- Validation des données côté serveur
- Logs d'audit pour toutes les actions sensibles

**Contrôle d'accès :**
```
┌─────────────────────────────────────────┐
│ Dashboard Admin                        │
├─────────────────────────────────────────┤
│ 👤 Super Admin                          │
│ ├── Gestion produits                    │
│ ├── Gestion commandes                   │
│ ├── Gestion utilisateurs                │
│ ├── Configuration système               │
│ └── Rapports et statistiques            │
│                                         │
│ 👤 Admin Produits                       │
│ ├── Gestion produits                    │
│ └── Gestion options                     │
│                                         │
│ 👤 Admin Commandes                      │
│ ├── Traitement commandes                │
│ └── Communication client                │
└─────────────────────────────────────────┘
```

---

## 2. Gestion des Produits

### 2.1 Interface de gestion des produits
**URL** : `/admin/products`
**Fonctionnalités principales :**

```
┌─────────────────────────────────────────┐
│ Gestion des Produits                    │
├─────────────────────────────────────────┤
│ [+ Nouveau Produit] [Importer] [Exporter] │
├─────────────────────────────────────────┤
│ Rechercher : [________________] [🔍]     │
│ Filtrer par : [Catégorie ▼] [Statut ▼]  │
├─────────────────────────────────────────┤
│ ID | Code    | Nom          | Statut    │
│ 1  | PROD001 | Bardage Bois | ✅ Actif  │
│ 2  | PROD002 | Toiture Metal| ⚠️ Draft  │
│ 3  | PROD003 | Isolation    | ❌ Inactif│
└─────────────────────────────────────────┘
```

### 2.2 Création et édition de produits
**Formulaire de création :**
```php
// Champs obligatoires
- Code produit (unique)
- Statut (Actif/Inactif/Draft)
- Prix de base
- Catégorie

// Traductions par langue (9 langues)
- Nom du produit
- Description courte
- Description détaillée
- Spécifications techniques
```

**Workflow de création :**
1. **Saisie des informations de base**
2. **Traduction dans toutes les langues**
3. **Upload des images/medias**
4. **Association aux catégories**
5. **Configuration des options disponibles**
6. **Validation et publication**

### 2.3 Gestion des médias produits
**Types de fichiers supportés :**
- Images : JPEG, PNG, WebP (max 10MB)
- Documents : PDF (spécifications techniques)
- Vidéos : MP4, WebM (max 100MB)

**Organisation des médias :**
```
/uploads/products/
├── 001_bardage_bois/
│   ├── main_image.jpg
│   ├── gallery_1.jpg
│   ├── gallery_2.jpg
│   └── specifications.pdf
└── 002_toiture_metal/
    ├── main_image.png
    └── demo_video.mp4
```

### 2.4 Gestion des catégories
**Structure hiérarchique :**
```
Catégories principales :
├── Bardage
│   ├── Bardage-bois
│   ├── Bardage-métal
│   └── Bardage-composite
├── Couverture
│   ├── Toiture
│   ├── Isolation
│   └── Étanchéité
└── Matériaux
    ├── Bois
    ├── Métal
    └── Composite
```

**Configuration des catégories :**
- Nom et description traduits
- Image de catégorie
- Ordre d'affichage
- SEO (meta title, description)

---

## 3. Gestion des Options de Produits

### 3.1 Configuration des groupes d'options
**URL** : `/admin/options`
**Types de groupes disponibles :**

```
┌─────────────────────────────────────────┐
│ Groupes d'Options                       │
├─────────────────────────────────────────┤
│ 1. Bardage                              │
│    └── Options : Bardage-bois, Bardage-métal │
│ 2. Couverture                            │
│    └── Options : Toiture, Isolation     │
│ 3. Matériaux                             │
│    └── Options : Bois, Métal, Composite │
│ 4. Équipements                           │
│    └── Options : Ventilation, Éclairage │
└─────────────────────────────────────────┘
```

### 3.2 Création d'options
**Formulaire d'option :**
```php
// Informations de base
- Code option (unique, ex: "BARDAGE_BOIS")
- Prix supplémentaire (peut être négatif)
- Ordre d'affichage

// Traductions (9 langues)
- Nom de l'option
- Description courte
- Description détaillée

// Configuration
- Groupe d'appartenance
- Statut (Actif/Inactif)
- Image optionnelle
- Compatibilités produits
```

### 3.3 Multilingue des options
**Support complet des 9 langues :**

```php
// Exemple pour "Bardage-bois"
'FR' => ['name' => 'Bardage-bois', 'description' => 'Revêtement en bois naturel'],
'EN' => ['name' => 'Wood siding', 'description' => 'Natural wood exterior cladding'],
'ES' => ['name' => 'Revestimiento de madera', 'description' => 'Revestimiento exterior de madera natural'],
'DE' => ['name' => 'Holzverkleidung', 'description' => 'Natürliche Holz-Außenverkleidung'],
'IT' => ['name' => 'Rivestimento in legno', 'description' => 'Rivestimento esterno in legno naturale'],
'PT' => ['name' => 'Revestimento de madeira', 'description' => 'Revestimento exterior em madeira natural'],
'AR' => ['name' => 'لوح خشبي', 'description' => 'ألواح خارجية خشبية طبيعية'],
'ZH' => ['name' => '木质外墙', 'description' => '天然木材外墙装饰'],
'JA' => ['name' => '木材外壁', 'description' => '天然木材外装材']
```

---

## 4. Gestion des Commandes

### 4.1 Dashboard des commandes
**URL** : `/admin/orders`
**Vue d'ensemble :**

```
┌─────────────────────────────────────────┐
│ Gestion des Commandes                   │
├─────────────────────────────────────────┤
│ 📊 Statistiques du jour                 │
│ ├── Nouvelles commandes : 12           │
│ ├── En attente de validation : 8       │
│ ├── Approuvées en attente de paiement : 5│
│ └── Livrées cette semaine : 23         │
├─────────────────────────────────────────┤
│ [Toutes] [En attente] [Approuvées] [Livrées] │
├─────────────────────────────────────────┤
│ #Commande    | Client        | Montant  │ Statut │
│ MODUSCAP-001 | Jean Dupont   | 1,250€  │ ✅ Approuvée │
│ MODUSCAP-002 | Marie Martin  | 890€    │ ⏳ En attente │
│ MODUSCAP-003 | Pierre Durand | 2,100€  │ 🚚 Expédiée  │
└─────────────────────────────────────────┘
```

### 4.2 Traitement des devis
**Processus d'approbation :**

1. **Réception du devis**
   - Notification automatique par email
   - Apparition dans la liste "En attente"
   - Détails complets disponibles

2. **Vérification technique**
   - Validation des spécifications
   - Vérification de la disponibilité
   - Contrôle de la faisabilité

3. **Validation commerciale**
   - Accord sur le prix
   - Vérification des conditions
   - Validation des délais

4. **Approbation finale**
   - Statut → "APPROVED"
   - Envoi notification client
   - Activation du lien de paiement

### 4.3 Détail d'une commande
**Vue complète :**

```
┌─────────────────────────────────────────┐
│ Commande MODUSCAP-20241118-0001         │
├─────────────────────────────────────────┤
│ 📋 Informations générales               │
│ ├── Date création : 18/11/2024 10:30    │
│ ├── Statut : APPROVED                   │
│ ├── Total TTC : 1,250€                  │
│ └── Commentaires : Urgent               │
├─────────────────────────────────────────┤
│ 👤 Informations client                  │
│ ├── Nom : Jean Dupont                   │
│ ├── Email : jean.dupont@email.com       │
│ ├── Téléphone : +33 6 12 34 56 78      │
│ └── Adresse : 123 Rue de la Paix, Paris │
├─────────────────────────────────────────┤
│ 🛍️ Produits commandés                   │
│ ├── Bardage-bois x5 = 500€             │
│ ├── Bardage-métal x2 = 300€            │
│ ├── Isolation premium x3 = 450€        │
│ └── Total produits : 1,250€             │
├─────────────────────────────────────────┤
│ 💳 Paiement                              │
│ ├── Statut : En attente                 │
│ ├── Preuve uploadée : ✅ Oui            │
│ └── Date upload : 18/11/2024 15:45      │
├─────────────────────────────────────────┤
│ [Approuver] [Refuser] [Marquer Payé]    │
│ [Envoyer Email] [Imprimer PDF]          │
└─────────────────────────────────────────┘
```

### 4.4 Actions de traitement
**Actions disponibles :**

- **✅ Approuver** : Validation du devis
- **❌ Refuser** : Rejet avec raison (notification client)
- **💰 Marquer Payé** : Validation du paiement reçu
- **🔄 Traitement** : Démarrage de la production
- **🚚 Expédier** : Validation d'expédition avec suivi
- **✅ Livrer** : Confirmation de livraison
- **📧 Email** : Envoi de notification personnalisée

---

## 5. Gestion des Utilisateurs

### 5.1 Administration des comptes
**URL** : `/admin/users`
**Gestion des accès :**

```
┌─────────────────────────────────────────┐
│ Gestion des Utilisateurs                │
├─────────────────────────────────────────┤
│ [+ Nouvel Utilisateur] [Exporter Liste] │
├─────────────────────────────────────────┤
│ ID | Username  | Email              | Rôle    │
│ 1  | admin     | admin@moduscap.com | Admin   │
│ 2  | support   | support@company.com| Support │
│ 3  | produits  | produits@company.com| Produit │
│ 4  | client1   | client@email.com   | Client  │
└─────────────────────────────────────────┘
```

### 5.2 Configuration des rôles
**Définition des permissions :**

```php
// Super Admin
- Accès complet à toutes les sections
- Gestion des utilisateurs et rôles
- Configuration système avancée
- Accès aux logs et audit

// Admin Produits
- CRUD produits et catégories
- Gestion des options et traductions
- Upload et organisation des médias
- Gestion des prix et remises

// Admin Commandes
- Traitement des devis
- Gestion des statuts de commandes
- Communication client
- Génération de rapports commandes

// Admin Support
- Consultation des commandes
- Envoi de communications
- Gestion des réclamations
- Accès limité aux données sensibles
```

### 5.3 Audit et logs
**Journalisation des actions :**

```php
// Types d'événements logged
- Connexions/déconnexions
- Modifications de données
- Actions sur les commandes
- Upload de fichiers
- Changements de statut
- Erreurs système
```

---

## 6. Configuration du Système

### 6.1 Paramètres généraux
**URL** : `/admin/settings`
**Configuration disponible :**

```
┌─────────────────────────────────────────┐
│ Paramètres Système                      │
├─────────────────────────────────────────┤
│ 🏢 Informations entreprise               │
│ ├── Nom : MODUSCAP                      │
│ ├── Adresse : [Zone de texte]           │
│ ├── Téléphone : [Input]                 │
│ └── Email : [Input]                     │
├─────────────────────────────────────────┤
│ 💳 Informations de paiement              │
│ ├── Nom banque : [Input]                │
│ ├── IBAN : [Input]                      │
│ ├── BIC : [Input]                       │
│ └── Conditions : [Zone de texte]        │
├─────────────────────────────────────────┤
│ 📧 Configuration emails                  │
│ ├── Serveur SMTP : [Input]              │
│ ├── Port : [Input]                      │
│ ├── Username : [Input]                  │
│ └── Mot de passe : [Password]           │
├─────────────────────────────────────────┤
│ 🌍 Langues actives                      │
│ ├── [✓] Français                        │
│ ├── [✓] Anglais                         │
│ ├── [✓] Espagnol                        │
│ └── [✓] Allemand                        │
└─────────────────────────────────────────┘
```

### 6.2 Gestion des langues
**Activation/désactivation des langues :**

```php
// Langues disponibles
'fr' => 'Français',
'en' => 'English', 
'es' => 'Español',
'de' => 'Deutsch',
'it' => 'Italiano',
'pt' => 'Português',
'ar' => 'العربية',
'zh' => '中文',
'ja' => '日本語'

// Configuration par langue
- Actif/Inactif
- Ordre d'affichage
- Traductions par défaut
- Format de devise
- Fuseau horaire
```

### 6.3 Maintenance de la base de données
**Outils disponibles :**

```bash
# Commandes de maintenance
php bin/console app:load-languages    # Charger les langues
php bin/console app:load-products     # Charger les produits
php bin/console app:load-options      # Charger les options
php bin/console app:fix-database      # Réparer la base SQLite
php bin/console app:load-all-data     # Charger toutes les données
```

---

## 7. Rapports et Statistiques

### 7.1 Dashboard analytique
**Métriques principales :**

```
┌─────────────────────────────────────────┐
│ 📊 Tableau de Bord                      │
├─────────────────────────────────────────┤
│ Ventes ce mois       | 45,230€ ↗️ +12%  │
│ Commandes en cours   | 23              │
│ Taux de conversion   | 8.5% ↗️ +2.1%    │
│ Temps traitement     | 2.3 jours ↘️ -0.5│
├─────────────────────────────────────────┤
│ 📈 Graphiques                           │
│ ├── Ventes par mois (12 mois)          │
│ ├── Commandes par statut                │
│ ├── Top produits vendus                 │
│ └── Évolution du panier moyen           │
└─────────────────────────────────────────┘
```

### 7.2 Rapports exportables
**Types de rapports :**

- **📊 Ventes** : CA, nombre de commandes, panier moyen
- **📦 Stock** : Disponibilité produits, alertes rupture
- **👥 Clients** : Nouveaux clients, fidelidad, géographique
- **⏱️ Performance** : Temps de traitement, taux satisfaction
- **🌍 International** : Ventes par langue/pays

**Formats d'export :**
- PDF (rapports formatés)
- Excel (données brutes)
- CSV (intégration externe)

---

## 8. Outils de Dépannage

### 8.1 Diagnostic système
**Vérifications automatiques :**

```php
// Santé du système
✅ Connexion base de données
✅ Permissions fichiers
✅ Upload de médias
✅ Envoi d'emails
⚠️ Espace disque (85% utilisé)
❌ Sauvegarde en retard (3 jours)
```

### 8.2 Logs et debugging
**Niveaux de logs :**

- **ERROR** : Erreurs critiques système
- **WARNING** : Problèmes non bloquants  
- **INFO** : Actions utilisateur importantes
- **DEBUG** : Informations de développement

**Consultation des logs :**
```bash
# Logs applicatifs
tail -f var/log/dev.log

# Logs d'erreur
tail -f var/log/prod.log

# Logs d'audit admin
tail -f var/log/admin_audit.log
```

### 8.3 Réparation automatique
**Problèmes courants et solutions :**

```php
// Erreur SQLite : "database is locked"
php bin/console app:fix-database

// Images manquantes
php bin/console app:repair-media

// Traductions incomplètes
php bin/console app:check-translations

// Cache corrompu
php bin/console cache:clear --env=prod
```

---

## 9. Sauvegarde et Sécurité

### 9.1 Stratégie de sauvegarde
**Fréquence des sauvegardes :**

- **Quotidienne** : Base de données (automatique)
- **Hebdomadaire** : Fichiers médias (programmée)
- **Mensuelle** : Sauvegarde complète (manuelle)

**Contenu sauvegardé :**
```bash
/backup/
├── daily/
│   ├── 2024-11-18/database.sqlite
│   └── 2024-11-18/config_backup.tar.gz
├── weekly/
│   ├── week_46/media_files.tar.gz
│   └── week_46/user_uploads.tar.gz
└── monthly/
    └── 2024-11/complete_backup.tar.gz
```

### 9.2 Sécurité renforcée
**Mesures de protection :**

- **Chiffrement** : SSL/TLS pour toutes les connexions
- **Firewall** : Protection contre les attaques réseau
- **Monitoring** : Détection d'intrusions en temps réel
- **Updates** : Mises à jour sécurité automatiques
- **Access Control** : Authentification forte (2FA)

---

## 10. Procédures d'Urgence

### 10.1 Gestion des pannes
**Procédure d'escalade :**

1. **Niveau 1 - Support interne**
   - Diagnostic automatique
   - Redémarrage services
   - Vérification logs

2. **Niveau 2 - Développeur**
   - Analyse approfondie
   - Correction code
   - Tests validation

3. **Niveau 3 - Expert système**
   - Intervention infrastructure
   - Sauvegarde/restauration
   - Plan de reprise

### 10.2 Communication de crise
**Notifications automatiques :**

```php
// Alertes système
- Email aux administrateurs
- SMS pour pannes critiques
- Notification dashboard admin
- Page statut temporaire (si nécessaire)
```

---

## Conclusion

L'interface d'administration MODUSCAP offre un contrôle complet et granulaire sur tous les aspects du système. La conception modulaire permet une gestion efficace des différentes responsabilités tandis que les outils de monitoring et de diagnostic garantissent une operation continue et sécurisée.

**Points clés de l'administration :**
- Interface intuitive et sécurisée
- Gestion complète des produits et options multilingues
- Traitement efficace des commandes
- Monitoring en temps réel
- Outils de maintenance intégrés
- Sauvegarde et sécurité renforcées

---

*Document créé le 18/11/2024 - Version 1.0*