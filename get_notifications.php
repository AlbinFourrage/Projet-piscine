<?php
session_start();

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:8888");
header("Access-Control-Allow-Credentials: true");

require_once "../config/database.php";
require_once "../middleware/auth.php";

requireLogin();

$userId = (int) $_SESSION["user_id"];
$filter = isset($_GET["filter"]) ? trim($_GET["filter"]) : "all";

$where = "user_id = :user_id";
$params = [":user_id" => $userId];

if ($filter === "unread") {
    $where .= " AND is_read = 0";
} elseif ($filter === "read") {
    $where .= " AND is_read = 1";
}

try {
    $stmt = $pdo->prepare("
        SELECT id, message, is_read, created_at
        FROM notifications
        WHERE $where
        ORDER BY created_at DESC
    ");
    $stmt->execute($params);

    $stmtCount = $pdo->prepare("
        SELECT COUNT(*) AS unread_count
        FROM notifications
        WHERE user_id = :user_id
          AND is_read = 0
    ");
    $stmtCount->execute([":user_id" => $userId]);
    $count = $stmtCount->fetch();

    echo json_encode([
        "success" => true,
        "notifications" => $stmt->fetchAll(),
        "unread_count" => (int) $count["unread_count"]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors du chargement des notifications."
    ]);
}
?>
