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
$userId = (int)$_SESSION["user_id"];

if (!$data || empty($data["negotiation_id"]) || empty($data["message"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Données invalides."]);
    exit;
}

$nid = (int)$data["negotiation_id"];
$message = trim($data["message"]);
$price = isset($data["proposed_price"]) && $data["proposed_price"] !== "" ? (float)$data["proposed_price"] : null;

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT negotiations.*, cars.seller_id, cars.title FROM negotiations INNER JOIN cars ON cars.id=negotiations.car_id WHERE negotiations.id=:id LIMIT 1 FOR UPDATE");
    $stmt->execute([":id" => $nid]);
    $neg = $stmt->fetch();

    if (!$neg || $neg["status"] !== "open") {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Négociation indisponible."]);
        exit;
    }

    $isBuyer = (int)$neg["buyer_id"] === $userId;
    $isSeller = (int)$neg["seller_id"] === $userId;
    if (!$isBuyer && !$isSeller && $_SESSION["user_role"] !== "admin") {
        $pdo->rollBack();
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Accès refusé."]);
        exit;
    }

    $pdo->prepare("INSERT INTO negotiation_messages (negotiation_id, sender_id, message, proposed_price, created_at) VALUES (:nid, :sid, :msg, :price, NOW())")
        ->execute([":nid" => $nid, ":sid" => $userId, ":msg" => $message, ":price" => $price]);
    $pdo->prepare("UPDATE negotiations SET updated_at=NOW() WHERE id=:id")->execute([":id" => $nid]);

    $receiver = $isBuyer ? (int)$neg["seller_id"] : (int)$neg["buyer_id"];
    $pdo->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (:uid, :msg, 0, NOW())")
        ->execute([":uid" => $receiver, ":msg" => "Nouveau message de négociation pour : ".$neg["title"]]);

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Message envoyé."]);
} catch(Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de l'envoi."]);
}
?>
