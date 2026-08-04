CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('acheteur','admin') DEFAULT 'acheteur'
);

CREATE TABLE vaches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    age INT,
    poids DECIMAL(6,2),
    description TEXT,
    statut ENUM('disponible','vendue') DEFAULT 'disponible',
    id_admin INT NOT NULL,
    FOREIGN KEY (id_admin) REFERENCES utilisateurs(id)
);

CREATE TABLE offres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    montant_propose DECIMAL(10,2) NOT NULL,
    date_offre DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente','acceptee','refusee') DEFAULT 'en_attente',
    id_utilisateur INT NOT NULL,
    id_vache INT NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id),
    FOREIGN KEY (id_vache) REFERENCES vaches(id)
);