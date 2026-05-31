<?php
session_start();

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:8888");
header("Access-Control-Allow-Credentials: true");

require_once "../config/database.php";
require_once "../middleware/auth.php";

requireRole(["seller", "admin"]);

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$sellerId = (int) $_SESSION["user_id"];

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Identifiant invalide."]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT *
        FROM cars
        WHERE id = :id
          AND seller_id = :seller_id
        LIMIT 1
    ");

    $stmt->execute([
        ":id" => $id,
        ":seller_id" => $sellerId
    ]);

    $sale = $stmt->fetch();

    if (!$sale) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Annonce introuvable."]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "sale" => $sale
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors du chargement."]);
}
?>
