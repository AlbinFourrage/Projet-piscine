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

if (!$data || empty($data["negotiation_id"]) || empty($data["status"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Données invalides."]);
    exit;
}

$nid = (int)$data["negotiation_id"];
$status = trim($data["status"]);
if (!in_array($status, ["accepted","rejected","closed"], true)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Statut invalide."]);
    exit;
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT negotiations.buyer_id, cars.seller_id, cars.title FROM negotiations INNER JOIN cars ON cars.id=negotiations.car_id WHERE negotiations.id=:id LIMIT 1 FOR UPDATE");
    $stmt->execute([":id" => $nid]);
    $neg = $stmt->fetch();

    if (!$neg || ((int)$neg["seller_id"] !== $userId && $_SESSION["user_role"] !== "admin")) {
        $pdo->rollBack();
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Seul le vendeur peut accepter ou refuser."]);
        exit;
    }

    $pdo->prepare("UPDATE negotiations SET status=:status, updated_at=NOW() WHERE id=:id")->execute([":status"=>$status, ":id"=>$nid]);

    if ($status === "accepted") {
        $pdo->prepare("UPDATE cars INNER JOIN negotiations ON negotiations.car_id=cars.id SET cars.status='sold' WHERE negotiations.id=:id")->execute([":id"=>$nid]);
    }

    $label = $status === "accepted" ? "acceptée" : ($status === "rejected" ? "refusée" : "fermée");
    $pdo->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (:uid, :msg, 0, NOW())")
        ->execute([":uid"=>(int)$neg["buyer_id"], ":msg"=>"Votre négociation pour ".$neg["title"]." a été ".$label."."]);

    $pdo->commit();
    echo json_encode(["success"=>true, "message"=>"Négociation mise à jour."]);
} catch(Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success"=>false, "message"=>"Erreur lors de la mise à jour."]);
}
?>
