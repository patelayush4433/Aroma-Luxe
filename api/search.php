<?php
/**
 * AJAX API for live navigation search
 */
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? sanitize($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $searchString = '%' . $query . '%';
    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.image_url, p.price_50ml, p.discount_50ml, b.name as brand_name 
        FROM `products` p
        LEFT JOIN `brands` b ON p.brand_id = b.id
        WHERE p.name LIKE :query 
           OR p.description LIKE :query 
           OR p.top_notes LIKE :query 
           OR p.middle_notes LIKE :query 
           OR p.base_notes LIKE :query 
           OR b.name LIKE :query
        LIMIT 5
    ");
    $stmt->execute(['query' => $searchString]);
    $results = $stmt->fetchAll();

    $formatted = [];
    foreach ($results as $row) {
        $finalPrice = (float)$row['price_50ml'] - (float)$row['discount_50ml'];
        $formatted[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'brand' => $row['brand_name'] ? $row['brand_name'] : 'AromaLuxe',
            'price' => formatPrice($finalPrice),
            'image' => $row['image_url']
        ];
    }

    echo json_encode($formatted);
} catch (PDOException $e) {
    echo json_encode([]);
}
