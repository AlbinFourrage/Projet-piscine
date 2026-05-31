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

requireRole(["seller", "admin"]);

$data = json_decode(file_get_contents("php://input"), true);
$sellerId = (int) $_SESSION["user_id"];

if (!$data || empty($data["id"]) || empty($data["status"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Données invalides."]);
    exit;
}

$id = (int) $data["id"];
$status = trim($data["status"]);

$allowedStatuses = ["active", "sold", "inactive", "pending"];

if (!in_array($status, $allowedStatuses, true)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Statut invalide."]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE cars
        SET status = :status
        WHERE id = :id
          AND seller_id = :seller_id
    ");

    $stmt->execute([
        ":status" => $status,
        ":id" => $id,
        ":seller_id" => $sellerId
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Statut mis à jour."
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors du changement de statut."]);
}
?>
