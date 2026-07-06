<?php
/**
 * AJAX API for customized perfume creation & cart additions
 */
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$baseNote = isset($_POST['base_note']) ? sanitize($_POST['base_note']) : 'Woody Oud';
$middleNote = isset($_POST['middle_note']) ? sanitize($_POST['middle_note']) : 'Rose Centifolia';
$topNote = isset($_POST['top_note']) ? sanitize($_POST['top_note']) : 'Calabrian Bergamot';
$topRatio = isset($_POST['top_ratio']) ? (int)$_POST['top_ratio'] : 30;
$midRatio = isset($_POST['mid_ratio']) ? (int)$_POST['mid_ratio'] : 40;
$baseRatio = isset($_POST['base_ratio']) ? (int)$_POST['base_ratio'] : 30;
$bottleSize = isset($_POST['bottle_size']) ? sanitize($_POST['bottle_size']) : '50ml';
$bottleStyle = isset($_POST['bottle_style']) ? sanitize($_POST['bottle_style']) : 'Obsidian Night';
$labelText = isset($_POST['label_text']) ? trim(sanitize($_POST['label_text'])) : 'My Signature';

if (empty($labelText)) {
    $labelText = 'My Signature';
}

// Ensure ratios add up to 100%
$totalRatio = $topRatio + $midRatio + $baseRatio;
if ($totalRatio === 0) {
    $topRatio = 30; $midRatio = 40; $baseRatio = 30;
}

// Generate unique SKU for this exact scent configuration
$uniqueString = strtolower($baseNote . '|' . $middleNote . '|' . $topNote . '|' . $bottleStyle . '|' . $labelText);
$skuHash = substr(md5($uniqueString), 0, 10);
$customSku = 'BESPOKE-' . strtoupper($skuHash);

// Set standard pricing
$price30 = 4500.00;
$price50 = 7500.00;
$price100 = 12000.00;

// Determine active price based on size
$activePrice = $price50;
if ($bottleSize === '30ml') $activePrice = $price30;
if ($bottleSize === '100ml') $activePrice = $price100;

try {
    // Check if the custom perfume product already exists in database
    $stmt = $pdo->prepare("SELECT id FROM `products` WHERE sku = ?");
    $stmt->execute([$customSku]);
    $existingProduct = $stmt->fetch();

    if ($existingProduct) {
        $productId = (int)$existingProduct['id'];
    } else {
        // Insert new custom product instance
        $productName = "Bespoke: " . $labelText;
        $description = "A customized, hand-blended perfume. Notes Ratio - Top ({$topRatio}%): {$topNote}, Heart ({$midRatio}%): {$middleNote}, Base ({$baseRatio}%): {$baseNote}. Styled in our {$bottleStyle} flacon.";
        $ingredients = "Natural absolute oils of {$topNote}, {$middleNote}, and {$baseNote}, combined with organic perfumer's denatured alcohol.";
        
        $insert = $pdo->prepare("
            INSERT INTO `products` (
                category_id, brand_id, name, sku, description, ingredients, 
                top_notes, middle_notes, base_notes, 
                price_30ml, price_50ml, price_100ml, 
                discount_30ml, discount_50ml, discount_100ml, 
                stock_30ml, stock_50ml, stock_100ml, 
                rating, image_url, is_featured
            ) VALUES (
                NULL, NULL, ?, ?, ?, ?, 
                ?, ?, ?, 
                ?, ?, ?, 
                0, 0, 0, 
                99, 99, 99, 
                5.00, 'assets/images/custom-bottle.png', 0
            )
        ");
        $insert->execute([
            $productName, $customSku, $description, $ingredients,
            $topNote, $middleNote, $baseNote,
            $price30, $price50, $price100
        ]);
        
        $productId = (int)$pdo->lastInsertId();
    }

    // Add to cart (standard user or guest session cart)
    if (isset($_SESSION['customer_id'])) {
        $customerId = $_SESSION['customer_id'];
        
        $stmt = $pdo->prepare("SELECT id, quantity FROM `cart` WHERE customer_id = ? AND product_id = ? AND size = ?");
        $stmt->execute([$customerId, $productId, $bottleSize]);
        $existingCart = $stmt->fetch();

        if ($existingCart) {
            $newQty = $existingCart['quantity'] + 1;
            $update = $pdo->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
            $update->execute([$newQty, $existingCart['id']]);
        } else {
            $insertCart = $pdo->prepare("INSERT INTO `cart` (customer_id, product_id, size, quantity) VALUES (?, ?, ?, 1)");
            $insertCart->execute([$customerId, $productId, $bottleSize]);
        }
    } else {
        // Guest cart
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $cartKey = $productId . '_' . $bottleSize;
        if (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey]['quantity'] += 1;
        } else {
            $_SESSION['cart'][$cartKey] = [
                'product_id' => $productId,
                'size' => $bottleSize,
                'quantity' => 1
            ];
        }
    }

    // Get updated total items count
    $totalItems = 0;
    if (isset($_SESSION['customer_id'])) {
        $stmt = $pdo->prepare("SELECT SUM(quantity) FROM `cart` WHERE customer_id = ?");
        $stmt->execute([$_SESSION['customer_id']]);
        $totalItems = (int)$stmt->fetchColumn();
    } else {
        foreach ($_SESSION['cart'] as $item) {
            $totalItems += $item['quantity'];
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Custom perfume added to your shopping bag!',
        'total_items' => $totalItems
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
