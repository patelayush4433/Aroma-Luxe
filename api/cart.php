<?php
/**
 * AJAX API for Shopping Cart operations
 */
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$action = isset($_POST['action']) ? sanitize($_POST['action']) : '';
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$size = isset($_POST['size']) ? sanitize($_POST['size']) : '50ml'; // 30ml, 50ml, 100ml
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate size
if (!in_array($size, ['30ml', '50ml', '100ml'])) {
    $size = '50ml';
}

$response = ['status' => 'error', 'message' => 'Invalid action'];

// Get database product info
$product = null;
if ($productId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM `products` WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
}

// Calculate price based on size
function getProductPriceForSize($prod, $sz) {
    if (!$prod) return 0;
    $priceField = 'price_' . $sz;
    $discField = 'discount_' . $sz;
    $price = (float)$prod[$priceField];
    $discount = (float)$prod[$discField];
    return $price - $discount;
}

if ($action === 'add') {
    if (!$product) {
        $response['message'] = 'Product not found.';
        echo json_encode($response);
        exit;
    }

    $price = getProductPriceForSize($product, $size);

    if (isset($_SESSION['customer_id'])) {
        $customerId = $_SESSION['customer_id'];
        // Check if item is already in cart
        $stmt = $pdo->prepare("SELECT id, quantity FROM `cart` WHERE customer_id = ? AND product_id = ? AND size = ?");
        $stmt->execute([$customerId, $productId, $size]);
        $existing = $stmt->fetch();

        if ($existing) {
            $newQty = $existing['quantity'] + $quantity;
            $update = $pdo->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
            $update->execute([$newQty, $existing['id']]);
        } else {
            $insert = $pdo->prepare("INSERT INTO `cart` (customer_id, product_id, size, quantity) VALUES (?, ?, ?, ?)");
            $insert->execute([$customerId, $productId, $size, $quantity]);
        }
    } else {
        // Guest session cart
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $key = $productId . '_' . $size;
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$key] = [
                'product_id' => $productId,
                'size' => $size,
                'quantity' => $quantity
            ];
        }
    }

    // Get updated total items
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

    $response = [
        'status' => 'success',
        'message' => $product['name'] . ' (' . $size . ') added to cart.',
        'total_items' => $totalItems
    ];

} elseif ($action === 'update') {
    if ($quantity < 1) {
        $response['message'] = 'Quantity must be at least 1.';
        echo json_encode($response);
        exit;
    }

    if (isset($_SESSION['customer_id'])) {
        $customerId = $_SESSION['customer_id'];
        $stmt = $pdo->prepare("UPDATE `cart` SET quantity = ? WHERE customer_id = ? AND product_id = ? AND size = ?");
        $stmt->execute([$quantity, $customerId, $productId, $size]);
    } else {
        $key = $productId . '_' . $size;
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['quantity'] = $quantity;
        }
    }

    $response = ['status' => 'success', 'message' => 'Cart updated successfully.'];

} elseif ($action === 'remove') {
    if (isset($_SESSION['customer_id'])) {
        $customerId = $_SESSION['customer_id'];
        $stmt = $pdo->prepare("DELETE FROM `cart` WHERE customer_id = ? AND product_id = ? AND size = ?");
        $stmt->execute([$customerId, $productId, $size]);
    } else {
        $key = $productId . '_' . $size;
        if (isset($_SESSION['cart'][$key])) {
            unset($_SESSION['cart'][$key]);
        }
    }

    $response = ['status' => 'success', 'message' => 'Product removed from cart.'];

} elseif ($action === 'coupon') {
    $couponCode = isset($_POST['code']) ? sanitize($_POST['code']) : '';
    if (empty($couponCode)) {
        $response['message'] = 'Please enter a coupon code.';
        echo json_encode($response);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM `coupons` WHERE code = ? AND is_active = 1 AND expiry_date >= CURDATE()");
    $stmt->execute([$couponCode]);
    $coupon = $stmt->fetch();

    if ($coupon) {
        $_SESSION['coupon'] = [
            'code' => $coupon['code'],
            'type' => $coupon['type'],
            'value' => (float)$coupon['value'],
            'min_spend' => (float)$coupon['min_spend']
        ];
        $response = [
            'status' => 'success',
            'message' => 'Coupon code ' . htmlspecialchars($coupon['code']) . ' applied successfully.'
        ];
    } else {
        unset($_SESSION['coupon']);
        $response['message'] = 'Invalid or expired coupon code.';
    }

} elseif ($action === 'remove_coupon') {
    unset($_SESSION['coupon']);
    $response = ['status' => 'success', 'message' => 'Coupon removed.'];

} elseif ($action === 'giftwrap') {
    $_SESSION['giftwrap'] = isset($_POST['giftwrap']) && $_POST['giftwrap'] == '1' ? 1 : 0;
    $response = ['status' => 'success', 'message' => 'Gift wrapping settings updated.'];
}

echo json_encode($response);
