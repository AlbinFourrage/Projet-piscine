<?php
session_start();

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:8888");
header("Access-Control-Allow-Credentials: true");

require_once "../config/database.php";
require_once "../middleware/auth.php";

requireRole(["seller", "admin"]);

$sellerId = (int) $_SESSION["user_id"];

try {
    $stmt = $pdo->prepare("
        SELECT id, title, brand, model, year, price, sale_type, status, created_at
        FROM cars
        WHERE seller_id = :seller_id
        ORDER BY created_at DESC
    ");
    $stmt->execute([":seller_id" => $sellerId]);

    echo json_encode([
        "success" => true,
        "sales" => $stmt->fetchAll()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors du chargement des ventes."
    ]);
}
?>
