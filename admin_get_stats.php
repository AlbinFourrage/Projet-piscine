<?php
session_start();

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:8888");
header("Access-Control-Allow-Credentials: true");

require_once "../config/database.php";
require_once "../middleware/auth.php";

requireRole(["admin"]);

try {
    echo json_encode([
        "success" => true,
        "stats" => [
            "total_users" => (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            "total_cars" => (int) $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn(),
            "active_cars" => (int) $pdo->query("SELECT COUNT(*) FROM cars WHERE status = 'active'")->fetchColumn(),
            "sold_cars" => (int) $pdo->query("SELECT COUNT(*) FROM cars WHERE status = 'sold'")->fetchColumn()
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur statistiques."]);
}
?>
