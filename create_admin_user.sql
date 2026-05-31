USE autonova;

-- Compte admin de test
-- Email : admin@autonova.fr
-- Mot de passe : Admin1234

INSERT INTO users (first_name, last_name, email, password_hash, role, created_at)
VALUES (
  'Admin',
  'AutoNova',
  'admin@autonova.fr',
  '$2y$10$wdV/vUkhqcuVc.V/Ccw9Je34q86bJDrKHsO4DR24irv1VCa.kqT86',
  'admin',
  NOW()
)
ON DUPLICATE KEY UPDATE role = 'admin';
