# 🔐 SYSTÈME D'AUTHENTIFICATION OBLIGATOIRE - IMPLÉMENTATION COMPLÈTE

**Date :** 2025-11-19  
**Auteur :** MiniMax Agent  
**Projet :** MODUSCAP - Système de devis en ligne

---

## 📋 OBJECTIF

Implémenter un système d'authentification **obligatoire** avant qu'un client puisse créer une commande/devis.

**Avant :** Clients anonymes pouvaient créer des devis sans compte  
**Après :** Connexion obligatoire pour accéder au système de devis

---

## 🔧 MODIFICATIONS EFFECTUÉES

### **1. Entity Order - Relation obligatoire vers User**

**Fichier :** `src/Entity/Order.php`

#### Ajout de la propriété `user`

```php
#[ORM\ManyToOne(targetEntity: User::class)]
#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
#[Assert\NotNull(message: 'order.user.required')]
private ?User $user = null;
```

#### Méthodes ajoutées

```php
public function getUser(): ?User
{
    return $this->user;
}

public function setUser(?User $user): static
{
    $this->user = $user;
    return $this;
}
```

**Impact :**
- ✅ Chaque commande est maintenant **liée à un utilisateur** (relation ManyToOne)
- ✅ Suppression en cascade : si un User est supprimé, ses commandes aussi
- ✅ Validation : impossible de créer une commande sans utilisateur

---

### **2. Migration SQL - Ajout de la colonne user_id**

**Fichier :** `migrations/Version20251119_AddUserToOrder.sql`

```sql
-- Add user_id column
ALTER TABLE orders 
ADD COLUMN user_id INT NULL AFTER payment_proof;

-- Add foreign key constraint
ALTER TABLE orders
ADD CONSTRAINT FK_orders_user
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE;

-- Create index for performance
CREATE INDEX IDX_orders_user_id ON orders(user_id);
```

**⚠️ IMPORTANT - Exécution de la migration :**

```bash
cd /workspace/moduscap
mysql -u root -p moduscap_db < migrations/Version20251119_AddUserToOrder.sql
```

**Pour les commandes existantes :**
```sql
-- Option 1: Supprimer les commandes sans utilisateur
DELETE FROM orders WHERE user_id IS NULL;

-- Option 2: Assigner à un utilisateur par défaut
UPDATE orders SET user_id = 1 WHERE user_id IS NULL;

-- Puis rendre la colonne obligatoire
ALTER TABLE orders MODIFY user_id INT NOT NULL;
```

---

### **3. QuoteController - Protection et association User**

**Fichier :** `src/Controller/QuoteController.php`

#### Import ajouté

```php
use Symfony\Component\Security\Http\Attribute\IsGranted;
```

#### Route `/quote/create` protégée

```php
#[Route('/create', name: 'app_quote_create', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_USER')]  // ← AUTHENTIFICATION OBLIGATOIRE
public function create(Request $request): Response
```

#### Association automatique du User à la commande

```php
private function handleQuoteSubmission(...): Response
{
    $order = new Order();
    
    // Associate user to order (MANDATORY)
    $user = $this->getUser();
    $order->setUser($user);
    
    // Pre-fill client info from user profile
    $order->setClientName($clientInfo['name'] ?? ($user->getFirstName() . ' ' . $user->getLastName()));
    $order->setClientEmail($clientInfo['email'] ?? $user->getEmail());
    // ...
}
```

#### Routes protégées avec vérification de propriété

```php
#[Route('/confirmation/{order_number}', name: 'app_quote_confirmation')]
#[IsGranted('ROLE_USER')]
public function confirmation(...): Response
{
    // Security: Check if order belongs to this user
    if ($order->getUser() !== $this->getUser()) {
        throw $this->createAccessDeniedException('You cannot access this order.');
    }
}
```

