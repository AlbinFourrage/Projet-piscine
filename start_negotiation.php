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
if (!$data || empty($data["car_id"]) || empty($data["message"]) || empty($data["proposed_price"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Données invalides."]);
    exit;
}

$carId = (int)$data["car_id"];
$buyerId = (int)$_SESSION["user_id"];
$message = trim($data["message"]);
$proposedPrice = (float)$data["proposed_price"];

try {
    $pdo->beginTransaction();

    $stmtCar = $pdo->prepare("SELECT id, seller_id, title, sale_type, status FROM cars WHERE id = :id LIMIT 1 FOR UPDATE");
    $stmtCar->execute([":id" => $carId]);
    $car = $stmtCar->fetch();

    if (!$car || $car["status"] !== "active" || $car["sale_type"] !== "negotiation") {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Cette voiture n'est pas disponible à la négociation."]);
        exit;
    }

    if ((int)$car["seller_id"] === $buyerId) {
        $pdo->rollBack();
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Vous ne pouvez pas négocier votre propre annonce."]);
        exit;
    }

    $stmtExisting = $pdo->prepare("SELECT id FROM negotiations WHERE car_id = :car_id AND buyer_id = :buyer_id LIMIT 1");
    $stmtExisting->execute([":car_id" => $carId, ":buyer_id" => $buyerId]);
    $existing = $stmtExisting->fetch();

    if ($existing) {
        $negotiationId = (int)$existing["id"];
        $pdo->prepare("UPDATE negotiations SET status='open', updated_at=NOW() WHERE id=:id")
            ->execute([":id" => $negotiationId]);
    } else {
        $stmtCreate = $pdo->prepare("INSERT INTO negotiations (car_id, buyer_id, status, created_at, updated_at) VALUES (:car_id, :buyer_id, 'open', NOW(), NOW())");
        $stmtCreate->execute([":car_id" => $carId, ":buyer_id" => $buyerId]);
        $negotiationId = (int)$pdo->lastInsertId();
    }

    $stmtMsg = $pdo->prepare("INSERT INTO negotiation_messages (negotiation_id, sender_id, message, proposed_price, created_at) VALUES (:nid, :sid, :msg, :price, NOW())");
    $stmtMsg->execute([":nid" => $negotiationId, ":sid" => $buyerId, ":msg" => $message, ":price" => $proposedPrice]);

    $stmtNotif = $pdo->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (:uid, :msg, 0, NOW())");
    $stmtNotif->execute([":uid" => (int)$car["seller_id"], ":msg" => "Nouvelle proposition de négociation sur votre annonce : ".$car["title"]]);

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Négociation démarrée.", "negotiation_id" => $negotiationId]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la création de la négociation."]);
}
?>
