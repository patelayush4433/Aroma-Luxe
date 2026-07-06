<?php
/**
 * Core Application Config and Utility Functions
 */

require_once __DIR__ . '/db.php';

// Define site URL and paths
define('SITE_URL', 'http://localhost/Perfume');
define('APP_NAME', isset($settings['site_name']) ? $settings['site_name'] : 'AromaLuxe');

// Multi-language system (simulated dictionary)
$languages = [
    'en' => [
        'home' => 'Home',
        'shop' => 'Shop',
        'about' => 'About Us',
        'contact' => 'Contact',
        'blog' => 'Blog',
        'search_placeholder' => 'Search luxury fragrances...',
        'cart' => 'Cart',
        'wishlist' => 'Wishlist',
        'login' => 'Login',
        'register' => 'Register',
        'hero_title' => 'The Essence of Luxury',
        'hero_subtitle' => 'Explore the finest artisanal perfume collection inspired by world-class notes.',
        'shop_now' => 'Shop Now',
        'explore' => 'Explore Collection',
        'add_to_cart' => 'Add to Cart',
        'buy_now' => 'Buy Now',
        'best_sellers' => 'Best Sellers',
        'new_arrivals' => 'New Arrivals',
        'featured_products' => 'Featured Products',
        'bespoke' => 'Bespoke Scent'
    ],
    'fr' => [
        'home' => 'Accueil',
        'shop' => 'Boutique',
        'about' => 'À Propos',
        'contact' => 'Contact',
        'blog' => 'Blog',
        'search_placeholder' => 'Rechercher des parfums de luxe...',
        'cart' => 'Panier',
        'wishlist' => 'Liste de Vœux',
        'login' => 'Connexion',
        'register' => 'S\'inscrire',
        'hero_title' => 'L\'essence du Luxe',
        'hero_subtitle' => 'Explorez la plus belle collection de parfums artisanaux inspirés de notes de classe mondiale.',
        'shop_now' => 'Acheter',
        'explore' => 'Explorer la collection',
        'add_to_cart' => 'Ajouter au Panier',
        'buy_now' => 'Acheter maintenant',
        'best_sellers' => 'Meilleures ventes',
        'new_arrivals' => 'Nouveautés',
        'featured_products' => 'Produits phares',
        'bespoke' => 'Parfum Sur Mesure'
    ],
    'ar' => [
        'home' => 'الرئيسية',
        'shop' => 'المتجر',
        'about' => 'من نحن',
        'contact' => 'اتصل بنا',
        'blog' => 'المدونة',
        'search_placeholder' => 'ابحث عن العطور الفاخرة...',
        'cart' => 'السلة',
        'wishlist' => 'المفضلة',
        'login' => 'تسجيل الدخول',
        'register' => 'إنشاء حساب',
        'hero_title' => 'جوهر الفخامة',
        'hero_subtitle' => 'اكتشف أفضل مجموعة عطور مستوحاة من نوتات عطرية عالمية.',
        'shop_now' => 'تسوق الآن',
        'explore' => 'اكتشف المجموعة',
        'add_to_cart' => 'أضف إلى السلة',
        'buy_now' => 'اشتر الآن',
        'best_sellers' => 'الأكثر مبيعاً',
        'new_arrivals' => 'وصل حديثاً',
        'featured_products' => 'عطور مميزة',
        'bespoke' => 'عطر مخصص'
    ]
];

// Current selected language
if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $languages)) {
    $_SESSION['lang'] = $_GET['lang'];
}
$current_lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';

// Helper function to translate keys
function __($key) {
    global $languages, $current_lang;
    return isset($languages[$current_lang][$key]) ? $languages[$current_lang][$key] : $languages['en'][$key];
}

// Currency Conversion System (simulated exchange rates from INR)
$currencies = [
    'INR' => ['symbol' => '₹', 'rate' => 1.0],
    'USD' => ['symbol' => '$', 'rate' => 0.012],
    'EUR' => ['symbol' => '€', 'rate' => 0.011],
    'GBP' => ['symbol' => '£', 'rate' => 0.0095]
];

// Determine active currency
if (isset($_GET['currency']) && array_key_exists($_GET['currency'], $currencies)) {
    $_SESSION['currency'] = $_GET['currency'];
}
// Force INR globally
$current_currency = 'INR';

// Format and convert price helper
function formatPrice($price) {
    global $currencies, $current_currency;
    $curr = $currencies[$current_currency];
    $converted = $price * $curr['rate'];
    return $curr['symbol'] . number_format($converted, 2);
}

// Set temporary notifications (Toast / Banner Alert)
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

// Check and output flash notifications
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Notification loggers for simulated Email & SMS alerts
function sendSimulatedNotification($type, $recipient, $subject, $body) {
    // Append notice logs to a session array to display in the UI as beautiful Toast messages
    if (!isset($_SESSION['notifications_log'])) {
        $_SESSION['notifications_log'] = [];
    }
    $_SESSION['notifications_log'][] = [
        'time' => date('H:i:s'),
        'type' => $type, // 'Email' or 'SMS'
        'recipient' => $recipient,
        'subject' => $subject,
        'body' => $body
    ];
}

// Sanitize inputs
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