**Toutes les routes protégées :**
- ✅ `/quote/create` - Création de devis
- ✅ `/quote/confirmation/{order_number}` - Page de confirmation
- ✅ `/quote/{order_number}/upload-payment` - Upload reçu de paiement
- ✅ `/quote/{order_number}/track` - Suivi de commande

---

### **4. AuthController - Utilisation de la relation User**

**Fichier :** `src/Controller/AuthController.php`

#### Dashboard - Récupération des commandes via relation

**Avant :**
```php
$orders = $this->orderRepository->findBy(
    ['clientEmail' => $user->getEmail()],  // ❌ Recherche par email
    ['createdAt' => 'DESC']
);
```

**Après :**
```php
$orders = $this->orderRepository->findBy(
    ['user' => $user],  // ✅ Relation directe
    ['createdAt' => 'DESC']
);
```

#### Mes commandes - Query Builder avec relation

**Avant :**
```php
$queryBuilder = $this->orderRepository->createQueryBuilder('o')
    ->where('o.clientEmail = :email')
    ->setParameter('email', $user->getEmail());
```

**Après :**
```php
$queryBuilder = $this->orderRepository->createQueryBuilder('o')
    ->where('o.user = :user')
    ->setParameter('user', $user);
```

#### Détails commande - Vérification de propriété via relation

**Avant :**
```php
if ($order->getClientEmail() !== $user->getEmail()) {
    throw $this->createAccessDeniedException(...);
}
```

**Après :**
```php
if ($order->getUser() !== $user) {
    throw $this->createAccessDeniedException(...);
}
```

**Routes modifiées :**
- ✅ `/dashboard` - Dashboard client
- ✅ `/my-orders` - Liste des commandes
- ✅ `/my-orders/{orderNumber}` - Détails d'une commande
- ✅ `/my-orders/{orderNumber}/track` - Suivi
- ✅ `/my-orders/{orderNumber}/cancel` - Annulation

---

### **5. ProductController - Redirection vers login**

**Fichier :** `src/Controller/ProductController.php`

#### Vérification avant personnalisation

```php
private function handleProductCustomization(...): Response
{
    // Check if user is logged in - redirect to login if not
    if (!$this->getUser()) {
        // Store customization in session to resume after login
        $formData = $request->request->all();
        $request->getSession()->set('pending_customization', [
            'product_code' => $product->getCode(),
            'form_data' => $formData
        ]);
        
        $this->addFlash('info', $this->translator->trans('controller.auth.login_required_for_quote'));
        return $this->redirectToRoute('app_user_login', ['_locale' => $request->getLocale()]);
    }
    
    // Pre-fill user info
    $user = $this->getUser();
    $clientName = $formData['client_name'] ?? ($user->getFirstName() . ' ' . $user->getLastName());
    $clientEmail = $formData['client_email'] ?? $user->getEmail();
    // ...
}
```

**Workflow :**
1. Client consulte un produit
2. Sélectionne options et remplit formulaire
3. Soumet → **VÉRIFICATION : connecté ?**
   - ❌ Non connecté → Redirection vers `/login` + sauvegarde session
   - ✅ Connecté → Passage au devis

---

## 📊 FLUX UTILISATEUR COMPLET

