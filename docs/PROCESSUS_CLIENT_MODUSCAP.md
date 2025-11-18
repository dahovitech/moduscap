# Guide du Processus Client - MODUSCAP

## Vue d'ensemble du parcours utilisateur

Le processus client de MODUSCAP est conçu pour offrir une expérience fluide depuis la découverte des produits jusqu'à la finalisation de la commande. Le système prend en charge 9 langues et permet la personnalisation complète des produits avec calcul de prix en temps réel.

---

## 1. Découverte et Navigation des Produits

### 1.1 Accès au site web
- **URL d'accueil** : `/{locale}/` (ex: `/fr/`, `/en/`, `/es/`)
- **Auto-détection de langue** : Le système détecte automatiquement la langue préférée du navigateur
- **Sélecteur de langue** : Disponible dans le header pour changer de langue

### 1.2 Navigation des produits
- **Page d'accueil** : Affichage des produits mis en avant
- **Catalogue complet** : Navigation par catégories
- **Recherche** : Recherche par nom, code ou description
- **Filtres** : Filtrage par catégorie, prix, statut (actif/inactif)

### 1.3 Types de produits disponibles
- **Bardages** : Bardage-bois, Bardage-métal, Bardage-composite
- **Couvertures** : Toiture, Isolation, Étanchéité
- **Matériaux** : Bois, Métal, Composite, Verre
- **Équipements** : Ventilation, Éclairage, Sécurité

---

## 2. Personnalisation de Produits

### 2.1 Interface de personnalisation
```
Formulaire de sélection :
┌─────────────────────────────────────────┐
│ Produit sélectionné                      │
├─────────────────────────────────────────┤
│ Option Group 1: [Sélecteur]            │
│ Option Group 2: [Sélecteur]            │
│ Option Group 3: [Sélecteur]            │
├─────────────────────────────────────────┤
│ Quantité: [Input numérique]             │
│ Notes spéciales: [Zone de texte]        │
├─────────────────────────────────────────┤
│ Prix total: [Calcul automatique]        │
│ [Personnaliser] [Ajouter au devis]     │
└─────────────────────────────────────────┘
```

### 2.2 Processus de sélection d'options
1. **Sélection du groupe d'options** : L'utilisateur choisit parmi les groupes disponibles
2. **Choix spécifique** : Sélection d'une option dans le groupe (ex: "Bardage-métal" dans le groupe "Bardage")
3. **Quantité** : Définition du nombre d'unités
4. **Notes personnalisées** : Zone libre pour exigences spéciales

### 2.3 Calcul de prix en temps réel
Le système calcule automatiquement :
- **Prix de base** : Selon la quantité sélectionnée
- **Prix des options** : Somme des options choisies
- **Remises** : Si applicable selon la quantité
- **TVA** : Calcul automatique selon le pays

**Exemple de calcul :**
```
Prix de base (5 unités) : 500€
Options sélectionnées :
  - Bardage-métal : +150€
  - Isolation premium : +200€
Total hors taxe : 850€
TVA (20%) : 170€
Total TTC : 1020€
```

---

## 3. Soumission de Devis

### 3.1 Formulaire de contact client
```
Informations requises :
┌─────────────────────────────────────────┐
│ Nom complet : [Input texte]            │
│ Email : [Input email]                  │
│ Téléphone : [Input téléphone]          │
│ Adresse complète : [Zone de texte]      │
│ Notes du projet : [Zone de texte]       │
│ [Soumettre le devis]                   │
└─────────────────────────────────────────┘
```

### 3.2 Validation des données
**Champs obligatoires :**
- Nom complet (minimum 2 caractères)
- Email (format valide)
- Téléphone (format international)
- Adresse complète
- Notes du projet (optionnel mais recommandé)

**Messages d'erreur :**
- "Le nom doit contenir au moins 2 caractères"
- "Veuillez saisir une adresse email valide"
- "Le numéro de téléphone doit contenir au moins 8 chiffres"

### 3.3 Génération du numéro de commande
Le système génère automatiquement un numéro unique :
- **Format** : `MODUSCAP-YYYYMMDD-XXXX`
- **Exemple** : `MODUSCAP-20241118-0001`
- **Stockage** : Référence conservée pour le suivi

---

## 4. Confirmation et Paiement

### 4.1 Page de confirmation
Après soumission du devis, l'utilisateur accède à :
```
┌─────────────────────────────────────────┐
│ ✅ Devis soumis avec succès            │
│                                     │
│ Numéro de commande : MODUSCAP-...     │
│                                     │
│ Prochaines étapes :                    │
│ 1. Attente d'approbation (24-48h)     │
│ 2. Notification par email             │
│ 3. Paiement et upload de preuve       │
│                                     │
│ [Télécharger le devis PDF]            │
│ [Suivre ma commande]                  │
└─────────────────────────────────────────┘
```

### 4.2 Informations de paiement
Le système affiche :
- **Montant total** à payer
- **Coordonnées bancaires** de l'entreprise
- **Modalités de paiement** (virement, chèque)
- **Conditions** de paiement

### 4.3 Upload de preuve de paiement
**Types de fichiers acceptés :**
- Images : JPEG, PNG, GIF (max 5MB)
- Documents : PDF (max 5MB)

**Processus d'upload :**
1. Sélection du fichier depuis l'ordinateur
2. Validation automatique du format et de la taille
3. Upload sécurisé vers `/uploads/payment_proofs/`
4. Confirmation d'upload réussi

