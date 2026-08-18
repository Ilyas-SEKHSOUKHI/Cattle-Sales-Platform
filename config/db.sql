CREATE DATABASE IF NOT EXISTS tarmast_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tarmast_db;

CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    ice VARCHAR(50) NULL,
    role ENUM('acheteur','admin') DEFAULT 'acheteur',
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    verification_token VARCHAR(64) NULL,
    verification_sent_at DATETIME NULL,
    last_login_at DATETIME NULL
);

-- Migration pour bases existantes :
-- ALTER TABLE utilisateurs ADD COLUMN telephone VARCHAR(20) NOT NULL DEFAULT '' AFTER email;
-- ALTER TABLE utilisateurs ADD COLUMN ice VARCHAR(50) NULL AFTER telephone;

CREATE TABLE IF NOT EXISTS vaches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    bovin VARCHAR(100) DEFAULT 'Vache',
    date_naissance DATE,
    age INT,
    poids DECIMAL(6,2),
    description TEXT,
    image TEXT,
    statut ENUM('disponible','vendue') DEFAULT 'disponible',
    date_reprise DATE NULL,
    id_admin INT NOT NULL,
    FOREIGN KEY (id_admin) REFERENCES utilisateurs(id)
);

-- Migration pour bases existantes :
-- ALTER TABLE vaches MODIFY COLUMN bovin VARCHAR(100) DEFAULT 'Vache';
-- ALTER TABLE vaches MODIFY COLUMN image TEXT NULL;
-- ALTER TABLE vaches ADD COLUMN date_naissance DATE NULL AFTER bovin;

CREATE TABLE IF NOT EXISTS offres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    montant_propose DECIMAL(10,2) NOT NULL,
    date_offre DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente','acceptee','refusee') DEFAULT 'en_attente',
    date_reprise DATE NULL,
    id_utilisateur INT NOT NULL,
    id_vache INT NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id),
    FOREIGN KEY (id_vache) REFERENCES vaches(id)
);

CREATE TABLE IF NOT EXISTS factures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_facture VARCHAR(30) UNIQUE NOT NULL,
    id_offre INT NOT NULL UNIQUE,
    id_utilisateur INT NOT NULL,
    id_vache INT NOT NULL,
    montant_ht DECIMAL(10,2) NOT NULL,
    montant_ttc DECIMAL(10,2) NOT NULL,
    tva_taux DECIMAL(5,2) DEFAULT 20.00,
    date_facture DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('payee', 'annulee') DEFAULT 'payee',
    FOREIGN KEY (id_offre) REFERENCES offres(id),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id),
    FOREIGN KEY (id_vache) REFERENCES vaches(id)
);

-- Compte admin par défaut : admin@tarmast.ma / admin123
INSERT INTO utilisateurs (nom, email, mot_de_passe, role)
SELECT 'Admin Jibal', 'admin@tarmast.ma', '$2y$10$QDy6PTk2w8i2mvcrP9oXxu45sJOEcrryKYrgSu0O4aJ1xpF7YV7Tq', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM utilisateurs WHERE email = 'admin@tarmast.ma');

