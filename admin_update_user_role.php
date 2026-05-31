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

if (!$data || empty($data["id"]) || empty($data["role"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Données invalides."]);
    exit;
}

$id = (int) $data["id"];
$role = trim($data["role"]);
$allowedRoles = ["buyer", "seller", "admin"];

if (!in_array($role, $allowedRoles, true)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Rôle invalide."]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
    $stmt->execute([":role" => $role, ":id" => $id]);

    echo json_encode(["success" => true, "message" => "Rôle mis à jour."]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur rôle."]);
}
?>
