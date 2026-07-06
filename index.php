<?php
/**
 * AromaLuxe Home Page
 */
require_once __DIR__ . '/config/config.php';

// Handle Newsletter Subscription AJAX requests
if (isset($_POST['action']) && $_POST['action'] === 'newsletter_subscribe') {
    header('Content-Type: application/json');
    $email = sanitize($_POST['email']);
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `newsletter` WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'You are already subscribed to our newsletter.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO `newsletter` (email) VALUES (?)");
        $stmt->execute([$email]);
        
        // Simulated subscription email
        sendSimulatedNotification(
            'Email',
            $email,
            'Newsletter Subscription Confirmed - AromaLuxe',
            "Thank you for subscribing to AromaLuxe's private catalog newsletter!\n\nYou will receive early access keys, special launch events notifications and details on upcoming notes."
        );
        
        echo json_encode(['status' => 'success', 'message' => 'Thank you for subscribing! Check your simulated inbox below.']);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error. Please try again later.']);
        exit;
    }
}

// Fetch products for home page sections
try {
    // 1. Featured Products
    $stmt = $pdo->query("SELECT p.*, b.name as brand_name FROM `products` p LEFT JOIN `brands` b ON p.brand_id = b.id WHERE p.is_featured = 1 LIMIT 4");
    $featuredProducts = $stmt->fetchAll();

    // 2. Best Sellers
    $stmt = $pdo->query("SELECT p.*, b.name as brand_name FROM `products` p LEFT JOIN `brands` b ON p.brand_id = b.id WHERE p.is_best_seller = 1 LIMIT 4");
    $bestSellers = $stmt->fetchAll();

    // 3. New Arrivals
    $stmt = $pdo->query("SELECT p.*, b.name as brand_name FROM `products` p LEFT JOIN `brands` b ON p.brand_id = b.id WHERE p.is_new_arrival = 1 LIMIT 4");
    $newArrivals = $stmt->fetchAll();
    // 4. Customer Wishlist IDs
    $userWishlistIds = [];
    if (isset($_SESSION['customer_id'])) {
        $stmt = $pdo->prepare("SELECT product_id FROM `wishlist` WHERE customer_id = ?");
        $stmt->execute([$_SESSION['customer_id']]);
        $userWishlistIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
} catch (PDOException $e) {
    $featuredProducts = [];
    $bestSellers = [];
    $newArrivals = [];
    $userWishlistIds = [];
}

include_once __DIR__ . '/includes/header.php';
?>

    <!-- Premium Parallax Hero Section -->
    <header class="hero-section">
        <div class="hero-bg-accent"></div>
        <div class="container h-100 d-flex align-items-center">
            <div class="row w-100 align-items-center">
                <!-- Left Content -->
                <div class="col-lg-6 hero-content text-lg-start text-center mb-5 mb-lg-0">
                    <span class="text-warning text-uppercase small fw-bold tracking-wide" style="letter-spacing: 3px;">Artisanal Niche Perfumery</span>
                    <h1 class="text-white display-3 fw-bold my-3 font-heading leading-tight text-luxury-glow">
                        <?php echo __('hero_title'); ?>
                    </h1>
                    <p class="text-secondary fs-5 mb-4 font-body fw-light">
                        <?php echo __('hero_subtitle'); ?>
                    </p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3">
                        <a href="shop.php" class="btn btn-gold"><?php echo __('shop_now'); ?></a>
                        <a href="shop.php?category=luxury" class="btn btn-outline-gold"><?php echo __('explore'); ?></a>
                    </div>
                </div>

                <!-- Right Perfume Floating Render -->
                <div class="col-lg-6 text-center hero-image-container">
                    <img src="assets/images/oud_perfume.png" alt="Featured Perfume bottle" class="hero-img img-fluid">
                </div>
            </div>
        </div>
    </header>

    <!-- Luxury Categories Spotlight Grid -->
    <section class="py-5 animate-on-scroll" style="background-color: var(--bg-secondary);">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="text-warning text-uppercase small tracking-widest" style="letter-spacing: 2px;">Curation</span>
                <h2 class="text-white font-heading mt-2 text-luxury-glow">Shop by Category</h2>
                <div class="mx-auto bg-warning mt-3" style="width: 50px; height: 1.5px;"></div>
            </div>

            <div class="row g-4 justify-content-center">
                <!-- Category Cards -->
                <div class="col-md-4 col-sm-6">
                    <a href="shop.php?category=mens-perfume" class="d-block text-center position-relative overflow-hidden rounded shadow border border-secondary category-card" style="height: 220px;">
                        <img src="assets/images/mens_perfume.png" class="w-100 h-100 object-fit-cover transition-all" alt="Men's collection">
                        <div class="position-absolute top-50 start-50 translate-middle text-white w-100">
                            <h4 class="font-heading m-0 category-card-title">Men's Perfume</h4>
                            <span class="text-warning small text-uppercase tracking-widest" style="font-size:0.7rem;">Bold & Woody</span>
                        </div>
                    </a>
                </div>

                <div class="col-md-4 col-sm-6">
                    <a href="shop.php?category=womens-perfume" class="d-block text-center position-relative overflow-hidden rounded shadow border border-secondary category-card" style="height: 220px;">
                        <img src="assets/images/womens_perfume.png" class="w-100 h-100 object-fit-cover transition-all" alt="Women's collection">
                        <div class="position-absolute top-50 start-50 translate-middle text-white w-100">
                            <h4 class="font-heading m-0 category-card-title">Women's Perfume</h4>
                            <span class="text-warning small text-uppercase tracking-widest" style="font-size:0.7rem;">Floral & Elegant</span>
                        </div>
                    </a>
                </div>

                <div class="col-md-4 col-sm-6">
                    <a href="shop.php?category=unisex" class="d-block text-center position-relative overflow-hidden rounded shadow border border-secondary category-card" style="height: 220px;">
                        <img src="assets/images/unisex_perfume.png" class="w-100 h-100 object-fit-cover transition-all" alt="Unisex collection">
                        <div class="position-absolute top-50 start-50 translate-middle text-white w-100">
                            <h4 class="font-heading m-0 category-card-title">Unisex</h4>
                            <span class="text-warning small text-uppercase tracking-widest" style="font-size:0.7rem;">Harmonious Blends</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Showcase -->
    <section class="py-5 bg-black animate-on-scroll">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="text-warning text-uppercase small tracking-widest" style="letter-spacing: 2px;">Exclusive Selection</span>
                <h2 class="text-white font-heading mt-2 text-luxury-glow"><?php echo __('featured_products'); ?></h2>
                <div class="mx-auto bg-warning mt-3" style="width: 50px; height: 1.5px;"></div>
            </div>

            <div class="row g-4">
                <?php if (count($featuredProducts) > 0): ?>
                    <?php foreach ($featuredProducts as $product): ?>
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="product-card">
                                <?php if ($product['is_limited_edition']): ?>
                                    <span class="badge-limited">Limited</span>
                                <?php endif; ?>
                                
                                <div class="product-image-wrap">
                                    <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                                    
                                    <div class="product-action-overlay">
                                        <button class="action-btn" data-wishlist-id="<?php echo $product['id']; ?>" onclick="toggleWishlist(<?php echo $product['id']; ?>)" title="Add to Wishlist">
                                            <i class="bi <?php echo in_array($product['id'], $userWishlistIds) ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                                        </button>
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="action-btn" title="Quick View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="small text-muted font-heading"><?php echo $product['brand_name'] ? $product['brand_name'] : 'AromaLuxe'; ?></div>
                                <h5 class="text-white font-heading mt-1 mb-2 fs-6"><?php echo $product['name']; ?></h5>
                                
                                <!-- Rating -->
                                <div class="text-warning small mb-3">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi <?php echo ($i <= round($product['rating'])) ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                                    <?php endfor; ?>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-warning fw-bold">
                                        <?php echo formatPrice($product['price_50ml'] - $product['discount_50ml']); ?>
                                    </div>
                                    <button onclick="addToCart(<?php echo $product['id']; ?>, '50ml')" class="btn btn-sm btn-gold px-3" style="font-size:0.75rem;">
                                        <i class="bi bi-bag-plus me-1"></i> Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted col-12">No products featured at this time.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Limited Offer Promo Spotlight (Discount Offers) -->
    <section class="py-5 border-top border-bottom border-secondary text-white animate-on-scroll" style="background: linear-gradient(135deg, #111 0%, #222 100%);">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <span class="text-warning font-heading small text-uppercase tracking-widest">Limited Edition Masterpiece</span>
                    <h2 class="font-heading display-4 fw-bold mt-2" style="color: var(--gold); text-shadow: 0 0 20px rgba(229, 192, 96, 0.4), 0 0 60px rgba(229, 192, 96, 0.15);">Golden Elixir Set</h2>
                    <p class="text-secondary mt-3 fs-5">Includes 100ml Parfum bottle, luxury traveler spray, and a velvet packaging sleeve. Infused with Cambodian Agarwood, Golden Honey, and Saffron.</p>
                    <div class="d-flex gap-3 mt-4 align-items-center">
                
                        <span class="text-muted text-decoration-line-through fs-4">₹16,500.00</span>
                        <span class="text-warning fs-2 fw-bold">₹14,525.00</span>
                        <span class="badge bg-danger py-2 px-3 fs-6">Save 12%</span>
                    </div>
                    <a href="product.php?id=8" class="btn btn-gold mt-4">Secure Yours Now</a>
                </div>
                <div class="col-md-6 text-center">
                    <img src="assets/images/oud_perfume.png" alt="Golden Elixir set" class="img-fluid rounded border border-warning shadow img-3d" style="max-height: 400px; object-fit: cover;">
                </div>
            </div>
        </div>
    </section>

    <!-- Best Selling and New Arrivals Tabs -->
    <section class="py-5 animate-on-scroll" style="background-color: var(--bg-secondary);">
        <div class="container py-4">
            <ul class="nav nav-tabs justify-content-center border-secondary mb-5" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link nav-link-luxury active fs-5 border-0" id="best-sellers-tab" data-bs-toggle="tab" data-bs-target="#best-sellers-pane" type="button" role="tab" aria-controls="best-sellers-pane" aria-selected="true">
                        <?php echo __('best_sellers'); ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link nav-link-luxury fs-5 border-0" id="new-arrivals-tab" data-bs-toggle="tab" data-bs-target="#new-arrivals-pane" type="button" role="tab" aria-controls="new-arrivals-pane" aria-selected="false">
                        <?php echo __('new_arrivals'); ?>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="productTabsContent">
                <!-- Tab: Best Sellers -->
                <div class="tab-pane fade show active" id="best-sellers-pane" role="tabpanel" aria-labelledby="best-sellers-tab" tabindex="0">
                    <div class="row g-4">
                        <?php if (count($bestSellers) > 0): ?>
                            <?php foreach ($bestSellers as $product): ?>
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    <div class="product-card">
                                        <div class="product-image-wrap">
                                            <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                                            <div class="product-action-overlay">
                                                <button class="action-btn" data-wishlist-id="<?php echo $product['id']; ?>" onclick="toggleWishlist(<?php echo $product['id']; ?>)">
                                                    <i class="bi <?php echo in_array($product['id'], $userWishlistIds) ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                                                </button>
                                                <a href="product.php?id=<?php echo $product['id']; ?>" class="action-btn"><i class="bi bi-eye"></i></a>
                                            </div>
                                        </div>
                                        <div class="small text-muted font-heading"><?php echo $product['brand_name'] ? $product['brand_name'] : 'AromaLuxe'; ?></div>
                                        <h5 class="text-white font-heading mt-1 mb-2 fs-6"><?php echo $product['name']; ?></h5>
                                        <div class="text-warning small mb-3">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi <?php echo ($i <= round($product['rating'])) ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-warning fw-bold">
                                                <?php echo formatPrice($product['price_50ml'] - $product['discount_50ml']); ?>
                                            </div>
                                            <button onclick="addToCart(<?php echo $product['id']; ?>, '50ml')" class="btn btn-sm btn-gold px-3" style="font-size:0.75rem;">
                                                Add
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center text-muted col-12">No best sellers registered.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tab: New Arrivals -->
                <div class="tab-pane fade" id="new-arrivals-pane" role="tabpanel" aria-labelledby="new-arrivals-tab" tabindex="0">
                    <div class="row g-4">
                        <?php if (count($newArrivals) > 0): ?>
                            <?php foreach ($newArrivals as $product): ?>
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    <div class="product-card">
                                        <div class="product-image-wrap">
                                            <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                                            <div class="product-action-overlay">
                                                <button class="action-btn" data-wishlist-id="<?php echo $product['id']; ?>" onclick="toggleWishlist(<?php echo $product['id']; ?>)">
                                                    <i class="bi <?php echo in_array($product['id'], $userWishlistIds) ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                                                </button>
                                                <a href="product.php?id=<?php echo $product['id']; ?>" class="action-btn"><i class="bi bi-eye"></i></a>
                                            </div>
                                        </div>
                                        <div class="small text-muted font-heading"><?php echo $product['brand_name'] ? $product['brand_name'] : 'AromaLuxe'; ?></div>
                                        <h5 class="text-white font-heading mt-1 mb-2 fs-6"><?php echo $product['name']; ?></h5>
                                        <div class="text-warning small mb-3">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi <?php echo ($i <= round($product['rating'])) ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-warning fw-bold">
                                                <?php echo formatPrice($product['price_50ml'] - $product['discount_50ml']); ?>
                                            </div>
                                            <button onclick="addToCart(<?php echo $product['id']; ?>, '50ml')" class="btn btn-sm btn-gold px-3" style="font-size:0.75rem;">
                                                Add
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center text-muted col-12">No new arrivals registered.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Luxury Client Feedback Scrolling Reviews -->
    <section class="py-5 bg-black border-top border-secondary animate-on-scroll">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="text-warning text-uppercase small tracking-widest" style="letter-spacing: 2px;">Testimonials</span>
                <h2 class="text-white font-heading mt-2 text-luxury-glow">What Our Clients Say</h2>
                <div class="mx-auto bg-warning mt-3" style="width: 50px; height: 1.5px;"></div>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100 text-center">
                        <div class="text-warning mb-3">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <p class="small text-secondary italic">"The Oud Imperial is simply marvelous. It carries a heavy, royal presence that gathers compliments everywhere I go. Incredible longevity!"</p>
                        <h6 class="font-heading text-white m-0 mt-3">— Alexander V.</h6>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100 text-center">
                        <div class="text-warning mb-3">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <p class="small text-secondary italic">"I bought the Rouge Passion as a gift for my mother. The glassmorphism design details of the bottle and its sweet scent profile are exceptional."</p>
                        <h6 class="font-heading text-white m-0 mt-3">— Isabella R.</h6>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100 text-center">
                        <div class="text-warning mb-3">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <p class="small text-secondary italic">"Exceptional customer service! The simulated notifications drawer updated me on every step of shipping. I'll definitely shop here again."</p>
                        <h6 class="font-heading text-white m-0 mt-3">— Victoria K.</h6>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Luxury Instagram Gallery -->
    <section class="py-5 animate-on-scroll" style="background-color: var(--bg-secondary);">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="text-warning text-uppercase small tracking-widest" style="letter-spacing: 2px;">Social Feed</span>
                <h2 class="text-white font-heading mt-2 text-luxury-glow">#AromaLuxeMoments</h2>
                <div class="mx-auto bg-warning mt-3" style="width: 50px; height: 1.5px;"></div>
            </div>

            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <a href="https://instagram.com" target="_blank" class="d-block overflow-hidden rounded position-relative group border border-secondary" style="height: 250px;">
                        <img src="assets/images/womens_perfume.png" class="w-100 h-100 object-fit-cover" alt="Instagram 1" style="filter: brightness(0.6);">
                        <div class="position-absolute top-50 start-50 translate-middle text-white opacity-0 group-hover-opacity-100 fs-3">
                            <i class="bi bi-instagram"></i>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="https://instagram.com" target="_blank" class="d-block overflow-hidden rounded position-relative group border border-secondary" style="height: 250px;">
                        <img src="assets/images/oud_perfume.png" class="w-100 h-100 object-fit-cover" alt="Instagram 2" style="filter: brightness(0.6);">
                        <div class="position-absolute top-50 start-50 translate-middle text-white opacity-0 group-hover-opacity-100 fs-3">
                            <i class="bi bi-instagram"></i>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="https://instagram.com" target="_blank" class="d-block overflow-hidden rounded position-relative group border border-secondary" style="height: 250px;">
                        <img src="assets/images/womens_perfume.png" class="w-100 h-100 object-fit-cover" alt="Instagram 3" style="filter: brightness(0.6);">
                        <div class="position-absolute top-50 start-50 translate-middle text-white opacity-0 group-hover-opacity-100 fs-3">
                            <i class="bi bi-instagram"></i>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="https://instagram.com" target="_blank" class="d-block overflow-hidden rounded position-relative group border border-secondary" style="height: 250px;">
                        <img src="assets/images/unisex_perfume.png" class="w-100 h-100 object-fit-cover" alt="Instagram 4" style="filter: brightness(0.6);">
                        <div class="position-absolute top-50 start-50 translate-middle text-white opacity-0 group-hover-opacity-100 fs-3">
                            <i class="bi bi-instagram"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
