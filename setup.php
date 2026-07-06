<?php
/**
 * Setup Script for AromaLuxe
 * Initializes database, tables, and inserts premium mock data.
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'perfume_store';

echo "<h2>AromaLuxe Database Installation Helper</h2>";

// 1. Establish initial server connection
try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green;'>✓ Connected to MySQL server.</p>";
} catch (PDOException $e) {
    die("<p style='color:red;'>✗ MySQL Connection failed: " . $e->getMessage() . "<br>Please ensure XAMPP MySQL is running.</p>");
}

// 2. Read and execute database.sql schema
try {
    $sql = file_get_contents(__DIR__ . '/database.sql');
    if ($sql === false) {
        throw new Exception("Could not find database.sql in current directory.");
    }
    
    // Execute multiple SQL statements
    $pdo->exec($sql);
    echo "<p style='color:green;'>✓ Database 'perfume_store' and tables created successfully.</p>";
    
    // Reconnect to the newly created database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("<p style='color:red;'>✗ Schema execution failed: " . $e->getMessage() . "</p>");
}

// Helper function to check if table is empty
function isTableEmpty($pdo, $tableName) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM `$tableName`");
    return $stmt->fetchColumn() == 0;
}

// 3. Insert Categories
if (isTableEmpty($pdo, 'categories')) {
    $categories = [
        ['Men\'s Perfume', 'mens-perfume', 'Elegant and masculine fragrances.'],
        ['Women\'s Perfume', 'womens-perfume', 'Floral, sweet, and elegant scents.'],
        ['Unisex', 'unisex', 'Versatile fragrances suited for all genders.'],
        ['Luxury', 'luxury', 'Exquisite private blends and niche items.'],
        ['Arabic Perfume', 'arabic-perfume', 'Rich notes of oud, amber, rose, and musk.'],
        ['Designer Perfume', 'designer-perfume', 'Fragrances from world-class fashion houses.'],
        ['Summer Collection', 'summer-collection', 'Light, fresh, and citrusy scents.'],
        ['Winter Collection', 'winter-collection', 'Warm, spicy, and woody notes.'],
        ['Gift Sets', 'gift-sets', 'Curated collections perfect for gifting.'],
        ['Travel Size', 'travel-size', 'Compact bottles perfect for travel.']
    ];
    $stmt = $pdo->prepare("INSERT INTO `categories` (name, slug, description) VALUES (?, ?, ?)");
    foreach ($categories as $cat) {
        $stmt->execute($cat);
    }
    echo "<p style='color:green;'>✓ Categories table seeded.</p>";
}

// 4. Insert Brands
if (isTableEmpty($pdo, 'brands')) {
    $brands = [
        ['AromaLuxe Private', 'aromaluxe-private', 'Signature luxury scents from our in-house perfumers.'],
        ['Creed', 'creed', 'Historic and royal fragrances from a legendary house.'],
        ['Tom Ford', 'tom-ford', 'Bold, elegant, and uncompromising scents.'],
        ['Chanel', 'chanel', 'Classic elegance and timeless French masterpieces.'],
        ['Dior', 'dior', 'Sophisticated couture perfumes with rich history.'],
        ['Gucci', 'gucci', 'Eclectic, modern, and romantic fragrances.']
    ];
    $stmt = $pdo->prepare("INSERT INTO `brands` (name, slug, description) VALUES (?, ?, ?)");
    foreach ($brands as $brand) {
        $stmt->execute($brand);
    }
    echo "<p style='color:green;'>✓ Brands table seeded.</p>";
}

// Helper to get ID mappings
$catIds = $pdo->query("SELECT slug, id FROM categories")->fetchAll(PDO::FETCH_KEY_PAIR);
$brandIds = $pdo->query("SELECT slug, id FROM brands")->fetchAll(PDO::FETCH_KEY_PAIR);

// 5. Insert Products
if (isTableEmpty($pdo, 'products')) {
    $products = [
        [
            'category_id' => $catIds['arabic-perfume'],
            'brand_id' => $brandIds['aromaluxe-private'],
            'name' => 'Oud Imperial',
            'sku' => 'ALX-OUD-IMP-01',
            'description' => 'A dark and majestic masterpiece. Oud Imperial is a rich blend of precious Cambodian Oud, warm patchouli, and sweet spices. Crafted for true connoisseurs of luxury, this fragrance wraps you in a deep, long-lasting aura of mystery and elegance.',
            'ingredients' => 'Alcohol Denat., Parfum (Fragrance), Aqua (Water), Limonene, Linalool, Coumarin, Eugenol, Benzyl Benzoate.',
            'top_notes' => 'Saffron, Nutmeg, Lavender',
            'middle_notes' => 'Cambodian Oud, Patchouli, Cashmere Wood',
            'base_notes' => 'Amber, Musk, Oakmoss',
            'price_30ml' => 89.00, 'price_50ml' => 129.00, 'price_100ml' => 219.00,
            'discount_30ml' => 0.00, 'discount_50ml' => 10.00, 'discount_100ml' => 20.00,
            'stock_30ml' => 15, 'stock_50ml' => 25, 'stock_100ml' => 10,
            'rating' => 4.9,
            'image_url' => 'assets/images/oud_perfume.png',
            'image_gallery' => 'assets/images/oud_perfume.png,assets/images/unisex_perfume.png',
            'is_featured' => 1, 'is_best_seller' => 1, 'is_new_arrival' => 0, 'is_limited_edition' => 1
        ],
        [
            'category_id' => $catIds['womens-perfume'],
            'brand_id' => $brandIds['chanel'],
            'name' => 'Rouge Passion',
            'sku' => 'CHL-RGE-PAS-02',
            'description' => 'Unleash your inner confidence. Rouge Passion features a vibrant floral heart of Bulgarian Rose and Grasse Jasmine, combined with the sophistication of blackcurrant and warm sandalwood. A modern tribute to femininity.',
            'ingredients' => 'Alcohol, Aqua, Parfum, Benzyl Salicylate, Hexyl Cinnamal, Citronellol, Geraniol.',
            'top_notes' => 'Blackcurrant, Pink Pepper, Bergamot',
            'middle_notes' => 'Bulgarian Rose, Jasmine Sambac, Iris',
            'base_notes' => 'Madagascar Vanilla, Patchouli, Sandalwood',
            'price_30ml' => 75.00, 'price_50ml' => 110.00, 'price_100ml' => 175.00,
            'discount_30ml' => 5.00, 'discount_50ml' => 5.00, 'discount_100ml' => 15.00,
            'stock_30ml' => 30, 'stock_50ml' => 20, 'stock_100ml' => 15,
            'rating' => 4.8,
            'image_url' => 'assets/images/womens_perfume.png',
            'image_gallery' => 'assets/images/womens_perfume.png,assets/images/oud_perfume.png',
            'is_featured' => 1, 'is_best_seller' => 0, 'is_new_arrival' => 1, 'is_limited_edition' => 0
        ],
        [
            'category_id' => $catIds['mens-perfume'],
            'brand_id' => $brandIds['dior'],
            'name' => 'Blue Sauvage',
            'sku' => 'DIR-BLU-SAV-03',
            'description' => 'A clean, crisp, and wildly fresh fragrance. Blue Sauvage pairs sparkling Calabrian bergamot with aromatic lavender, finishing with a raw, masculine trace of ambroxan and cedarwood. Ideal for active, self-assured men.',
            'ingredients' => 'Alcohol, Parfum, Aqua, Limonene, Linalool, Citral, Coumarin.',
            'top_notes' => 'Calabrian Bergamot, Sichuan Pepper',
            'middle_notes' => 'Lavender, Vetiver, Patchouli',
            'base_notes' => 'Ambroxan, Cedarwood, Labdanum',
            'price_30ml' => 69.00, 'price_50ml' => 95.00, 'price_100ml' => 145.00,
            'discount_30ml' => 0.00, 'discount_50ml' => 0.00, 'discount_100ml' => 10.00,
            'stock_30ml' => 40, 'stock_50ml' => 50, 'stock_100ml' => 35,
            'rating' => 4.7,
            'image_url' => 'assets/images/mens_perfume.png',
            'image_gallery' => 'assets/images/mens_perfume.png,assets/images/mens_perfume.png',
            'is_featured' => 0, 'is_best_seller' => 1, 'is_new_arrival' => 0, 'is_limited_edition' => 0
        ],
        [
            'category_id' => $catIds['mens-perfume'],
            'brand_id' => $brandIds['tom-ford'],
            'name' => 'Noir Charm',
            'sku' => 'TFD-NOR-CHM-04',
            'description' => 'Uncompromising and dark. Noir Charm captures a dense woody intensity loaded with spicy black pepper, premium vanilla, and rich leather. An evening fragrance that commands respect and triggers attraction.',
            'ingredients' => 'Alcohol Denat., Fragrance, Water, Linalool, Benzyl Salicylate, Geraniol.',
            'top_notes' => 'Black Pepper, Cardamom, Cypress',
            'middle_notes' => 'Leather, Tuscan Iris, Jasmine',
            'base_notes' => 'Amber, Vanilla Bean, Vetiver',
            'price_30ml' => 95.00, 'price_50ml' => 150.00, 'price_100ml' => 240.00,
            'discount_30ml' => 0.00, 'discount_50ml' => 0.00, 'discount_100ml' => 0.00,
            'stock_30ml' => 12, 'stock_50ml' => 10, 'stock_100ml' => 8,
            'rating' => 4.9,
            'image_url' => 'assets/images/mens_perfume.png',
            'image_gallery' => 'assets/images/mens_perfume.png,assets/images/oud_perfume.png',
            'is_featured' => 1, 'is_best_seller' => 0, 'is_new_arrival' => 1, 'is_limited_edition' => 0
        ],
        [
            'category_id' => $catIds['womens-perfume'],
            'brand_id' => $brandIds['aromaluxe-private'],
            'name' => 'Vanilla Royale',
            'sku' => 'ALX-VAN-ROY-05',
            'description' => 'Indulge in sweet luxury. Vanilla Royale is a rich gourmand perfume blending caramel, warm honey, and Madagascar vanilla pod, offset by a sophisticated touch of tobacco flower and amberwood.',
            'ingredients' => 'Alcohol Denat., Aqua, Parfum, Coumarin, Benzyl Alcohol, Anise Alcohol, Limonene.',
            'top_notes' => 'Honey, Caramel, Tonka Bean',
            'middle_notes' => 'Vanilla Pod, Tobacco Flower, Cocoa',
            'base_notes' => 'Amberwood, Sandalwood, Musk',
            'price_30ml' => 59.00, 'price_50ml' => 89.00, 'price_100ml' => 139.00,
            'discount_30ml' => 5.00, 'discount_50ml' => 10.00, 'discount_100ml' => 15.00,
            'stock_30ml' => 20, 'stock_50ml' => 25, 'stock_100ml' => 30,
            'rating' => 4.6,
            'image_url' => 'assets/images/womens_perfume.png',
            'image_gallery' => 'assets/images/womens_perfume.png',
            'is_featured' => 0, 'is_best_seller' => 1, 'is_new_arrival' => 1, 'is_limited_edition' => 0
        ],
        [
            'category_id' => $catIds['unisex'],
            'brand_id' => $brandIds['creed'],
            'name' => 'Citrus Breeze',
            'sku' => 'CRD-CTR-BRZ-06',
            'description' => 'Bright, uplifting, and completely fresh. Citrus Breeze blends sparkling lemon, green tea, and marine notes, resting on a clean bed of white musk. It captures the spirit of a Mediterranean summer afternoon.',
            'ingredients' => 'Alcohol, Aqua, Parfum, Citral, Limonene, Linalool, Geraniol, Citronellol.',
            'top_notes' => 'Bergamot, Amalfi Lemon, Mandarin Orange',
            'middle_notes' => 'Green Tea, Black Currant, Sea Salt',
            'base_notes' => 'White Musk, Galbanum, Sandalwood',
            'price_30ml' => 95.00, 'price_50ml' => 140.00, 'price_100ml' => 210.00,
            'discount_30ml' => 0.00, 'discount_50ml' => 0.00, 'discount_100ml' => 20.00,
            'stock_30ml' => 25, 'stock_50ml' => 15, 'stock_100ml' => 12,
            'rating' => 4.5,
            'image_url' => 'assets/images/unisex_perfume.png',
            'image_gallery' => 'assets/images/unisex_perfume.png',
            'is_featured' => 0, 'is_best_seller' => 0, 'is_new_arrival' => 0, 'is_limited_edition' => 0
        ],
        [
            'category_id' => $catIds['unisex'],
            'brand_id' => $brandIds['tom-ford'],
            'name' => 'Ambre Nuit',
            'sku' => 'TFD-AMB-NUT-07',
            'description' => 'An opulent, oriental fusion. Ambre Nuit combines dry woods, rich ambergris, and sweet Turkish rose. An intoxicating fragrance that is warm, powdery, and incredibly sensual for both men and women.',
            'ingredients' => 'Alcohol Denat., Parfum, Aqua, Citronellol, Linalool, Geraniol, Eugenol.',
            'top_notes' => 'Bergamot, Grapefruit',
            'middle_notes' => 'Turkish Rose, Pink Pepper',
            'base_notes' => 'Ambergris, Patchouli, Cedarwood',
            'price_30ml' => 110.00, 'price_50ml' => 170.00, 'price_100ml' => 280.00,
            'discount_30ml' => 0.00, 'discount_50ml' => 10.00, 'discount_100ml' => 30.00,
            'stock_30ml' => 8, 'stock_50ml' => 10, 'stock_100ml' => 6,
            'rating' => 4.9,
            'image_url' => 'assets/images/unisex_perfume.png',
            'image_gallery' => 'assets/images/unisex_perfume.png,assets/images/oud_perfume.png',
            'is_featured' => 1, 'is_best_seller' => 1, 'is_new_arrival' => 0, 'is_limited_edition' => 0
        ],
        [
            'category_id' => $catIds['gift-sets'],
            'brand_id' => $brandIds['aromaluxe-private'],
            'name' => 'Golden Elixir',
            'sku' => 'ALX-GDN-ELX-08',
            'description' => 'Our highly prized limited-edition blend. The Golden Elixir is an intensive perfume oil formulation capturing notes of precious agarwood, saffron, soft white lily, and rich honeyed sandalwood. Elevates any occasion.',
            'ingredients' => 'Parfum (Fragrance), Aqua (Water), Alpha-Isomethyl Ionone, Limonene, Citronellol, Coumarin.',
            'top_notes' => 'Saffron, White Lily',
            'middle_notes' => 'Oudwood, Honey, Patchouli',
            'base_notes' => 'Sandalwood, Vanilla, Musk',
            'price_30ml' => 125.00, 'price_50ml' => 195.00, 'price_100ml' => 320.00,
            'discount_30ml' => 15.00, 'discount_50ml' => 20.00, 'discount_100ml' => 40.00,
            'stock_30ml' => 5, 'stock_50ml' => 5, 'stock_100ml' => 4,
            'rating' => 5.0,
            'image_url' => 'assets/images/oud_perfume.png',
            'image_gallery' => 'assets/images/oud_perfume.png',
            'is_featured' => 1, 'is_best_seller' => 0, 'is_new_arrival' => 0, 'is_limited_edition' => 1
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO `products` (
        category_id, brand_id, name, sku, description, ingredients, top_notes, middle_notes, base_notes,
        price_30ml, price_50ml, price_100ml, discount_30ml, discount_50ml, discount_100ml,
        stock_30ml, stock_50ml, stock_100ml, rating, image_url, image_gallery,
        is_featured, is_best_seller, is_new_arrival, is_limited_edition
    ) VALUES (
        :category_id, :brand_id, :name, :sku, :description, :ingredients, :top_notes, :middle_notes, :base_notes,
        :price_30ml, :price_50ml, :price_100ml, :discount_30ml, :discount_50ml, :discount_100ml,
        :stock_30ml, :stock_50ml, :stock_100ml, :rating, :image_url, :image_gallery,
        :is_featured, :is_best_seller, :is_new_arrival, :is_limited_edition
    )");

    foreach ($products as $prod) {
        $stmt->execute($prod);
    }
    echo "<p style='color:green;'>✓ Products table seeded.</p>";
}

// 6. Insert Admin User
if (isTableEmpty($pdo, 'admin')) {
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO `admin` (username, password, email) VALUES (?, ?, ?)");
    $stmt->execute(['admin', $adminPassword, 'admin@aromaluxe.com']);
    echo "<p style='color:green;'>✓ Default Admin account seeded. (User: <b>admin</b>, Pass: <b>admin123</b>)</p>";
}

// 7. Insert Mock Customer
if (isTableEmpty($pdo, 'customers')) {
    $customerPassword = password_hash('customer123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO `customers` (name, email, password, phone, is_verified, referral_code, loyalty_points) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Sophia Loren', 'sophia@example.com', $customerPassword, '+1234567890', 1, 'SOPHIA-GOLD', 120]);
    echo "<p style='color:green;'>✓ Default Customer account seeded. (Email: <b>sophia@example.com</b>, Pass: <b>customer123</b>)</p>";
}

// 8. Insert Coupons
if (isTableEmpty($pdo, 'coupons')) {
    $coupons = [
        ['LUXURY10', 'percentage', 10.00, 50.00, '2027-12-31', 1],
        ['GOLD25', 'fixed', 25.00, 150.00, '2027-12-31', 1],
        ['WELCOME15', 'percentage', 15.00, 0.00, '2027-12-31', 1]
    ];
    $stmt = $pdo->prepare("INSERT INTO `coupons` (code, type, value, min_spend, expiry_date, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($coupons as $c) {
        $stmt->execute($c);
    }
    echo "<p style='color:green;'>✓ Discount Coupons seeded. (Try: <b>LUXURY10</b> or <b>GOLD25</b>)</p>";
}

// 9. Insert Settings
if (isTableEmpty($pdo, 'settings')) {
    $settings = [
        ['site_name', 'AromaLuxe'],
        ['site_email', 'support@aromaluxe.com'],
        ['site_phone', '+1 (800) 799-2766'],
        ['site_address', '720 Fifth Avenue, New York, NY 10019'],
        ['shipping_fee', '15.00'],
        ['gst_percentage', '18.00'],
        ['currency', 'INR'],
        ['currency_symbol', '₹'],
        ['loyalty_multiplier', '10'] // 10 points per dollar spent
    ];
    $stmt = $pdo->prepare("INSERT INTO `settings` (setting_key, setting_value) VALUES (?, ?)");
    foreach ($settings as $set) {
        $stmt->execute($set);
    }
    echo "<p style='color:green;'>✓ Website Settings seeded.</p>";
}

// 10. Insert Product Reviews
if (isTableEmpty($pdo, 'reviews')) {
    $pId = $pdo->query("SELECT id FROM products LIMIT 1")->fetchColumn();
    $cId = $pdo->query("SELECT id FROM customers LIMIT 1")->fetchColumn();
    if ($pId && $cId) {
        $reviews = [
            [$pId, $cId, 5, 'This is simply breathtaking. The notes of Oud are so pure and last all day. Truly worth the premium price.', 'Approved'],
            [$pId, $cId, 4, 'Very rich and elegant presentation. The bottle is beautiful. Excellent silage.', 'Approved']
        ];
        $stmt = $pdo->prepare("INSERT INTO `reviews` (product_id, customer_id, rating, comment, status) VALUES (?, ?, ?, ?, ?)");
        foreach ($reviews as $rev) {
            $stmt->execute($rev);
        }
        echo "<p style='color:green;'>✓ Initial Reviews seeded.</p>";
    }
}

echo "<h3>✓ Installation complete. You are ready to open <a href='index.php'>AromaLuxe E-Commerce Store</a>!</h3>";
?>
