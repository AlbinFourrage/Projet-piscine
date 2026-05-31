<?php
session_start();

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:8888");
header("Access-Control-Allow-Credentials: true");

require_once "../config/database.php";

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Identifiant de voiture invalide."
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT
            cars.id,
            cars.seller_id,
            cars.title,
            cars.brand,
            cars.model,
            cars.year,
            cars.mileage,
            cars.fuel,
            cars.car_condition,
            cars.price,
            cars.sale_type,
            cars.description,
            cars.image_url,
            cars.status,
            cars.created_at,
            users.first_name AS seller_first_name,
            users.last_name AS seller_last_name,
            users.email AS seller_email
        FROM cars
        INNER JOIN users ON users.id = cars.seller_id
        WHERE cars.id = :id
          AND cars.status = 'active'
        LIMIT 1
    ");

    $stmt->execute([":id" => $id]);
    $car = $stmt->fetch();

    if (!$car) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Voiture introuvable ou annonce inactive."
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "car" => $car
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors du chargement du véhicule."
    ]);
}
?>