```
┌─────────────────────────────────────────────────┐
│         CLIENT VISITE LE SITE                   │
└──────────────────┬──────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────┐
│    CONSULTE CATALOGUE PRODUITS                  │
│    (public, pas d'authentification)             │
└──────────────────┬──────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────┐
│    SÉLECTIONNE UN PRODUIT                       │
│    • Choix options                              │
│    • Remplit formulaire                         │
└──────────────────┬──────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────┐
│    SOUMET "DEMANDER UN DEVIS"                   │
│                                                 │
│    ❓ UTILISATEUR CONNECTÉ ?                    │
│                                                 │
│    NON ────────────────┐                       │
│                        │                        │
│    OUI ──────────────┐ │                       │
│                      │ │                        │
└──────────────────────┼─┼────────────────────────┘
                       │ │
         ┌─────────────┘ └──────────────┐
         │                               │
         ▼                               ▼
┌──────────────────┐          ┌──────────────────┐
│ REDIRECTION      │          │ REDIRECTION      │
│ → /login         │          │ → PAGE LOGIN     │
│                  │          │                  │
│ Session sauvegarde│         │ Avec message:   │
│ la customisation │          │ "Connectez-vous │
│                  │          │  pour continuer"│
└────────┬─────────┘          └────────┬─────────┘
         │                             │
         │  Connexion réussie          │
         └──────────┬──────────────────┘
                    │
                    ▼
         ┌──────────────────────────┐
         │  PAGE CRÉATION DEVIS     │
         │  • Infos pré-remplies    │
         │  • Résumé prix           │
         │  • Infos bancaires       │
         └──────────┬───────────────┘
                    │
                    ▼
         ┌──────────────────────────┐
         │  CONFIRMATION DEVIS      │
         │  • Order créée           │
         │  • User associé          │
         │  • Upload reçu possible  │
         └──────────┬───────────────┘
                    │
                    ▼
         ┌──────────────────────────┐
         │  DASHBOARD CLIENT        │
         │  • Historique commandes  │
         │  • Suivi temps réel      │
         │  • Upload paiements      │
         └──────────────────────────┘
```

---

## 🔒 SÉCURITÉ

### **Contrôles d'accès implémentés**

| **Route** | **Protection** | **Vérification propriété** |
|-----------|----------------|----------------------------|
| `/products/*` | ❌ Public | N/A |
| `/quote/create` | ✅ `IsGranted('ROLE_USER')` | Auto (User connecté) |
| `/quote/confirmation/{id}` | ✅ `IsGranted('ROLE_USER')` | ✅ `order.user === currentUser` |
| `/quote/{id}/upload-payment` | ✅ `IsGranted('ROLE_USER')` | ✅ `order.user === currentUser` |
| `/quote/{id}/track` | ✅ `IsGranted('ROLE_USER')` | ✅ `order.user === currentUser` |
| `/dashboard` | ✅ `denyAccessUnlessGranted('ROLE_USER')` | N/A |
| `/my-orders` | ✅ `denyAccessUnlessGranted('ROLE_USER')` | Auto (query filter) |
| `/my-orders/{id}` | ✅ `denyAccessUnlessGranted('ROLE_USER')` | ✅ `order.user === currentUser` |

### **Avantages sécurité**

- ✅ **Isolation des données** : Clients ne voient QUE leurs commandes
- ✅ **Pas de leak d'email** : Pas besoin de chercher par `clientEmail`
- ✅ **Cascade protection** : Suppression User → Suppression Orders
- ✅ **Token-based** : Symfony Security gère l'authentification
- ✅ **CSRF protection** : Forms Symfony avec tokens

---

## 🧪 TESTS À EFFECTUER

### **1. Test création compte**

```bash
# Naviguer vers /register
# Remplir formulaire:
- Prénom: Jean
- Nom: Dupont
- Email: jean.dupont@test.com
- Mot de passe: Test1234!
- Confirmer mot de passe: Test1234!

# Vérifier:
✅ Redirection vers /login
✅ Flash message succès
✅ Email de bienvenue envoyé
```

### **2. Test connexion**

```bash
# Naviguer vers /login
# Remplir:
- Email: jean.dupont@test.com
- Mot de passe: Test1234!

# Vérifier:
✅ Redirection vers /dashboard
✅ Affichage nom utilisateur
✅ Statistiques affichées
```

### **3. Test création devis (connecté)**

```bash
# Étant connecté, naviguer vers un produit
# Sélectionner options et soumettre

# Vérifier:
✅ Redirection vers /quote/create
✅ Infos client pré-remplies
✅ Possibilité de modifier les infos
✅ Création commande avec user_id renseigné
```

### **4. Test création devis (NON connecté)**

