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
require_once "../middleware/auth.php";

requireLogin();

$userId = (int) $_SESSION["user_id"];

try {
    $stmt = $pdo->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE user_id = :user_id
          AND is_read = 0
    ");
    $stmt->execute([":user_id" => $userId]);

    echo json_encode([
        "success" => true,
        "message" => "Toutes les notifications ont été marquées comme lues."
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de la mise à jour."
    ]);
}
?>
