CREATE DATABASE IF NOT EXISTS tarmast_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tarmast_db;

CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('acheteur','admin') DEFAULT 'acheteur'
);

-- Migration pour bases existantes :
-- ALTER TABLE utilisateurs ADD COLUMN telephone VARCHAR(20) NOT NULL DEFAULT '' AFTER email;

CREATE TABLE IF NOT EXISTS vaches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    bovin ENUM('vache','veau','velle','genisse','boeuf') DEFAULT 'vache',
    date_naissance DATE,
    age INT,
    poids DECIMAL(6,2),
    description TEXT,
    image VARCHAR(255),
    statut ENUM('disponible','vendue') DEFAULT 'disponible',
    id_admin INT NOT NULL,
    FOREIGN KEY (id_admin) REFERENCES utilisateurs(id)
);

-- Migration pour bases existantes :
-- ALTER TABLE vaches ADD COLUMN bovin ENUM('vache','veau','velle','genisse','boeuf') DEFAULT 'vache' AFTER nom;
-- ALTER TABLE vaches ADD COLUMN date_naissance DATE NULL AFTER bovin;
-- ALTER TABLE vaches ADD COLUMN image VARCHAR(255) NULL AFTER description;

CREATE TABLE IF NOT EXISTS offres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    montant_propose DECIMAL(10,2) NOT NULL,
    date_offre DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente','acceptee','refusee') DEFAULT 'en_attente',
    id_utilisateur INT NOT NULL,
    id_vache INT NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id),
    FOREIGN KEY (id_vache) REFERENCES vaches(id)
);

-- Compte admin par défaut : admin@tarmast.ma / admin123
INSERT INTO utilisateurs (nom, email, mot_de_passe, role)
SELECT 'Admin Jibal', 'admin@tarmast.ma', '$2y$10$QDy6PTk2w8i2mvcrP9oXxu45sJOEcrryKYrgSu0O4aJ1xpF7YV7Tq', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM utilisateurs WHERE email = 'admin@tarmast.ma');
