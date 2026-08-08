# 🐄 Ferme Tarmast — Plateforme de vente de bovins

Plateforme web permettant à **Ferme Tarmast**, entreprise laitière, de mettre en vente les bovins de son cheptel. Les acheteurs consultent les annonces et proposent un prix ; l'administrateur accepte ou refuse chaque offre.

Projet réalisé dans le cadre d'un stage chez **Jibal**.

🚧 **Work in Progress** — Ce projet est en cours de développement.

---

## ✨ Fonctionnalités

### Espace acheteur
- Inscription avec validation par email
- Connexion sécurisée (hachage des mots de passe, session PHP)
- Consultation des bovins disponibles (vaches, veaux, velles, génisses, bœufs)
- Détail d'un animal (âge, poids, description, photo)
- Proposition d'un prix d'achat
- Suivi de ses offres (en attente / acceptée / refusée)
- Gestion du profil

### Espace administrateur
- Tableau de bord
- Ajout / modification / suppression d'un bovin (avec upload d'image)
- Gestion des offres reçues (acceptation ou refus, avec refus automatique des autres offres en attente sur le même animal)
- Historique des ventes

---

## 🛠️ Stack technique

| Domaine | Technologie |
|---|---|
| Backend | PHP (PDO, requêtes préparées) |
| Base de données | MySQL |
| Frontend | HTML, CSS, JavaScript |
| Emails | [Mailtrap](https://mailtrap.io) (API) |
| Dépendances PHP | Composer |

---

## 📁 Structure du projet

```
├── actions/          # Traitements (login, register, offres, vaches...)
├── admin/            # Pages de l'espace administrateur
├── client/           # Pages de l'espace acheteur
├── includes/         # Fonctions partagées, session, envoi d'emails
├── config/           # Connexion base de données, config SMTP, schéma SQL
├── assets/           # Images statiques
├── uploads/          # Photos des bovins uploadées
├── docs/             # Diagramme de cas d'utilisation, MCD
├── index.php         # Page d'accueil publique
├── login.php / register.php
└── composer.json
```

---

## 🚀 Installation

### Prérequis
- PHP ≥ 8.0
- MySQL
- Composer
- Un compte [Mailtrap](https://mailtrap.io) (pour l'envoi des emails de validation)

### Étapes

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/Ilyas-SEKHSOUKHI/Cattle-Sales-Platform.git
   cd Cattle-Sales-Platform
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   ```

3. **Créer la base de données**

   Importer le schéma fourni :
   ```bash
   mysql -u root -p < config/db.sql
   ```

   Cela crée la base `tarmast_db`, les tables `utilisateurs`, `vaches`, `offres`, ainsi qu'un compte administrateur par défaut.

4. **Configurer la connexion à la base de données**

   Adapter `config/database.php` si besoin (hôte, utilisateur, mot de passe).

5. **Configurer l'envoi d'emails**

   Renseigner vos identifiants Mailtrap dans `config/smtp.php` :
   ```php
   $smtpConfig = [
       'enabled'  => true,
       'host'     => 'live.smtp.mailtrap.io',
       'port'     => 587,
       'username' => 'api',
       'password' => 'VOTRE_CLE_API_MAILTRAP',
       'encryption'  => 'tls',
       'from_email'  => 'no-reply@votredomaine.com',
       'from_name'   => 'Ferme Tarmast',
   ];
   ```

   > ⚠️ Sans domaine vérifié sur Mailtrap, seule l'adresse email associée à votre compte Mailtrap pourra recevoir des emails de test. Utilisez l'**Email Testing (Sandbox)** de Mailtrap pour tester sans restriction de destinataire.

6. **Lancer le serveur**
   ```bash
   php -S localhost:8000
   ```

   Puis ouvrir [http://localhost:8000](http://localhost:8000).

---

## 🔑 Compte administrateur par défaut

| Email | Mot de passe |
|---|---|
| `admin@tarmast.ma` | `admin123` |

> À changer immédiatement après la première connexion.

---

## 🗄️ Modèle de données

- **utilisateurs** — comptes acheteurs et administrateurs, vérification d'email
- **vaches** — bovins mis en vente (nom, type, âge, poids, statut, photo)
- **offres** — propositions de prix des acheteurs sur un bovin, avec statut (en attente / acceptée / refusée)

Voir `docs/bd (MCD).mcd` et `docs/db (MCD) Image.png` pour le modèle conceptuel complet, ainsi que `docs/Diagramme de cas d'utilisation.png` pour les cas d'usage.

---

## ⚠️ Notes de sécurité

- Les mots de passe sont hachés avec `password_hash` / `password_verify`
- Toutes les requêtes SQL utilisent des paramètres préparés (PDO)
- Les données affichées sont échappées via `htmlspecialchars`
- Aucune protection CSRF n'est encore en place — à ajouter avant tout déploiement en production
- Ne jamais committer de vraies clés API ou identifiants dans `config/`

---

## 📄 Licence

Projet réalisé dans un cadre pédagogique / stage. Licence à définir.