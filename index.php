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

    <!-- Full-Page Particle Canvas Background -->
    <canvas id="particleCanvas" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 1;"></canvas>

    <!-- Premium Video Hero Section -->
    <header class="hero-section" style="position: relative; overflow: hidden;">
        <!-- Video Background -->
        <video autoplay muted loop playsinline id="heroVideo" style="
            position: absolute;
            top: 50%; left: 50%;
            min-width: 100%; min-height: 100%;
            width: auto; height: auto;
            transform: translate(-50%, -50%);
            z-index: 0;
            object-fit: cover;
        ">
            <source src="assets/videos/hero-bg.mp4" type="video/mp4">
        </video>

        <!-- Dark Overlay for Readability -->
        <div style="
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(180deg, rgba(6,7,14,0.75) 0%, rgba(6,7,14,0.55) 40%, rgba(6,7,14,0.80) 100%);
            z-index: 1;
        "></div>

        <div class="hero-bg-accent"></div>
        <div class="hero-particles"></div>
        <div class="hero-particles-extra"></div>

        <div class="container h-100 d-flex align-items-center" style="position: relative; z-index: 3;">
            <div class="row w-100 align-items-center">
                <!-- Content -->
                <div class="col-lg-8 col-xl-7 hero-content text-lg-start text-center mx-auto mx-lg-0">
                    <span class="section-label" style="letter-spacing: 4px;">Artisanal Niche Perfumery</span>
                    <h1 class="text-white display-3 fw-bold my-3 font-display leading-tight text-luxury-glow hero-title-animate" style="visibility: hidden;">
                        <?php echo __('hero_title'); ?>
                    </h1>
                    <p class="text-secondary fs-5 mb-4 fw-light" style="font-family: var(--font-body); max-width: 560px;">
                        <?php echo __('hero_subtitle'); ?>
                    </p>
                    <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3">
                        <a href="shop.php" class="btn btn-gold"><?php echo __('shop_now'); ?></a>
                        <a href="shop.php?category=luxury" class="btn btn-outline-gold"><?php echo __('explore'); ?></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div style="position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 3; text-align: center;">
            <div style="width: 24px; height: 40px; border: 2px solid rgba(212,168,83,0.4); border-radius: 12px; margin: 0 auto;">
                <div style="width: 4px; height: 8px; background: var(--gold); border-radius: 2px; margin: 6px auto 0; animation: scrollPulse 2s ease-in-out infinite;"></div>
            </div>
            <span class="d-block mt-2" style="font-size: 0.65rem; letter-spacing: 3px; text-transform: uppercase; color: rgba(212,168,83,0.5);">Scroll</span>
        </div>
    </header>

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Luxury Categories Spotlight Grid -->
    <section class="py-5 animate-on-scroll" style="background-color: var(--bg-secondary);">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="section-label">Curation</span>
                <h2 class="text-white font-display mt-2 text-luxury-glow">Shop by Category</h2>
                <div class="section-heading-line mx-auto mt-3"></div>
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

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Featured Products Showcase -->
    <section class="py-5 bg-black animate-on-scroll">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="section-label">Exclusive Selection</span>
                <h2 class="text-white font-display mt-2 text-luxury-glow"><?php echo __('featured_products'); ?></h2>
                <div class="section-heading-line mx-auto mt-3"></div>
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

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Limited Offer Promo Spotlight (Discount Offers) -->
    <section class="py-5 text-white animate-on-scroll" style="background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-tertiary) 50%, var(--bg-primary) 100%);">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <span class="section-label">Limited Edition Masterpiece</span>
                    <h2 class="font-display display-4 fw-bold mt-2" style="background: linear-gradient(135deg, var(--gold-light), var(--gold), var(--rose-gold)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Golden Elixir Set</h2>
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

    <!-- Section Divider -->
    <div class="section-divider"></div>

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

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Luxury Client Feedback Scrolling Reviews -->
    <section class="py-5 bg-black animate-on-scroll">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="section-label">Testimonials</span>
                <h2 class="text-white font-display mt-2 text-luxury-glow">What Our Clients Say</h2>
                <div class="section-heading-line mx-auto mt-3"></div>
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

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Luxury Instagram Gallery -->
    <section class="py-5 animate-on-scroll" style="background-color: var(--bg-secondary);">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="section-label">Social Feed</span>
                <h2 class="text-white font-display mt-2 text-luxury-glow">#AromaLuxeMoments</h2>
                <div class="section-heading-line mx-auto mt-3"></div>
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

