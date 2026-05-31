<?php
session_start();
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:8888");
header("Access-Control-Allow-Credentials: true");
require_once "../config/database.php";
require_once "../middleware/auth.php";
requireLogin();

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$userId = (int)$_SESSION["user_id"];
$userRole = $_SESSION["user_role"];

try {
    $stmt = $pdo->prepare("
      SELECT negotiations.*, cars.title, cars.brand, cars.model, cars.price, cars.seller_id,
             buyer.first_name AS buyer_first_name, buyer.last_name AS buyer_last_name,
             seller.first_name AS seller_first_name, seller.last_name AS seller_last_name
      FROM negotiations
      INNER JOIN cars ON cars.id = negotiations.car_id
      INNER JOIN users buyer ON buyer.id = negotiations.buyer_id
      INNER JOIN users seller ON seller.id = cars.seller_id
      WHERE negotiations.id = :id LIMIT 1
    ");
    $stmt->execute([":id" => $id]);
    $neg = $stmt->fetch();

    if (!$neg) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Négociation introuvable."]);
        exit;
    }

    if ((int)$neg["buyer_id"] !== $userId && (int)$neg["seller_id"] !== $userId && $userRole !== "admin") {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Accès refusé."]);
        exit;
    }

    $stmtMsg = $pdo->prepare("
      SELECT negotiation_messages.*, users.first_name, users.last_name
      FROM negotiation_messages
      INNER JOIN users ON users.id = negotiation_messages.sender_id
      WHERE negotiation_id = :id
      ORDER BY created_at ASC
    ");
    $stmtMsg->execute([":id" => $id]);

    echo json_encode(["success" => true, "negotiation" => $neg, "messages" => $stmtMsg->fetchAll(), "current_user_id" => $userId]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors du chargement."]);
}
?>
