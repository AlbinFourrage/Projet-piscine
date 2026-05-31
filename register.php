<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit;
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Méthode non autorisée."
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Données JSON invalides."
    ]);
    exit;
}

$requiredFields = ["first_name", "last_name", "email", "password", "confirm_password", "role"];

foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || trim((string)$data[$field]) === "") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Le champ $field est obligatoire."
        ]);
        exit;
    }
}

$firstName = trim($data["first_name"]);
$lastName = trim($data["last_name"]);
$email = strtolower(trim($data["email"]));
$password = (string) $data["password"];
$confirmPassword = (string) $data["confirm_password"];
$role = trim($data["role"]);

$allowedRoles = ["buyer", "seller"];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Adresse email invalide."
    ]);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Le mot de passe doit contenir au moins 8 caractères."
    ]);
    exit;
}

if ($password !== $confirmPassword) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Les mots de passe ne correspondent pas."
    ]);
    exit;
}

if (!in_array($role, $allowedRoles, true)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Rôle invalide."
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([":email" => $email]);

    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Un compte existe déjà avec cette adresse email."
        ]);
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, password_hash, role, created_at)
        VALUES (:first_name, :last_name, :email, :password_hash, :role, NOW())
    ");

    $stmt->execute([
        ":first_name" => $firstName,
        ":last_name" => $lastName,
        ":email" => $email,
        ":password_hash" => $passwordHash,
        ":role" => $role
    ]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Compte créé avec succès.",
        "user_id" => (int) $pdo->lastInsertId()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de la création du compte."
    ]);
}
?>
