CREATE DATABASE IF NOT EXISTS autonova 
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE autonova;

CREATE TABLE IF NOT EXISTS utilisateurs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  Nom VARCHAR(100) NOT NULL,
  Prenom VARCHAR(100) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  mot_de_passe VARCHAR(255) NOT NULL,
  role ENUM('acheteur', 'vendeur', 'admin') NOT NULL DEFAULT 'acheteur',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS voitures (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vendeur_id INT NOT NULL,
  Titre VARCHAR(180) NOT NULL,
  Marque VARCHAR(100) NOT NULL,
  Modele VARCHAR(100) NOT NULL,
  annee INT NOT NULL,
  kilometrage INT NOT NULL,
  fuel ENUM('Essence', 'Diesel', 'Hybride', 'Électrique') NOT NULL,
  voiture_condition ENUM('Neuf', 'Très bon état', 'Bon état', 'Non roulant') NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  sale_type ENUM('direct', 'auction', 'negotiation') NOT NULL,
  description TEXT NOT NULL,
  image_url VARCHAR(500),
  status ENUM('active', 'vendue', 'inactive', 'suspend') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_voitures_vendeur
    FOREIGN KEY (vendeur_id) REFERENCES utilisateurs(id)
    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS actions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  voiture_id INT NOT NULL,
  Prix_depart DECIMAL(10,2) NOT NULL,
  Prix_actuel DECIMAL(10,2) NOT NULL,
  Date_fin DATETIME NOT NULL,
  status ENUM('Ouvert', 'Ferme', 'Annule') NOT NULL DEFAULT 'Ouvert',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_actions_voiture
    FOREIGN KEY (voiture_id) REFERENCES voitures(id)
    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilisateur_id INT NOT NULL,
  message VARCHAR(255) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_utilisateur
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
    ON DELETE CASCADE
);

INSERT INTO utilisateurs (Nom, Prenom, email, mot_de_passe, role)
VALUES (
  'Utilisateur',
  'Test',
  'vendeur@test.fr',
  '$2y$10$exemplehashpedagogique',
  'vendeur'
)
ON DUPLICATE KEY UPDATE email = email;