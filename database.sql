CREATE DATABASE IF NOT EXISTS autonova
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE autonova;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('buyer', 'seller', 'admin') NOT NULL DEFAULT 'buyer',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cars (
  id INT AUTO_INCREMENT PRIMARY KEY,
  seller_id INT NOT NULL,
  title VARCHAR(180) NOT NULL,
  brand VARCHAR(100) NOT NULL,
  model VARCHAR(100) NOT NULL,
  year INT NOT NULL,
  mileage INT NOT NULL,
  fuel ENUM('Essence', 'Diesel', 'Hybride', 'Électrique') NOT NULL,
  car_condition ENUM('Neuf', 'Très bon état', 'Bon état', 'Non roulant') NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  sale_type ENUM('direct', 'auction', 'negotiation') NOT NULL,
  description TEXT NOT NULL,
  image_url VARCHAR(500),
  status ENUM('active', 'sold', 'inactive', 'pending') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cars_seller
    FOREIGN KEY (seller_id) REFERENCES users(id)
    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS auctions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  car_id INT NOT NULL,
  start_price DECIMAL(10,2) NOT NULL,
  current_price DECIMAL(10,2) NOT NULL,
  end_date DATETIME NOT NULL,
  status ENUM('open', 'closed', 'cancelled') NOT NULL DEFAULT 'open',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_auctions_car
    FOREIGN KEY (car_id) REFERENCES cars(id)
    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message VARCHAR(255) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
);

INSERT INTO users (first_name, last_name, email, password_hash, role)
VALUES (
  'Utilisateur',
  'Test',
  'vendeur@test.fr',
  '$2y$10$exemplehashpedagogique',
  'seller'
)
ON DUPLICATE KEY UPDATE email = email;
