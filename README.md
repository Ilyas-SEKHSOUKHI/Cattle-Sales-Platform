<p align="center">
  <img src="assets/images/banner-github.png" alt="Ferme Tarmast Banner" width="100%" />
</p>

# 🐄 Ferme Tarmast — Plateforme de vente de bovins

Plateforme web permettant à **Ferme Tarmast**, entreprise laitière, de mettre en vente les bovins de son cheptel. Les acheteurs consultent les annonces et proposent un prix ; l'administrateur accepte ou refuse chaque offre.

Projet réalisé dans le cadre d'un stage chez **Jibal**.

---

## ✨ Fonctionnalités

### 👤 Espace acheteur
- Inscription avec validation par email
- Connexion sécurisée (hachage des mots de passe, gestion de session PHP)
- Consultation des bovins disponibles (vaches, veaux, velles, génisses, bœufs et types personnalisés)
- Détail d'un animal :
  - **Carrousel multi-photos** avec navigation par flèches, compteur et bande de miniatures cliquables
  - **Calcul précis de l'âge en années, mois et jours** pour les veaux/petits, poids, description, statut
- Proposition d'un prix d'achat pour un bovin
- Suivi de ses offres (en attente / acceptée / refusée)
- **Gestion du profil utilisateur & ICE** (saisie et modification de son numéro d'Identifiant Commun de l'Entreprise - ICE)