```bash
# Se déconnecter
# Naviguer vers un produit
# Sélectionner options et soumettre

# Vérifier:
✅ Redirection vers /login
✅ Message "Connectez-vous pour continuer"
✅ Session sauvegarde customization
✅ Après login → Reprise du processus
```

### **5. Test sécurité - Accès commande autre user**

```bash
# User A connecté avec order ORD-20251119-ABC123
# Essayer d'accéder /quote/confirmation/ORD-20251119-XYZ789 (commande User B)

# Vérifier:
✅ Exception AccessDeniedException
✅ Message: "You cannot access this order"
✅ Code HTTP 403
```

### **6. Test dashboard**

```bash
# Se connecter
# Naviguer vers /dashboard

# Vérifier:
✅ Total commandes affiché
✅ Commandes en attente
✅ Commandes livrées
✅ Total dépensé
✅ Liste dernières 5 commandes
✅ Alerte commandes en attente paiement
```

---

## 📦 FICHIERS MODIFIÉS

| **Fichier** | **Modifications** | **Lignes** |
|-------------|-------------------|------------|
| `src/Entity/Order.php` | Ajout relation `user` + getters/setters | +17 |
| `src/Controller/QuoteController.php` | Protection routes + association User | ~45 |
| `src/Controller/AuthController.php` | Utilisation relation au lieu email | ~25 |
| `src/Controller/ProductController.php` | Redirection login si non connecté | ~30 |
| `migrations/Version20251119_AddUserToOrder.sql` | Migration BDD | +21 |

**Total :** ~138 lignes modifiées/ajoutées

---

## 🚀 DÉPLOIEMENT

### **1. Exécuter la migration SQL**

```bash
cd /workspace/moduscap
mysql -u root -p moduscap_db < migrations/Version20251119_AddUserToOrder.sql

# Ou avec Docker
docker exec -i moduscap_db mysql -u root -pVOTRE_MOT_DE_PASSE moduscap_db < migrations/Version20251119_AddUserToOrder.sql
```

### **2. Gérer les commandes existantes**

Si vous avez des commandes sans `user_id` :

```sql
-- Vérifier
SELECT COUNT(*) FROM orders WHERE user_id IS NULL;

-- Option 1: Supprimer
DELETE FROM orders WHERE user_id IS NULL;

-- Option 2: Assigner à admin
UPDATE orders SET user_id = 1 WHERE user_id IS NULL;

-- Rendre obligatoire
ALTER TABLE orders MODIFY user_id INT NOT NULL;
```

### **3. Vider le cache Symfony**

```bash
cd /workspace/moduscap
php bin/console cache:clear
```

### **4. Tester le système**

Suivre les tests décrits dans la section **🧪 TESTS À EFFECTUER**

---

## ✅ RÉSULTAT FINAL

### **Avant l'implémentation**

❌ Clients anonymes créaient des devis  
❌ Aucune relation User ↔ Order  
❌ Recherche commandes par `clientEmail`  
❌ Pas de sécurité sur l'accès aux commandes  
❌ Pas d'historique centralisé  

### **Après l'implémentation**

✅ **Authentification obligatoire** avant création devis  
✅ **Relation forte** Order → User (foreign key)  
✅ **Sécurité renforcée** : isolation des données par User  
✅ **Dashboard client** avec historique complet  
✅ **UX améliorée** : infos pré-remplies automatiquement  
✅ **Cascade protection** : suppression User → suppression Orders  

---

## 🎯 PROCHAINES ÉTAPES (OPTIONNEL)

1. **Email de rappel** : Envoi auto si client ne finit pas son devis
2. **Favoris produits** : Permettre aux clients de sauvegarder des produits
3. **Adresses multiples** : Gestion de plusieurs adresses de livraison
4. **Historique paiements** : Centraliser tous les reçus téléchargés
5. **Notifications push** : Alertes temps réel sur changement statut

---

**📌 Document généré automatiquement par MiniMax Agent**  
**🔗 Projet MODUSCAP - Branch: dev-edge**
