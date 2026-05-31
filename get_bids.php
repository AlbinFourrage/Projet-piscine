<?php
session_start();
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:8888");
header("Access-Control-Allow-Credentials: true");
require_once "../config/database.php";

$carId = isset($_GET["car_id"]) ? (int) $_GET["car_id"] : 0;
if ($carId <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Identifiant invalide."]);
    exit;
}

try {
    $stmtCar = $pdo->prepare("SELECT id, title, brand, model, price, sale_type, seller_id FROM cars WHERE id = :id LIMIT 1");
    $stmtCar->execute([":id" => $carId]);
    $car = $stmtCar->fetch();

    if (!$car || $car["sale_type"] !== "auction") {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Voiture aux enchères introuvable."]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT bids.id, bids.amount, bids.created_at, users.first_name, users.last_name, users.email
        FROM bids
        INNER JOIN users ON users.id = bids.bidder_id
        WHERE bids.car_id = :car_id
        ORDER BY bids.amount DESC, bids.created_at DESC
    ");
    $stmt->execute([":car_id" => $carId]);

    echo json_encode(["success" => true, "car" => $car, "bids" => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur chargement enchères."]);
}
?>