---

## 5. Suivi de Commande

### 5.1 Consultation du statut
**URL de suivi** : `/{locale}/quote/{order_number}/track`

**États de commande disponibles :**
```
État 1: ⏳ PENDING (En attente d'approbation)
  └─ Description: Votre devis est en cours d'examen par nos équipes

État 2: ✅ APPROVED (Approuvé)
  └─ Description: Votre devis a été approuvé, procédez au paiement

État 3: 💳 PAID (Payé)
  └─ Description: Paiement reçu, votre commande est en préparation

État 4: 🔄 PROCESSING (En cours de traitement)
  └─ Description: Votre commande est en cours de production

État 5: 🚚 SHIPPED (Expédié)
  └─ Description: Votre commande a été expédiée

État 6: ✅ DELIVERED (Livré)
  └─ Description: Votre commande a été livrée
```

### 5.2 Notifications par email
Le système envoie des emails automatiques pour :
- **Soumission du devis** : Confirmation de réception
- **Approbation** : Devis approuvé avec détails de paiement
- **Paiement confirmé** : Accusé de réception du paiement
- **Expédition** : Numéro de suivi et estimation de livraison
- **Livraison** : Confirmation de livraison

### 5.3 Interface de suivi utilisateur
```
┌─────────────────────────────────────────┐
│ 📦 Suivi de commande                    │
│                                     │
│ #MODUSCAP-20241118-0001                │
│                                     │
│ ✅ Devis soumis    [2024-11-18 10:30] │
│ ✅ Approuvé        [2024-11-18 14:15] │
│ ✅ Payé           [2024-11-18 16:45] │
│ 🔄 En traitement   [2024-11-19 09:00] │
│ ⏳ En attente     → [Est: 2024-11-22] │
└─────────────────────────────────────────┘
```

---

## 6. Fonctionnalités Spéciales

### 6.1 Support multilingue
**Langues supportées :**
- Français (FR) - Langue par défaut
- Anglais (EN)
- Espagnol (ES)
- Allemand (DE)
- Italien (IT)
- Portugais (PT)
- Arabe (AR)
- Chinois (ZH)
- Japonais (JA)

**Adaptation automatique :**
- Interface utilisateur traduite
- Contenu produit localisé
- Calculs monétaires selon la région
- Formats de date/heure locaux

### 6.2 Responsive design
**Compatibilité :**
- Desktop (1920x1080 et plus)
- Tablet (768px - 1024px)
- Mobile (320px - 767px)

**Fonctionnalités mobiles :**
- Sélection tactile des options
- Upload de fichiers depuis la galerie
- Notifications push pour les mises à jour

### 6.3 Gestion de session
**Données conservées :**
- Personnalisation en cours (30 minutes)
- Préférences linguistiques
- Panier d'achats temporaire

**Sécurité :**
- Session sécurisée avec token CSRF
- Timeout automatique pour inactivité
- Validation serveur de toutes les données

---

## 7. Support Client

### 7.1 Canaux de contact
- **Email** : Contact via formulaire sur le site
- **Téléphone** : Numéro affiché selon la langue
- **Chat en direct** : Si configuré
- **FAQ** : Questions fréquentes par catégorie

### 7.2 Temps de réponse
- **Devis** : 24-48 heures ouvrées
- **Questions techniques** : 4-8 heures
- **Support urgent** : 2-4 heures

### 7.3 Ressources disponibles
- **Documentation produit** : Guides techniques
- **Vidéos tutoriels** : Processus de personnalisation
- **Catalogue PDF** : Specifications techniques
- **Comparatif produits** : Tableaux de comparaison

---

## 8. Sécurité et Confidentialité

### 8.1 Protection des données
**Données collectées :**
- Informations de contact (nom, email, téléphone, adresse)
- Préférences de produit
- Historique de navigation

**Mesures de sécurité :**
- Chiffrement SSL/TLS pour toutes les transactions
- Stockage sécurisé des données personnelles
- Conformité RGPD pour les utilisateurs européens

### 8.2 Gestion des fichiers
**Upload sécurisé :**
- Validation des types de fichiers
- Scan antivirus des uploads
- Stockage dans des répertoires sécurisés
- Nommage unique pour éviter les conflits

---

## 9. Dépannage Courant

### 9.1 Problèmes de navigation
**Problème** : Page d'erreur 404
**Solution** : Vérifier l'URL et la langue sélectionnée

**Problème** : Chargement lent des images
**Solution** : Vider le cache du navigateur

### 9.2 Problèmes de formulaire
**Problème** : Erreur de validation
**Solution** : Vérifier tous les champs obligatoires

**Problème** : Upload échoué
**Solution** : Vérifier la taille et le format du fichier

### 9.3 Problèmes de suivi
**Problème** : Numéro de commande non trouvé
**Solution** : Vérifier l'orthographe exacte ou contacter le support

---

## Conclusion

Le processus client MODUSCAP est optimisé pour offrir une expérience utilisateur fluide et intuitive, de la découverte des produits à la livraison finale. Le système multilingue et les fonctionnalités de suivi en temps réel garantissent une transparence totale tout au long du processus d'achat.

**Points clés à retenir :**
- Interface intuitive et responsive
- Calcul de prix en temps réel
- Support multilingue complet
- Suivi de commande transparent
- Support client dédié
- Sécurité et confidentialité garanties

---

*Document créé le 18/11/2024 - Version 1.0*