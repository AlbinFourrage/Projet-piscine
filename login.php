<?php
session_start();

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:8888");
header("Access-Control-Allow-Credentials: true");
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

if (!$data || empty($data["email"]) || empty($data["password"])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Email et mot de passe obligatoires."
    ]);
    exit;
}

$email = strtolower(trim($data["email"]));
$password = (string) $data["password"];

try {
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, password_hash, role
        FROM users
        WHERE email = :email
        LIMIT 1
    ");
    $stmt->execute([":email" => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user["password_hash"])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Email ou mot de passe incorrect."
        ]);
        exit;
    }

    $_SESSION["user_id"] = (int) $user["id"];
    $_SESSION["user_role"] = $user["role"];
    $_SESSION["user_email"] = $user["email"];

    echo json_encode([
        "success" => true,
        "message" => "Connexion réussie.",
        "user" => [
            "id" => (int) $user["id"],
            "first_name" => $user["first_name"],
            "last_name" => $user["last_name"],
            "email" => $user["email"],
            "role" => $user["role"]
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de la connexion."
    ]);
}
?>
