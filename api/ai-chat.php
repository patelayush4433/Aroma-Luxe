<?php
/**
 * AJAX API for AI Assistant chatbot responses
 */
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Check if request is POST and message is set
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = isset($input['message']) ? trim(sanitize($input['message'])) : '';

if (empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Message is empty.']);
    exit;
}

$response = '';
$products = [];

$msgLower = strtolower($message);

// Basic NLP Keyword matching logic
if (strpos($msgLower, 'hello') !== false || strpos($msgLower, 'hi') !== false || strpos($msgLower, 'hey') !== false) {
    $response = "Greetings! I am the AromaLuxe Scent Guide. How may I assist you in finding your perfect fragrance today?";
} elseif (strpos($msgLower, 'oud') !== false || strpos($msgLower, 'arabic') !== false || strpos($msgLower, 'amber') !== false || strpos($msgLower, 'woody') !== false || strpos($msgLower, 'oriental') !== false) {
    $response = "Oud, Amber, and Woody notes form the core of our oriental blends. Our masterwork is the <strong>Oud Imperial</strong>, which combines Cambodian Agarwood with honey and saffron. Here are our top woody and amber scents:";
    
    // Fetch woody/oud products
    try {
        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.price_50ml, p.discount_50ml, p.image_url, b.name as brand 
            FROM products p 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE p.name LIKE ? OR p.description LIKE ? OR p.top_notes LIKE ? OR p.middle_notes LIKE ? OR p.base_notes LIKE ? 
            LIMIT 3
        ");
        $term = '%Oud%';
        $stmt->execute([$term, $term, $term, $term, $term]);
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        $products = [];
    }
} elseif (strpos($msgLower, 'women') !== false || strpos($msgLower, 'floral') !== false || strpos($msgLower, 'sweet') !== false || strpos($msgLower, 'rose') !== false || strpos($msgLower, 'jasmine') !== false) {
    $response = "For an elegant, floral, or sweet scent, we highly recommend our womens and unisex collections, especially <strong>Rouge Passion</strong> (sweet & floral notes). Here are some curated fragrances you might love:";
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.price_50ml, p.discount_50ml, p.image_url, b.name as brand 
            FROM products p 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE p.name LIKE ? OR p.description LIKE ? OR p.middle_notes LIKE ? 
            LIMIT 3
        ");
        $term = '%Rose%';
        $stmt->execute([$term, $term, $term]);
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        $products = [];
    }
} elseif (strpos($msgLower, 'men') !== false || strpos($msgLower, 'bold') !== false || strpos($msgLower, 'fresh') !== false || strpos($msgLower, 'citrus') !== false) {
    $response = "Our mens fragrances offer bold, fresh, and citrus profiles (like Bergamot, Grapefruit, and Cedarwood). Check out these popular choices:";
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.price_50ml, p.discount_50ml, p.image_url, b.name as brand 
            FROM products p 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE p.name LIKE ? OR p.description LIKE ? OR p.top_notes LIKE ? 
            LIMIT 3
        ");
        $term = '%Oud%'; // fallback to list top perfumes
        $stmt->execute([$term, $term, $term]);
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        $products = [];
    }
} elseif (strpos($msgLower, 'coupon') !== false || strpos($msgLower, 'discount') !== false || strpos($msgLower, 'code') !== false || strpos($msgLower, 'offer') !== false) {
    $response = "You can use code <strong>LUXURY10</strong> at the checkout page to get an instant 10% discount on your order! Also, refer your friends via your referral code to earn 50 loyalty points.";
} elseif (strpos($msgLower, 'shipping') !== false || strpos($msgLower, 'delivery') !== false || strpos($msgLower, 'track') !== false) {
    $response = "We offer free standard shipping on orders above ₹8,350. You can track your active orders by visiting your <a href='track-order.php' class='text-warning text-decoration-underline'>Order Tracking</a> page or directly under your profile tab.";
} elseif (strpos($msgLower, 'return') !== false || strpos($msgLower, 'refund') !== false || strpos($msgLower, 'policy') !== false) {
    $response = "AromaLuxe offers a 30-day return policy for unopened items in their original packaging. Simply contact our support chat at +91 7284077032 or open a return ticket.";
} elseif (strpos($msgLower, 'customize') !== false || strpos($msgLower, 'make my own') !== false || strpos($msgLower, 'bespoke') !== false || strpos($msgLower, 'custom scent') !== false || strpos($msgLower, 'create perfume') !== false) {
    $response = "You can create your own custom signature fragrance in our Bespoke Studio! Select note profiles, adjust note ratios (top, heart, and base), choose bottle aesthetics, and engrave a personalized name on the gold label. Start creating here: <a href='customize.php' class='text-warning text-decoration-underline'><strong>Bespoke Scent Customizer</strong></a>.";
} elseif (strpos($msgLower, 'best perfume') !== false || strpos($msgLower, 'best-seller') !== false) {
    $response = "Our best-selling fragrances represent the crown jewels of AromaLuxe's collection. Highly popular among perfume collectors, these masterworks are selected for their performance and character:";
    try {
        $stmt = $pdo->query("
            SELECT p.id, p.name, p.price_50ml, p.discount_50ml, p.image_url, b.name as brand 
            FROM products p 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE p.is_best_seller = 1 
            LIMIT 3
        ");
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        $products = [];
    }
} elseif (strpos($msgLower, 'high demand') !== false || strpos($msgLower, 'featured') !== false || strpos($msgLower, 'demand') !== false) {
    $response = "Our high-demand and featured fragrances are currently trending worldwide, representing the most sought-after note profiles of the season:";
    try {
        $stmt = $pdo->query("
            SELECT p.id, p.name, p.price_50ml, p.discount_50ml, p.image_url, b.name as brand 
            FROM products p 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE p.is_featured = 1 
            LIMIT 3
        ");
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        $products = [];
    }
} elseif (strpos($msgLower, 'regular perfume') !== false || strpos($msgLower, 'regular wear') !== false || strpos($msgLower, 'daily wear') !== false || strpos($msgLower, 'normal perfume') !== false) {
    $response = "For regular, daily wear, we recommend our versatile, comforting, and signature designer-house perfumes. These are perfect everyday choices:";
    try {
        $stmt = $pdo->query("
            SELECT p.id, p.name, p.price_50ml, p.discount_50ml, p.image_url, b.name as brand 
            FROM products p 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE p.is_limited_edition = 0 
            ORDER BY p.id ASC 
            LIMIT 3
        ");
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        $products = [];
    }
} else {
    // General keyword recommendation query
    $response = "I searched our perfume library for '$message'. Here are some excellent fragrance selections that match your query:";
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.price_50ml, p.discount_50ml, p.image_url, b.name as brand 
            FROM products p 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE p.name LIKE ? OR p.description LIKE ? OR p.top_notes LIKE ? OR p.middle_notes LIKE ? OR p.base_notes LIKE ? 
            LIMIT 3
        ");
        $term = '%' . $message . '%';
        $stmt->execute([$term, $term, $term, $term, $term]);
        $products = $stmt->fetchAll();
        
        if (count($products) === 0) {
            $response = "I couldn't find any direct matches for '$message'. Could you specify if you are looking for woody, floral, fresh, mens, or womens fragrances? I will be glad to assist!";
        }
    } catch (PDOException $e) {
        $products = [];
    }
}

// Convert formats
$formattedProducts = [];
foreach ($products as $p) {
    $priceFinal = (float)$p['price_50ml'] - (float)$p['discount_50ml'];
    $formattedProducts[] = [
        'id' => (int)$p['id'],
        'name' => htmlspecialchars($p['name']),
        'brand' => htmlspecialchars($p['brand'] ?: 'AromaLuxe'),
        'price' => formatPrice($priceFinal),
        'image' => htmlspecialchars($p['image_url'])
    ];
}

echo json_encode([
    'status' => 'success',
    'response' => $response,
    'products' => $formattedProducts
]);