### ⚙️ Espace administrateur
- Tableau de bord avec indicateurs clés (revenu total, nombre de ventes, cheptel, offres en attente)
- **Consultation instantanée des fiches vaches** dans le tableau de bord et la liste des offres (modal interactive avec photo hero, statut, caractéristiques et description complète)
- Gestion du cheptel :
  - **Upload multi-photos (jusqu'à 5 images par animal)** : zone de glisser-déposer (drag-and-drop), prévisualisation en temps réel et suppression individuelle
  - **Saisie dynamique de la Race & du Type de bovin** : champs à suggestions avec possibilité d'ajouter de nouvelles races/types librement
  - Ajout, modification et suppression d'un bovin avec calcul automatique de l'âge
- Gestion des offres reçues (acceptation avec sélection de la date de récupération ou refus)
- **Historique des ventes et filtres multicritères** :
  - Filtrage par période (Date début / Date fin)
  - Filtrage par acheteur
- **Exportation Excel (.xlsx)** :
  - Génération d'un fichier Excel stylisé, compact et centré
  - Colonnes personnalisées : *Numéro de série, Date, Nom & Prénom, Adresse mail, Téléphone, Produit, Quantité, Montant HT, Montant TTC (TVA 20%)*
- **Système de facturation complet & Impression PDF** :
  - Auto-génération des factures à l'acceptation des offres avec numérotation strictement séquentielle (`FACT-2026-0001`, `FACT-2026-0002`...)
  - **Historique des factures (`admin/factures.php`)** : recherche et archivage permanent de toutes les factures émises
  - **Facture imprimable/téléchargeable en PDF (`admin/voir_facture.php`)** :
    - Logo officiel `iconVache.png`
    - Coordonnées client avec affichage de l'**ICE** (ou génération automatique d'un ICE réaliste à 15 chiffres)
    - Colonne **SÉRIE** de l'animal (ex: `BOV-0042`)
    - Tableau de facturation (*SÉRIE, DÉSIGNATION, QUANTITÉ, PRIX HT, TVA, MONTANT HT, MONTANT TTC*)
    - **Montant total en toutes lettres** (ex: *« Deux mille quatre cents Dirhams »*)
    - Typography harmonisée (`Fraunces` et `Work Sans`) et styles d'impression épurés (masquage de l'URL/périphérie du navigateur via `@page`)
  - **Facture Récapitulative (`admin/facture_recapitulative.php`)** :
    - Regroupement des factures d'un même acheteur sous une seule facture récapitulative
    - 3 modes de récupération : **Par mois**, **Par année**, ou **Par lot** (sélection manuelle par cases à cocher avec vérification dynamique d'acheteur unique)

---

## 🛠️ Stack technique

| Domaine | Technologie |
|---|---|
| Backend | PHP 8.2 (PDO, requêtes préparées) |
| Base de données | MySQL / MariaDB |
| Frontend | HTML5, CSS3, JavaScript (Vanilla, DataTransfer API, Drag & Drop) |
| Typographie | Google Fonts (`Fraunces`, `Work Sans`) |
| Exports | [PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) (`phpoffice/phpspreadsheet`) |
| Emails | [Mailtrap](https://mailtrap.io) (API / SMTP) |
| Gestionnaire de paquets | Composer |

---

## 📁 Structure du projet

```
├── actions/                  # Traitements (login, register, offres, vaches...)
├── admin/                    # Espace administrateur
│   ├── dashboard.php         # Tableau de bord principal (avec aperçu fiches vaches)
│   ├── liste_vaches.php      # Gestion du cheptel
│   ├── ajouter_vache.php     # Formulaire d'ajout (multi-photos, race & bovin dynamiques)
│   ├── modifier_vache.php    # Formulaire de modification (multi-photos, race & bovin dynamiques)
│   ├── offres.php            # Gestion des offres d'achat (avec consultation des fiches vaches)
│   ├── ventes.php            # Historique des ventes & filtres
│   ├── factures.php          # Historique, recherche et sélection par lot des factures
│   ├── voir_facture.php      # Facture individuelle imprimable / export PDF avec logo & total en lettres
│   ├── facture_recapitulative.php # Facture récapitulative pour regrouper les achats d'un même client
│   └── export_ventes_excel.php # Export des ventes sous format Excel (.xlsx)
├── client/                   # Espace acheteur (accueil, détails avec carrousel photos, offres, profil avec ICE)
├── includes/                 # Fonctions partagées, sessions, helpers (gestion multi-images, nombreEnLettres, vacheAgeFormatted...)
├── config/                   # Connexion base de données, config SMTP, schéma SQL (vaches avec VARCHAR/TEXT, factures...)
├── assets/                   # Images statiques et icônes (iconVache.png, banner-github.png...)
├── uploads/                  # Photos des bovins uploadées
├── docs/                     # Diagramme de cas d'utilisation, MCD
├── index.php                 # Page d'accueil publique
├── login.php / register.php  # Authentification
└── composer.json             # Dépendances du projet
```

---

## 🚀 Installation

### Prérequis
- PHP ≥ 8.0 (avec extensions `gd`, `zip`, `mbstring`, `pdo_mysql`)
- MySQL / MariaDB
- Composer
- Un compte [Mailtrap](https://mailtrap.io) (pour l'envoi des emails de confirmation)

### Étapes

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/Ilyas-SEKHSOUKHI/Cattle-Sales-Platform.git
   cd Cattle-Sales-Platform
   ```

2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```

3. **Créer et initialiser la base de données**

   Importer le schéma fourni :
   ```bash
   mysql -u root -p < config/db.sql
   ```

   Cela crée la base `tarmast_db`, les tables `utilisateurs`, `vaches` (compatible multi-photos et races dynamiques), `offres`, `factures`, ainsi qu'un compte administrateur par défaut.

4. **Configurer les variables d'environnement**

   Copier le fichier exemple `.env.example` vers `.env` et ajuster vos paramètres de base de données et d'envoi d'emails (Mailtrap) :
   ```bash
   cp .env.example .env
   ```

   Contenu de `.env` :
   ```env
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=tarmast_db
   DB_USER=root
   DB_PASS=

   SMTP_ENABLED=true
   SMTP_HOST=live.smtp.mailtrap.io
   SMTP_PORT=587
   SMTP_USER=api
   SMTP_PASS=VOTRE_CLE_API_MAILTRAP
   SMTP_ENCRYPTION=tls
   SMTP_FROM_EMAIL=no-reply@tarmast.ma
   SMTP_FROM_NAME="Ferme Tarmast"
   ```

6. **Lancer l'application**
   ```bash
   php -S localhost:8000
   ```

   Ouvrir votre navigateur sur [http://localhost:8000](http://localhost:8000).

---

## 🔑 Compte administrateur par défaut

| Email | Mot de passe |
|---|---|
| `admin@tarmast.ma` | `admin123` |

> ⚠️ À modifier immédiatement après la première connexion.

---

## 🗄️ Modèle de données

- **utilisateurs** — Comptes acheteurs et administrateurs (rôles, statut de vérification par email, téléphone, champ **ICE**).
- **vaches** — Bovins mis en vente (nom/race personnalisée, type de bovin dynamique, date de naissance / âge calculé en ans/mois/jours, poids, description, photos en tableau JSON [jusqu'à 5], statut *disponible/vendue*).
- **offres** — Propositions d'achat soumises par les acheteurs (montant, date, statut *en_attente/acceptee/refusee*, date de récupération).
- **factures** — Factures archivées et numérotées séquentiellement (numéro, offre, acheteur, bovin, montants HT/TTC, date, statut).

---

## 📄 Licence

Projet réalisé dans un cadre pédagogique / stage chez **Jibal**.