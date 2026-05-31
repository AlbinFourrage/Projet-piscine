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
requireLogin();

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || empty($data["car_id"]) || empty($data["amount"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Données invalides."]);
    exit;
}

$carId = (int) $data["car_id"];
$bidderId = (int) $_SESSION["user_id"];
$amount = (float) $data["amount"];

try {
    $pdo->beginTransaction();

    $stmtCar = $pdo->prepare("SELECT id, seller_id, price, sale_type, status FROM cars WHERE id = :id LIMIT 1 FOR UPDATE");
    $stmtCar->execute([":id" => $carId]);
    $car = $stmtCar->fetch();

    if (!$car || $car["status"] !== "active" || $car["sale_type"] !== "auction") {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Cette voiture n'est pas disponible aux enchères."]);
        exit;
    }

    if ((int) $car["seller_id"] === $bidderId) {
        $pdo->rollBack();
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Vous ne pouvez pas enchérir sur votre propre annonce."]);
        exit;
    }

    $stmtHighest = $pdo->prepare("SELECT MAX(amount) AS highest_amount FROM bids WHERE car_id = :car_id");
    $stmtHighest->execute([":car_id" => $carId]);
    $highest = $stmtHighest->fetch();
    $minimum = $highest && $highest["highest_amount"] !== null ? (float) $highest["highest_amount"] : (float) $car["price"];

    if ($amount <= $minimum) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Votre enchère doit être supérieure à " . number_format($minimum, 2, ",", " ") . " €."]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO bids (car_id, bidder_id, amount, created_at) VALUES (:car_id, :bidder_id, :amount, NOW())");
    $stmt->execute([":car_id" => $carId, ":bidder_id" => $bidderId, ":amount" => $amount]);

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Enchère enregistrée."]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de l'enchère."]);
}
?>
