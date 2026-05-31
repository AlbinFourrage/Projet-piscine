USE autonova;

CREATE TABLE IF NOT EXISTS negotiations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  car_id INT NOT NULL,
  buyer_id INT NOT NULL,
  status ENUM('open', 'accepted', 'rejected', 'closed') NOT NULL DEFAULT 'open',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
  FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_negotiation_per_buyer_car (car_id, buyer_id)
);

CREATE TABLE IF NOT EXISTS negotiation_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  negotiation_id INT NOT NULL,
  sender_id INT NOT NULL,
  message TEXT NOT NULL,
  proposed_price DECIMAL(10,2) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (negotiation_id) REFERENCES negotiations(id) ON DELETE CASCADE,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);
