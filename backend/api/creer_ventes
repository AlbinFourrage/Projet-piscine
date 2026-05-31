<?php
header("Content-Type: application/json; charset=utf-8");
require_once "cors.php";
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Méthode non autorisée."
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Données JSON invalides."
    ]);
    exit;
}

$requiredFields = [
    "title", "brand", "model", "year", "mileage",
    "fuel", "condition", "price", "sale_type", "description"
];

foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || trim((string)$data[$field]) === "") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Le champ $field est obligatoire."
        ]);
        exit;
    }
}

if (!isset($_SESSION["user"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Vous devez être connecté pour créer une vente."]);
    exit;
}

if (!in_array($_SESSION["user"]["role"], ["seller", "admin"], true)) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Seuls les vendeurs peuvent créer une annonce."]);
    exit;
}

$sellerId = (int) $_SESSION["user"]["id"];
$title = trim($data["title"]);
$brand = trim($data["brand"]);
$model = trim($data["model"]);
$year = (int) $data["year"];
$mileage = (int) $data["mileage"];
$fuel = trim($data["fuel"]);
$condition = trim($data["condition"]);
$price = (float) $data["price"];
$saleType = trim($data["sale_type"]);
$description = trim($data["description"]);
$imageUrl = isset($data["image_url"]) ? trim($data["image_url"]) : null;
$auctionStartPrice = isset($data["auction_start_price"]) && $data["auction_start_price"] !== ""
    ? (float) $data["auction_start_price"]
    : null;
$auctionEndDate = isset($data["auction_end_date"]) && $data["auction_end_date"] !== ""
    ? str_replace("T", " ", $data["auction_end_date"]) . ":00"
    : null;

$allowedSaleTypes = ["direct", "auction", "negotiation"];
$allowedFuels = ["Essence", "Diesel", "Hybride", "Électrique"];
$allowedConditions = ["Neuf", "Très bon état", "Bon état", "Non roulant"];

if (!in_array($saleType, $allowedSaleTypes, true)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Type de vente invalide."]);
    exit;
}

if (!in_array($fuel, $allowedFuels, true)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Carburant invalide."]);
    exit;
}

if (!in_array($condition, $allowedConditions, true)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "État invalide."]);
    exit;
}

$currentYear = (int) date("Y") + 1;
if ($year < 1950 || $year > $currentYear) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Année invalide."]);
    exit;
}

if ($mileage < 0 || $price <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Prix ou kilométrage invalide."]);
    exit;
}

if ($saleType === "auction" && ($auctionStartPrice === null || $auctionEndDate === null)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Pour une enchère, indique un prix de départ et une date de fin."
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO cars (
            seller_id, title, brand, model, year, mileage, fuel, car_condition,
            price, sale_type, description, image_url, status, created_at
        ) VALUES (
            :seller_id, :title, :brand, :model, :year, :mileage, :fuel, :car_condition,
            :price, :sale_type, :description, :image_url, 'active', NOW()
        )
    ");

    $stmt->execute([
        ":seller_id" => $sellerId,
        ":title" => $title,
        ":brand" => $brand,
        ":model" => $model,
        ":year" => $year,
        ":mileage" => $mileage,
        ":fuel" => $fuel,
        ":car_condition" => $condition,
        ":price" => $price,
        ":sale_type" => $saleType,
        ":description" => $description,
        ":image_url" => $imageUrl
    ]);

    $carId = (int) $pdo->lastInsertId();

    if ($saleType === "auction") {
        $stmtAuction = $pdo->prepare("
            INSERT INTO auctions (car_id, start_price, current_price, end_date, status, created_at)
            VALUES (:car_id, :start_price, :current_price, :end_date, 'open', NOW())
        ");

        $stmtAuction->execute([
            ":car_id" => $carId,
            ":start_price" => $auctionStartPrice,
            ":current_price" => $auctionStartPrice,
            ":end_date" => $auctionEndDate
        ]);
    }

    $stmtNotification = $pdo->prepare("
        INSERT INTO notifications (user_id, message, is_read, created_at)
        VALUES (:user_id, :message, 0, NOW())
    ");

    $stmtNotification->execute([
        ":user_id" => $sellerId,
        ":message" => "Votre annonce '$title' a été publiée."
    ]);

    $pdo->commit();

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Annonce créée avec succès.",
        "car_id" => $carId
    ]);
} catch (Exception $e) {
    $pdo->rollBack();

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de l'enregistrement de l'annonce."
    ]);
}
?>
