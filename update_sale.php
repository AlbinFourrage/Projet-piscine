<?php
session_start();

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:8888");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit;
}

require_once "../config/database.php";
require_once "../middleware/auth.php";

requireRole(["seller", "admin"]);

$data = json_decode(file_get_contents("php://input"), true);
$sellerId = (int) $_SESSION["user_id"];

if (!$data || empty($data["id"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Données invalides."]);
    exit;
}

$id = (int) $data["id"];

$requiredFields = [
    "title", "brand", "model", "year", "mileage", "fuel",
    "condition", "price", "sale_type", "status", "description"
];

foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || trim((string)$data[$field]) === "") {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Le champ $field est obligatoire."]);
        exit;
    }
}

try {
    $stmt = $pdo->prepare("
        UPDATE cars
        SET
            title = :title,
            brand = :brand,
            model = :model,
            year = :year,
            mileage = :mileage,
            fuel = :fuel,
            car_condition = :car_condition,
            price = :price,
            sale_type = :sale_type,
            status = :status,
            image_url = :image_url,
            description = :description
        WHERE id = :id
          AND seller_id = :seller_id
    ");

    $stmt->execute([
        ":title" => trim($data["title"]),
        ":brand" => trim($data["brand"]),
        ":model" => trim($data["model"]),
        ":year" => (int) $data["year"],
        ":mileage" => (int) $data["mileage"],
        ":fuel" => trim($data["fuel"]),
        ":car_condition" => trim($data["condition"]),
        ":price" => (float) $data["price"],
        ":sale_type" => trim($data["sale_type"]),
        ":status" => trim($data["status"]),
        ":image_url" => isset($data["image_url"]) ? trim($data["image_url"]) : null,
        ":description" => trim($data["description"]),
        ":id" => $id,
        ":seller_id" => $sellerId
    ]);

    if ($stmt->rowCount() === 0) {
        echo json_encode([
            "success" => true,
            "message" => "Aucune modification détectée ou annonce non trouvée."
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Annonce modifiée avec succès."
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la modification."]);
}
?>
