<?php
/**
 * Shop Listing Page
 */
require_once __DIR__ . '/config/config.php';

// Retrieve Filters
$filterCategory = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$filterBrand = isset($_GET['brand']) ? sanitize($_GET['brand']) : '';
$searchKeyword = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$filterMinPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0.0;
$filterMaxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : 25000.0;
$filterRating = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
$filterSize = isset($_GET['size']) ? sanitize($_GET['size']) : ''; // 30ml, 50ml, 100ml
$filterLimited = isset($_GET['filter_limited']) ? (int)$_GET['filter_limited'] : 0;
$filterAvailability = isset($_GET['availability']) ? (int)$_GET['availability'] : 0;
$sortBy = isset($_GET['sort_by']) ? sanitize($_GET['sort_by']) : 'newest';

// Dynamic Query Builder
$query = "
    SELECT p.*, b.name as brand_name, c.name as category_name, c.slug as category_slug
    FROM `products` p
    LEFT JOIN `brands` b ON p.brand_id = b.id
    LEFT JOIN `categories` c ON p.category_id = c.id
    WHERE 1=1
";
$params = [];

// Category filter
if (!empty($filterCategory)) {
    $query .= " AND c.slug = :category";
    $params['category'] = $filterCategory;
}

// Brand filter
if (!empty($filterBrand)) {
    $query .= " AND b.slug = :brand";
    $params['brand'] = $filterBrand;
}

// Keyword search
if (!empty($searchKeyword)) {
    $query .= " AND (p.name LIKE :search OR p.description LIKE :search OR b.name LIKE :search OR p.top_notes LIKE :search OR p.middle_notes LIKE :search OR p.base_notes LIKE :search)";
    $params['search'] = '%' . $searchKeyword . '%';
}

// Limited Edition
if ($filterLimited == 1) {
    $query .= " AND p.is_limited_edition = 1";
}

// Rating filter
if ($filterRating > 0) {
    $query .= " AND p.rating >= :rating";
    $params['rating'] = $filterRating;
}

// Price filters (supports both raw scale and INR scale)
if ($filterMinPrice > 0) {
    $query .= " AND (p.price_50ml - p.discount_50ml) >= :min_price";
    $params['min_price'] = $filterMinPrice;
}
if (isset($_GET['max_price']) && $_GET['max_price'] !== '' && (float)$_GET['max_price'] < 25000.0) {
    $maxVal = (float)$_GET['max_price'];
    if ($maxVal <= 500) {
        $query .= " AND (p.price_50ml - p.discount_50ml) <= :max_price";
    } else {
        $query .= " AND ((p.price_50ml - p.discount_50ml) * 100) <= :max_price";
    }
    $params['max_price'] = $maxVal;
}

// Size filter
if (!empty($filterSize)) {
    $stockField = 'stock_' . $filterSize;
    $priceField = 'price_' . $filterSize;
    $query .= " AND p.$priceField IS NOT NULL";
    if ($filterAvailability == 1) {
        $query .= " AND p.$stockField > 0";
    }
} else {
    if ($filterAvailability == 1) {
        $query .= " AND (p.stock_30ml > 0 OR p.stock_50ml > 0 OR p.stock_100ml > 0)";
    }
}

// Sorting logic
switch ($sortBy) {
    case 'price_low_high':
        $query .= " ORDER BY (p.price_50ml - p.discount_50ml) ASC";
        break;
    case 'price_high_low':
        $query .= " ORDER BY (p.price_50ml - p.discount_50ml) DESC";
        break;
    case 'popularity':
        $query .= " ORDER BY p.rating DESC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY p.created_at DESC";
        break;
}

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $productsList = $stmt->fetchAll();

    // Fetch categories & brands for sidebar filters
    $categoriesList = $pdo->query("SELECT * FROM `categories`")->fetchAll();
    $brandsList = $pdo->query("SELECT * FROM `brands`")->fetchAll();

    // Fetch customer wishlist IDs
    $userWishlistIds = [];
    if (isset($_SESSION['customer_id'])) {
        $stmt = $pdo->prepare("SELECT product_id FROM `wishlist` WHERE customer_id = ?");
        $stmt->execute([$_SESSION['customer_id']]);
        $userWishlistIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
} catch (PDOException $e) {
    $productsList = [];
    $categoriesList = [];
    $brandsList = [];
    $userWishlistIds = [];
}

