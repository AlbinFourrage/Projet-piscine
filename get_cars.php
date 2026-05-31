<?php
session_start();

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:8888");
header("Access-Control-Allow-Credentials: true");

require_once "../config/database.php";

$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$maxPrice = isset($_GET["max_price"]) ? trim($_GET["max_price"]) : "";
$fuel = isset($_GET["fuel"]) ? trim($_GET["fuel"]) : "";
$condition = isset($_GET["condition"]) ? trim($_GET["condition"]) : "";
$sort = isset($_GET["sort"]) ? trim($_GET["sort"]) : "recent";

$where = ["cars.status = 'active'"];
$params = [];

if ($search !== "") {
    $where[] = "(cars.title LIKE :search OR cars.brand LIKE :search OR cars.model LIKE :search)";
    $params[":search"] = "%" . $search . "%";
}

if ($maxPrice !== "" && is_numeric($maxPrice)) {
    $where[] = "cars.price <= :max_price";
    $params[":max_price"] = (float) $maxPrice;
}

if ($fuel !== "") {
    $where[] = "cars.fuel = :fuel";
    $params[":fuel"] = $fuel;
}

if ($condition !== "") {
    $where[] = "cars.car_condition = :car_condition";
    $params[":car_condition"] = $condition;
}

$orderBy = "cars.created_at DESC";

switch ($sort) {
    case "price_asc": $orderBy = "cars.price ASC"; break;
    case "price_desc": $orderBy = "cars.price DESC"; break;
    case "year_desc": $orderBy = "cars.year DESC"; break;
    case "mileage_asc": $orderBy = "cars.mileage ASC"; break;
}

try {
    $sql = "
        SELECT
            cars.id,
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
            users.last_name AS seller_last_name
        FROM cars
        INNER JOIN users ON users.id = cars.seller_id
        WHERE " . implode(" AND ", $where) . "
        ORDER BY $orderBy
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        "success" => true,
        "cars" => $stmt->fetchAll()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors du chargement du catalogue."
    ]);
}
?>
