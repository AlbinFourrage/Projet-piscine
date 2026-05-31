<?php
session_start();

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:8888");
header("Access-Control-Allow-Credentials: true");

require_once "../config/database.php";

if (empty($_SESSION["user_id"])) {
    echo json_encode([
        "success" => true,
        "unread_count" => 0
    ]);
    exit;
}

$userId = (int) $_SESSION["user_id"];

try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS unread_count
        FROM notifications
        WHERE user_id = :user_id
          AND is_read = 0
    ");
    $stmt->execute([":user_id" => $userId]);
    $count = $stmt->fetch();

    echo json_encode([
        "success" => true,
        "unread_count" => (int) $count["unread_count"]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "unread_count" => 0
    ]);
}
?>
