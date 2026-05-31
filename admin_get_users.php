<?php
session_start();

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:8888");
header("Access-Control-Allow-Credentials: true");

require_once "../config/database.php";
require_once "../middleware/auth.php";

requireRole(["admin"]);

try {
    $stmt = $pdo->query("
        SELECT id, first_name, last_name, email, role, created_at
        FROM users
        ORDER BY created_at DESC
    ");

    echo json_encode(["success" => true, "users" => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur utilisateurs."]);
}
?>