include_once __DIR__ . '/includes/header.php';
?>

    <!-- Page Title Header -->
    <div class="py-5 bg-black border-bottom border-secondary text-center">
        <div class="container">
            <span class="text-warning text-uppercase small tracking-widest">AromaLuxe Catalog</span>
            <h1 class="luxury-font text-white display-5 mt-2 text-luxury-glow">All Fragrances</h1>
            <div class="mx-auto bg-warning mt-3" style="width: 50px; height: 1.5px;"></div>
        </div>
    </div>

    <!-- Catalog Main Area -->
    <div class="container py-5">
        <div class="row g-4">
            
            <!-- Filters Sidebar -->
            <div class="col-lg-3 animate-on-scroll">
                <div class="glass-card p-4 sticky-lg-top" style="top: 100px; z-index: 10;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="font-heading text-white m-0">Filters</h5>
                        <a href="shop.php" class="small text-warning text-decoration-underline">Clear All</a>
                    </div>

                    <!-- Search Form -->
                    <form method="GET" action="shop.php" class="mb-4">
                        <label class="form-label text-white-50 small">Keyword Search</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control bg-transparent border-secondary text-white small" placeholder="Search perfume name..." value="<?php echo htmlspecialchars($searchKeyword); ?>">
                            <button class="btn btn-gold py-0" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </form>

                    <!-- Category Filter List -->
                    <div class="mb-4">
                        <label class="form-label text-white-50 small mb-2">Fragrance Category</label>
                        <div class="list-group list-group-flush" style="max-height: 180px; overflow-y: auto;">
                            <?php foreach ($categoriesList as $cat): ?>
                                <a href="shop.php?category=<?php echo $cat['slug']; ?>&brand=<?php echo $filterBrand; ?>&sort_by=<?php echo $sortBy; ?>" class="list-group-item bg-transparent text-light border-0 py-1 px-0 d-flex justify-content-between align-items-center <?php echo ($filterCategory === $cat['slug']) ? 'text-warning font-heading fw-bold' : ''; ?>">
                                    <span><?php echo $cat['name']; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Brand Filter List -->
                    <div class="mb-4">
                        <label class="form-label text-white-50 small mb-2">Perfume House / Brand</label>
                        <div class="list-group list-group-flush" style="max-height: 180px; overflow-y: auto;">
                            <?php foreach ($brandsList as $br): ?>
                                <a href="shop.php?brand=<?php echo $br['slug']; ?>&category=<?php echo $filterCategory; ?>&sort_by=<?php echo $sortBy; ?>" class="list-group-item bg-transparent text-light border-0 py-1 px-0 d-flex justify-content-between align-items-center <?php echo ($filterBrand === $br['slug']) ? 'text-warning font-heading fw-bold' : ''; ?>">
                                    <span><?php echo $br['name']; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Price Filter Slider -->
                    <form method="GET" action="shop.php" class="mb-4 p-3 rounded-3" style="background: rgba(15, 18, 30, 0.6); border: 1px solid rgba(212, 168, 83, 0.2);">
                        <!-- Keep category, brand, search parameters -->
                        <?php if(!empty($filterCategory)): ?><input type="hidden" name="category" value="<?php echo $filterCategory; ?>"><?php endif; ?>
                        <?php if(!empty($filterBrand)): ?><input type="hidden" name="brand" value="<?php echo $filterBrand; ?>"><?php endif; ?>
                        <?php if(!empty($searchKeyword)): ?><input type="hidden" name="search" value="<?php echo $searchKeyword; ?>"><?php endif; ?>
                        
                        <label class="form-label text-light small mb-2 d-flex justify-content-between align-items-center">
                            <span class="fw-medium">Max Price (INR)</span>
                            <strong class="text-warning fs-6"><?php echo $settings['currency_symbol'] ?? '₹'; ?><span id="priceValDisplay"><?php echo (int)($filterMaxPrice > 0 ? $filterMaxPrice : 25000); ?></span></strong>
                        </label>
                        <input type="range" class="form-range" name="max_price" id="maxPriceRangeInput" min="500" max="25000" step="500" value="<?php echo (int)($filterMaxPrice > 0 ? $filterMaxPrice : 25000); ?>" oninput="document.getElementById('priceValDisplay').innerText=this.value">
                        <div class="d-flex justify-content-between text-muted small mb-2" style="font-size:0.72rem;">
                            <span>₹500</span>
                            <span>₹25,000</span>
                        </div>
                        <button type="submit" class="btn btn-sm btn-gold w-100 py-2 fw-bold shadow-gold" style="font-size:0.82rem;">
                            <i class="bi bi-funnel-fill me-1"></i>Apply Price Filter
                        </button>
                    </form>

                    <!-- Scent Bottle Size -->
                    <div class="mb-4">
                        <label class="form-label text-white-50 small mb-2">Bottle Size</label>
                        <div class="d-flex gap-2">
                            <a href="shop.php?size=30ml&category=<?php echo $filterCategory; ?>&brand=<?php echo $filterBrand; ?>" class="btn btn-sm <?php echo ($filterSize === '30ml') ? 'btn-gold' : 'btn-outline-secondary text-white'; ?> py-1 flex-fill">30ml</a>
                            <a href="shop.php?size=50ml&category=<?php echo $filterCategory; ?>&brand=<?php echo $filterBrand; ?>" class="btn btn-sm <?php echo ($filterSize === '50ml') ? 'btn-gold' : 'btn-outline-secondary text-white'; ?> py-1 flex-fill">50ml</a>
                            <a href="shop.php?size=100ml&category=<?php echo $filterCategory; ?>&brand=<?php echo $filterBrand; ?>" class="btn btn-sm <?php echo ($filterSize === '100ml') ? 'btn-gold' : 'btn-outline-secondary text-white'; ?> py-1 flex-fill">100ml</a>
                        </div>
                    </div>

                    <!-- Stock Availability Toggle -->
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input bg-dark border-secondary" type="checkbox" role="switch" id="stockToggleInput" <?php echo ($filterAvailability == 1) ? 'checked' : ''; ?> onchange="window.location.href='shop.php?availability=' + (this.checked ? '1' : '0') + '&category=<?php echo $filterCategory; ?>&brand=<?php echo $filterBrand; ?>&size=<?php echo $filterSize; ?>'">
                        <label class="form-check-label text-white-50 small" for="stockToggleInput">Show In Stock Only</label>
                    </div>
                </div>
            </div>

            <!-- Products Catalog -->
            <div class="col-lg-9 animate-on-scroll delay-100">
                <!-- Sorting & Metadata Row -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary">
                    <div class="text-secondary small mb-2 mb-sm-0">
                        Showing <strong class="text-white"><?php echo count($productsList); ?></strong> luxurious fragrances
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="text-secondary small text-nowrap">Sort By:</span>
                        <select class="form-select form-select-sm bg-dark border-secondary text-white small" style="width: 180px;" onchange="window.location.href='shop.php?category=<?php echo $filterCategory; ?>&brand=<?php echo $filterBrand; ?>&search=<?php echo $searchKeyword; ?>&max_price=<?php echo $filterMaxPrice; ?>&size=<?php echo $filterSize; ?>&availability=<?php echo $filterAvailability; ?>&sort_by=' + this.value">
                            <option value="newest" <?php echo ($sortBy === 'newest') ? 'selected' : ''; ?>>Newest Arrivals</option>
                            <option value="price_low_high" <?php echo ($sortBy === 'price_low_high') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high_low" <?php echo ($sortBy === 'price_high_low') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="popularity" <?php echo ($sortBy === 'popularity') ? 'selected' : ''; ?>>Popularity (Rating)</option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="row g-4">
                    <?php if (count($productsList) > 0): ?>
                        <?php foreach ($productsList as $product): ?>
                            <?php 
                            // Select prices dynamically based on size filter
                            $sizeSelected = !empty($filterSize) ? $filterSize : '50ml';
                            $priceField = 'price_' . $sizeSelected;
                            $discField = 'discount_' . $sizeSelected;
                            $stockField = 'stock_' . $sizeSelected;

                            $priceRaw = (float)$product[$priceField];
                            $discRaw = (float)$product[$discField];
                            $priceFinal = $priceRaw - $discRaw;
                            $stockAmt = (int)$product[$stockField];
                            ?>
                            <div class="col-md-4 col-sm-6">
                                <div class="product-card h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <?php if ($product['is_limited_edition']): ?>
                                            <span class="badge-limited">Limited</span>
                                        <?php endif; ?>
                                        <?php if ($discRaw > 0): ?>
                                            <span class="badge-discount">-<?php echo formatPrice($discRaw); ?> Off</span>
                                        <?php endif; ?>

                                        <div class="product-image-wrap">
                                            <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" onerror="this.onerror=null; this.src='assets/images/womens_perfume.png';">
                                            <div class="product-action-overlay">
                                                <button class="action-btn" data-wishlist-id="<?php echo $product['id']; ?>" onclick="toggleWishlist(<?php echo $product['id']; ?>)">
                                                    <i class="bi <?php echo in_array($product['id'], $userWishlistIds) ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                                                </button>
                                                <a href="product.php?id=<?php echo $product['id']; ?>" class="action-btn"><i class="bi bi-eye"></i></a>
                                            </div>
                                        </div>

                                        <div class="small text-secondary font-heading"><?php echo $product['brand_name'] ? $product['brand_name'] : 'AromaLuxe'; ?></div>
                                        <h5 class="text-white font-heading mt-1 mb-2 fs-6"><?php echo $product['name']; ?></h5>
                                        
                                        <!-- Note Preview -->
                                        <div class="small text-secondary mb-2" style="font-size:0.75rem;">
                                            Notes: <span class="text-warning"><?php echo implode(', ', array_slice(explode(',', $product['top_notes']), 0, 2)); ?>...</span>
                                        </div>

                                        <div class="text-warning small mb-3">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi <?php echo ($i <= round($product['rating'])) ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                                            <?php endfor; ?>
                                            <span class="text-secondary ps-1">(<?php echo $product['rating']; ?>)</span>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="text-warning fw-bold">
                                            <?php echo formatPrice($priceFinal); ?>
                                            <span class="text-secondary d-block small fw-normal" style="font-size:0.65rem;">Size: <?php echo $sizeSelected; ?></span>
                                        </div>
                                        
                                        <?php if ($stockAmt > 0): ?>
                                            <button onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo $sizeSelected; ?>')" class="btn btn-sm btn-gold px-3" style="font-size:0.75rem;">
                                                <i class="bi bi-bag-plus me-1"></i> Add
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary px-3 text-dark small" style="font-size:0.75rem;" disabled>Out of Stock</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 py-5 text-center">
                            <i class="bi bi-emoji-frown fs-1 text-secondary"></i>
                            <h4 class="font-heading mt-3 text-white">No fragrances found</h4>
                            <p class="text-secondary small">Please modify your active filters or clear search query.</p>
                            <a href="shop.php" class="btn btn-gold mt-2">Browse All Products</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
