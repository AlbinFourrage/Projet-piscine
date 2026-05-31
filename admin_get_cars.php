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
        SELECT
            cars.id,
            cars.title,
            cars.brand,
            cars.model,
            cars.price,
            cars.sale_type,
            cars.status,
            users.first_name AS seller_first_name,
            users.last_name AS seller_last_name,
            users.email AS seller_email
        FROM cars
        INNER JOIN users ON users.id = cars.seller_id
        ORDER BY cars.created_at DESC
    ");

    echo json_encode(["success" => true, "cars" => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur annonces."]);
}
?>
