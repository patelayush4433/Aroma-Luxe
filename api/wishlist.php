<?php
/**
 * AJAX API for Wishlist operations
 */
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode([
        'status' => 'not_logged_in',
        'message' => 'Please sign in to add products to your wishlist.'
    ]);
    exit;
}

$customerId = $_SESSION['customer_id'];
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if ($productId <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid product identifier.'
    ]);
    exit;
}

// Check if item exists in wishlist
$stmt = $pdo->prepare("SELECT id FROM `wishlist` WHERE customer_id = ? AND product_id = ?");
$stmt->execute([$customerId, $productId]);
$exists = $stmt->fetch();

if ($exists) {
    // Remove it
    $stmt = $pdo->prepare("DELETE FROM `wishlist` WHERE id = ?");
    $stmt->execute([$exists['id']]);
    
    // Get updated total count
    $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM `wishlist` WHERE customer_id = ?");
    $cntStmt->execute([$customerId]);
    $totalCount = (int)$cntStmt->fetchColumn();

    echo json_encode([
        'status' => 'removed',
        'message' => 'Product removed from your wishlist.',
        'total_items' => $totalCount
    ]);
} else {
    // Add it
    $stmt = $pdo->prepare("INSERT INTO `wishlist` (customer_id, product_id) VALUES (?, ?)");
    $stmt->execute([$customerId, $productId]);
    
    // Get updated total count
    $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM `wishlist` WHERE customer_id = ?");
    $cntStmt->execute([$customerId]);
    $totalCount = (int)$cntStmt->fetchColumn();

    echo json_encode([
        'status' => 'added',
        'message' => 'Product added to your wishlist.',
        'total_items' => $totalCount
    ]);
}
