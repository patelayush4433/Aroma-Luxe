<?php
/**
 * AJAX API for AI Fragrance Recommendations
 */
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$notes = isset($_GET['notes']) ? sanitize($_GET['notes']) : '';
$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

try {
    if (!empty($notes)) {
        // AI note-matching simulation
        $noteTerm = '%' . $notes . '%';
        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.image_url, p.price_50ml, p.discount_50ml, b.name as brand_name, p.rating
            FROM `products` p
            LEFT JOIN `brands` b ON p.brand_id = b.id
            WHERE p.top_notes LIKE :note OR p.middle_notes LIKE :note OR p.base_notes LIKE :note
            LIMIT 4
        ");
        $stmt->execute(['note' => $noteTerm]);
        $recommended = $stmt->fetchAll();
    } else {
        // Fallback to featured or general items
        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.image_url, p.price_50ml, p.discount_50ml, b.name as brand_name, p.rating
            FROM `products` p
            LEFT JOIN `brands` b ON p.brand_id = b.id
            WHERE p.is_featured = 1 OR p.is_best_seller = 1
            LIMIT 4
        ");
        $stmt->execute();
        $recommended = $stmt->fetchAll();
    }

    $formatted = [];
    foreach ($recommended as $row) {
        $finalPrice = (float)$row['price_50ml'] - (float)$row['discount_50ml'];
        $formatted[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'brand' => $row['brand_name'] ? $row['brand_name'] : 'AromaLuxe',
            'price' => formatPrice($finalPrice),
            'rating' => $row['rating'],
            'image' => $row['image_url']
        ];
    }

    echo json_encode($formatted);
} catch (PDOException $e) {
    echo json_encode([]);
}