<!-- Premium Particle Animation Engine -->
<script>
(function() {
    const canvas = document.getElementById('particleCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let particles = [];
    let mouseX = -1000, mouseY = -1000;
    const MAX_PARTICLES = 80;
    const CONNECT_DISTANCE = 120;

    function resize() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    window.addEventListener('resize', resize);
    resize();

    document.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
    });

    class Particle {
        constructor() {
            this.reset();
        }
        reset() {
            this.x = Math.random() * canvas.width;
            this.y = Math.random() * canvas.height;
            this.size = Math.random() * 2.2 + 0.5;
            this.speedX = (Math.random() - 0.5) * 0.4;
            this.speedY = (Math.random() - 0.5) * 0.3 - 0.15;
            this.opacity = Math.random() * 0.5 + 0.1;
            this.fadeDir = Math.random() > 0.5 ? 1 : -1;
            this.fadeSpeed = Math.random() * 0.003 + 0.001;
            // Color variety: gold, rose-gold, amethyst, champagne
            const colors = [
                [212, 168, 83],   // gold
                [201, 139, 110],  // rose-gold
                [139, 108, 193],  // amethyst
                [245, 230, 196],  // champagne
                [242, 219, 167],  // gold-light
            ];
            this.color = colors[Math.floor(Math.random() * colors.length)];
            this.isStar = Math.random() > 0.85;
            this.twinklePhase = Math.random() * Math.PI * 2;
            this.twinkleSpeed = Math.random() * 0.02 + 0.01;
        }
        update() {
            this.x += this.speedX;
            this.y += this.speedY;

            // Fade in/out pulse
            this.opacity += this.fadeDir * this.fadeSpeed;
            if (this.opacity >= 0.6) this.fadeDir = -1;
            if (this.opacity <= 0.05) this.fadeDir = 1;

            // Twinkle for star particles
            this.twinklePhase += this.twinkleSpeed;

            // Mouse repulsion
            const dx = this.x - mouseX;
            const dy = this.y - mouseY;
            const dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < 100) {
                const force = (100 - dist) / 100;
                this.x += (dx / dist) * force * 1.5;
                this.y += (dy / dist) * force * 1.5;
            }

            // Wrap around
            if (this.x < -10) this.x = canvas.width + 10;
            if (this.x > canvas.width + 10) this.x = -10;
            if (this.y < -10) this.y = canvas.height + 10;
            if (this.y > canvas.height + 10) this.y = -10;
        }
        draw() {
            const [r, g, b] = this.color;
            const alpha = this.isStar
                ? this.opacity * (0.5 + 0.5 * Math.sin(this.twinklePhase))
                : this.opacity;

            if (this.isStar) {
                // Draw 4-point star
                const s = this.size * 2;
                ctx.save();
                ctx.translate(this.x, this.y);
                ctx.rotate(this.twinklePhase * 0.5);
                ctx.beginPath();
                for (let i = 0; i < 4; i++) {
                    const angle = (i * Math.PI) / 2;
                    ctx.moveTo(0, 0);
                    ctx.lineTo(Math.cos(angle) * s, Math.sin(angle) * s);
                }
                ctx.strokeStyle = `rgba(${r}, ${g}, ${b}, ${alpha})`;
                ctx.lineWidth = 0.5;
                ctx.stroke();
                ctx.restore();

                // Glow center
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size * 0.6, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(${r}, ${g}, ${b}, ${alpha})`;
                ctx.fill();
            } else {
                // Regular dot with soft glow
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(${r}, ${g}, ${b}, ${alpha})`;
                ctx.fill();

                // Glow halo
                if (this.size > 1.2) {
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, this.size * 3, 0, Math.PI * 2);
                    ctx.fillStyle = `rgba(${r}, ${g}, ${b}, ${alpha * 0.08})`;
                    ctx.fill();
                }
            }
        }
    }

    // Initialize particles
    for (let i = 0; i < MAX_PARTICLES; i++) {
        particles.push(new Particle());
    }

    function drawConnections() {
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < CONNECT_DISTANCE) {
                    const alpha = (1 - dist / CONNECT_DISTANCE) * 0.06;
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = `rgba(212, 168, 83, ${alpha})`;
                    ctx.lineWidth = 0.4;
                    ctx.stroke();
                }
            }
        }
    }

    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        particles.forEach(p => {
            p.update();
            p.draw();
        });
        drawConnections();
        requestAnimationFrame(animate);
    }

    animate();
})();
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
