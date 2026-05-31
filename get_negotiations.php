<?php
session_start();
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:8888");
header("Access-Control-Allow-Credentials: true");
require_once "../config/database.php";
require_once "../middleware/auth.php";
requireLogin();

$userId = (int)$_SESSION["user_id"];
$role = $_SESSION["user_role"];

try {
    if ($role === "seller") {
        $sql = "SELECT negotiations.*, cars.id AS car_id, cars.title, cars.brand, cars.model, cars.price,
                buyer.first_name AS other_first_name, buyer.last_name AS other_last_name
                FROM negotiations
                INNER JOIN cars ON cars.id = negotiations.car_id
                INNER JOIN users buyer ON buyer.id = negotiations.buyer_id
                WHERE cars.seller_id = :uid ORDER BY negotiations.updated_at DESC";
    } else {
        $sql = "SELECT negotiations.*, cars.id AS car_id, cars.title, cars.brand, cars.model, cars.price,
                seller.first_name AS other_first_name, seller.last_name AS other_last_name
                FROM negotiations
                INNER JOIN cars ON cars.id = negotiations.car_id
                INNER JOIN users seller ON seller.id = cars.seller_id
                WHERE negotiations.buyer_id = :uid ORDER BY negotiations.updated_at DESC";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":uid" => $userId]);
    echo json_encode(["success" => true, "negotiations" => $stmt->fetchAll()]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors du chargement des négociations."]);
}
?>
