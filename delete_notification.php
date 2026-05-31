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

$data = json_decode(file_get_contents("php://input"), true);
$userId = (int) $_SESSION["user_id"];

if (!$data || empty($data["id"])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Identifiant invalide."
    ]);
    exit;
}

$id = (int) $data["id"];

try {
    $stmt = $pdo->prepare("
        DELETE FROM notifications
        WHERE id = :id
          AND user_id = :user_id
    ");
    $stmt->execute([
        ":id" => $id,
        ":user_id" => $userId
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Notification supprimée."
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de la suppression."
    ]);
}
?>
