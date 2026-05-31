<?php
session_start();

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:8888");
header("Access-Control-Allow-Credentials: true");

require_once "../config/database.php";

if (empty($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Utilisateur non connecté."
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, role
        FROM users
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute([":id" => (int) $_SESSION["user_id"]]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Session invalide."
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
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
        "message" => "Erreur lors de la lecture de session."
    ]);
}
?>
