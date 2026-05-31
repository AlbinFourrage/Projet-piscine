<?php
session_start();

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:8888");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") exit;

require_once "../config/database.php";
require_once "../middleware/auth.php";

requireRole(["admin"]);

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data["id"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Identifiant invalide."]);
    exit;
}

$id = (int) $data["id"];

try {
    $stmt = $pdo->prepare("DELETE FROM cars WHERE id = :id");
    $stmt->execute([":id" => $id]);

    echo json_encode(["success" => true, "message" => "Annonce supprimée."]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur suppression annonce."]);
}
?>
