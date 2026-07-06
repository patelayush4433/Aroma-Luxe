<?php
/**
 * Product Details Page
 */
require_once __DIR__ . '/config/config.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    header("Location: shop.php");
    exit;
}

// Fetch Product Details
$stmt = $pdo->prepare("
    SELECT p.*, b.name as brand_name, c.name as category_name 
    FROM `products` p
    LEFT JOIN `brands` b ON p.brand_id = b.id
    LEFT JOIN `categories` c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    setFlashMessage("danger", "Product not found.");
    header("Location: shop.php");
    exit;
}

// Check if in customer wishlist
$inWishlist = false;
if (isset($_SESSION['customer_id'])) {
    $wlStmt = $pdo->prepare("SELECT COUNT(*) FROM `wishlist` WHERE customer_id = ? AND product_id = ?");
    $wlStmt->execute([$_SESSION['customer_id'], $productId]);
    $inWishlist = ((int)$wlStmt->fetchColumn() > 0);
}

// Add Customer Review Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['customer_id'])) {
        setFlashMessage("danger", "Please login to write a review.");
        header("Location: auth/login.php");
        exit;
    }

    $customerId = $_SESSION['customer_id'];
    $rating = (int)$_POST['rating'];
    $comment = sanitize($_POST['comment']);

    if ($rating < 1 || $rating > 5) {
        $errorReview = "Please select a rating between 1 and 5 stars.";
    } else {
        try {
            $pdo->beginTransaction();
            // Insert Review
            $insReview = $pdo->prepare("INSERT INTO `reviews` (product_id, customer_id, rating, comment, status) VALUES (?, ?, ?, ?, 'Approved')");
            $insReview->execute([$productId, $customerId, $rating, $comment]);

            // Recalculate average product rating
            $avgRatingStmt = $pdo->prepare("SELECT AVG(rating) FROM `reviews` WHERE product_id = ? AND status = 'Approved'");
            $avgRatingStmt->execute([$productId]);
            $newAvgRating = (float)$avgRatingStmt->fetchColumn();

            // Update product rating
            $upProduct = $pdo->prepare("UPDATE `products` SET rating = ? WHERE id = ?");
            $upProduct->execute([$newAvgRating, $productId]);

            $pdo->commit();
            setFlashMessage("success", "Review submitted successfully!");
            header("Location: product.php?id=" . $productId);
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            setFlashMessage("danger", "Review submission failed. " . $e->getMessage());
        }
    }
}

// Fetch Approved Reviews
$stmt = $pdo->prepare("
    SELECT r.*, c.name as customer_name 
    FROM `reviews` r
    LEFT JOIN `customers` c ON r.customer_id = c.id
    WHERE r.product_id = ? AND r.status = 'Approved'
    ORDER BY r.created_at DESC
");
$stmt->execute([$productId]);
$reviews = $stmt->fetchAll();

// Fetch Related Products (same category, limit 4)
$stmt = $pdo->prepare("
    SELECT p.*, b.name as brand_name 
    FROM `products` p
    LEFT JOIN `brands` b ON p.brand_id = b.id
    WHERE p.category_id = ? AND p.id != ? 
    LIMIT 4
");
$stmt->execute([$product['category_id'], $productId]);
$relatedProducts = $stmt->fetchAll();

// Setup recently viewed session
if (!isset($_SESSION['recently_viewed'])) {
    $_SESSION['recently_viewed'] = [];
}
if (!in_array($productId, $_SESSION['recently_viewed'])) {
    array_unshift($_SESSION['recently_viewed'], $productId);
    // Limit to 4 items
    $_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 4);
}

include_once __DIR__ . '/includes/header.php';
?>

    <!-- Main Product Layout -->
    <div class="container py-5">
        
        <!-- Breadcrumb link paths -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="shop.php" class="text-muted">Shop</a></li>
                <li class="breadcrumb-item"><a href="shop.php?category=<?php echo urlencode($product['category_name']); ?>" class="text-muted"><?php echo $product['category_name']; ?></a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"><?php echo $product['name']; ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <!-- Product Gallery with Zoom-on-Hover -->
            <div class="col-lg-6 animate-on-scroll">
                <div class="zoom-container">
                    <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" id="mainProductImage">
                </div>

                <!-- Product Gallery thumbnails -->
                <?php if (!empty($product['image_gallery'])): ?>
                    <div class="row g-2 mt-2">
                        <?php 
                        $gallery = explode(',', $product['image_gallery']);
                        foreach ($gallery as $img): 
                        ?>
                            <div class="col-3">
                                <div class="border border-secondary rounded p-1 text-center bg-black cursor-pointer" onclick="document.getElementById('mainProductImage').src='<?php echo trim($img); ?>'">
                                    <img src="<?php echo trim($img); ?>" class="img-fluid" style="max-height: 80px; object-fit: contain;">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Details Summary -->
            <div class="col-lg-6 animate-on-scroll delay-100">
                <span class="text-warning font-heading text-uppercase tracking-wider small"><?php echo $product['brand_name'] ? $product['brand_name'] : 'AromaLuxe'; ?></span>
                <h1 class="text-white font-heading mt-2 mb-3 text-luxury-glow"><?php echo $product['name']; ?></h1>
                
                <!-- Ratings Summary -->
                <div class="d-flex align-items-center mb-4">
                    <div class="text-warning me-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi <?php echo ($i <= round($product['rating'])) ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="text-muted small">(<?php echo count($reviews); ?> Customer Reviews)</span>
                </div>

                <!-- Price and Size configuration attributes -->
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <h2 class="text-warning font-heading m-0" id="productPriceDisplay">
                            <?php 
                            $initialPrice = (float)$product['price_50ml'] - (float)$product['discount_50ml'];
                            echo formatPrice($initialPrice); 
                            ?>
                        </h2>
                        <!-- If discount exists, display raw strike pricing -->
                        <span class="text-muted text-decoration-line-through fs-5" id="productStrikeDisplay" style="display: <?php echo ((float)$product['discount_50ml'] > 0) ? 'inline' : 'none'; ?>;">
                            <?php echo formatPrice((float)$product['price_50ml']); ?>
                        </span>
                    </div>
                    <div class="small text-muted mt-1">* Prices adjusted relative to active exchange rates</div>
                </div>

                <!-- Detailed Description -->
                <p class="text-secondary mb-4"><?php echo $product['description']; ?></p>

                <!-- Configuration form (Size, Quantity, Stock, Actions) -->
                <form id="productPurchaseForm" class="mb-4">
                    <!-- Bottle Size Selection -->
                    <div class="mb-4">
                        <label class="form-label text-white-50 small mb-2">Select Bottle Size</label>
                        <div class="d-flex gap-3">
                            <?php foreach (['30ml', '50ml', '100ml'] as $sz): 
                                $priceVal = (float)$product['price_' . $sz];
                                $discVal = (float)$product['discount_' . $sz];
                                $finalVal = $priceVal - $discVal;
                                $stockVal = (int)$product['stock_' . $sz];
                                if ($priceVal > 0):
                            ?>
                                <div class="flex-fill">
                                    <input type="radio" class="btn-check" name="size" id="size_<?php echo $sz; ?>" value="<?php echo $sz; ?>" <?php echo ($sz === '50ml') ? 'checked' : ''; ?> 
                                           data-price="<?php echo $finalVal; ?>" 
                                           data-strike="<?php echo $priceVal; ?>" 
                                           data-has-discount="<?php echo ($discVal > 0) ? '1' : '0'; ?>"
                                           data-stock="<?php echo $stockVal; ?>"
                                           onchange="updatePriceAndStock(this)">
                                    <label class="btn btn-outline-gold w-100 py-2 small" for="size_<?php echo $sz; ?>"><?php echo $sz; ?></label>
                                </div>
                            <?php endif; endforeach; ?>
                        </div>
                    </div>

                    <!-- Quantity Selector & Stock Status -->
                    <div class="row align-items-center mb-4">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label text-white-50 small mb-2">Quantity</label>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary border-secondary text-white" type="button" onclick="adjustQty(-1)"><i class="bi bi-dash"></i></button>
                                <input type="number" id="qtyInput" class="form-control bg-transparent border-secondary text-center text-white" value="1" min="1" readonly>
                                <button class="btn btn-outline-secondary border-secondary text-white" type="button" onclick="adjustQty(1)"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-md-8 pt-4">
                            <span class="badge bg-success-subtle text-success py-2 px-3 border border-success" id="stockStatusBadge">In Stock</span>
                            <span class="small text-muted ms-2">SKU: <strong class="text-white-50" id="skuDisplay"><?php echo $product['sku']; ?></strong></span>
                        </div>
                    </div>

                    <!-- Purchase Actions buttons -->
                    <div class="d-flex flex-wrap gap-3">
                        <button type="button" class="btn btn-gold flex-fill py-3" onclick="handleAddToCartClick()">Add to Cart</button>
                        <button type="button" class="btn btn-outline-gold flex-fill py-3" onclick="handleBuyNowClick()">Buy Now</button>
                        
                        <!-- Wishlist & Compare Buttons -->
                        <button type="button" class="btn btn-outline-secondary border-secondary text-white px-3" data-wishlist-id="<?php echo $productId; ?>" onclick="toggleWishlist(<?php echo $productId; ?>)" title="Wishlist">
                            <i class="bi <?php echo $inWishlist ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary border-secondary text-white px-3" onclick="alert('Fragrance Compare: Added to queue. Try adding another perfume house element.')" title="Compare">
                            <i class="bi bi-arrow-left-right"></i>
                        </button>
                    </div>
                </form>

                <!-- Interactive Fragrance Note Profile Visualizer -->
                <div class="mb-5 animate-on-scroll">
                    <h5 class="font-heading text-white mb-3 text-luxury-glow">Fragrance Accord Profile</h5>
                    <div class="notes-container">
                        <div class="note-item">
                            <strong class="text-warning small text-uppercase tracking-widest"><i class="bi bi-wind me-2"></i>Top Notes</strong>
                            <div class="text-white mt-1 small"><?php echo $product['top_notes']; ?></div>
                        </div>
                        <div class="note-item">
                            <strong class="text-warning small text-uppercase tracking-widest"><i class="bi bi-heart-fill me-2"></i>Heart Notes (Middle)</strong>
                            <div class="text-white mt-1 small"><?php echo $product['middle_notes']; ?></div>
                        </div>
                        <div class="note-item">
                            <strong class="text-warning small text-uppercase tracking-widest"><i class="bi bi-flower1 me-2"></i>Base Notes</strong>
                            <div class="text-white mt-1 small"><?php echo $product['base_notes']; ?></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Product Tabbed Information (Ingredients, Reviews) -->
        <div class="row mt-5 animate-on-scroll">
            <div class="col-12">
                <ul class="nav nav-tabs border-secondary mb-4" id="detailTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link nav-link-luxury active border-0" id="ingredients-tab" data-bs-toggle="tab" data-bs-target="#ingredients-pane" type="button" role="tab" aria-controls="ingredients-pane" aria-selected="true">
                            Ingredients List
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link nav-link-luxury border-0" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-pane" type="button" role="tab" aria-controls="reviews-pane" aria-selected="false">
                            Reviews (<?php echo count($reviews); ?>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="detailTabsContent">
                    <!-- Ingredients Panel -->
                    <div class="tab-pane fade show active text-muted small" id="ingredients-pane" role="tabpanel" aria-labelledby="ingredients-tab">
                        <p class="lh-lg"><?php echo !empty($product['ingredients']) ? $product['ingredients'] : 'Standard premium ethanol content, aqua distillation base, essential botanical fragrance extracts.'; ?></p>
                    </div>

                    <!-- Reviews Tab -->
                    <div class="tab-pane fade" id="reviews-pane" role="tabpanel" aria-labelledby="reviews-tab">
                        <div class="row g-4">
                            <!-- Review List -->
                            <div class="col-md-7">
                                <h5 class="text-white font-heading mb-4">Customer Opinions</h5>
                                <div class="list-group list-group-flush">
                                    <?php if (count($reviews) > 0): ?>
                                        <?php foreach ($reviews as $rev): ?>
                                            <div class="list-group-item bg-transparent text-light border-secondary py-3 px-0">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <strong class="text-white"><?php echo htmlspecialchars($rev['customer_name'] ? $rev['customer_name'] : 'Verified Customer'); ?></strong>
                                                    <span class="small text-muted"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></span>
                                                </div>
                                                <div class="text-warning small mb-2">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="bi <?php echo ($i <= $rev['rating']) ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <p class="small text-muted m-0"><?php echo htmlspecialchars($rev['comment']); ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted small">No reviews posted yet for this masterpiece. Be the first to share your thoughts!</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Write a Review Form -->
                            <div class="col-md-5">
                                <div class="glass-card p-4">
                                    <h5 class="text-white font-heading mb-3">Write Review</h5>
                                    
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label class="form-label text-white-50 small">Your Rating</label>
                                            <div class="rating-star-select fs-4 text-warning cursor-pointer">
                                                <i class="bi bi-star"></i><i class="bi bi-star"></i><i class="bi bi-star"></i><i class="bi bi-star"></i><i class="bi bi-star"></i>
                                            </div>
                                            <input type="hidden" name="rating" id="reviewRatingInput" value="0" required>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="form-label text-white-50 small">Review Comment</label>
                                            <textarea name="comment" rows="4" class="form-control bg-transparent border-secondary text-white" placeholder="Describe the fragrance profile, durability and packaging..." required></textarea>
                                        </div>

                                        <button type="submit" name="submit_review" class="btn btn-gold w-100">Submit Review</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products Section -->
        <?php if (count($relatedProducts) > 0): ?>
            <div class="mt-5 pt-5 border-top border-secondary">
                <h3 class="font-heading text-white text-center mb-5">Related Masterpieces</h3>
                <div class="row g-4">
                    <?php foreach ($relatedProducts as $rel): ?>
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="product-card h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="product-image-wrap">
                                        <img src="<?php echo $rel['image_url']; ?>" alt="<?php echo $rel['name']; ?>">
                                        <div class="product-action-overlay">
                                            <button class="action-btn wishlist-btn-<?php echo $rel['id']; ?>" data-id="<?php echo $rel['id']; ?>" onclick="toggleWishlist(<?php echo $rel['id']; ?>)">
                                                <i class="bi bi-heart"></i>
                                            </button>
                                            <a href="product.php?id=<?php echo $rel['id']; ?>" class="action-btn"><i class="bi bi-eye"></i></a>
                                        </div>
                                    </div>
                                    <div class="small text-muted font-heading"><?php echo $rel['brand_name'] ? $rel['brand_name'] : 'AromaLuxe'; ?></div>
                                    <h5 class="text-white font-heading mt-1 mb-2 fs-6"><?php echo $rel['name']; ?></h5>
                                    <div class="text-warning small mb-3">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi <?php echo ($i <= round($rel['rating'])) ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="text-warning fw-bold">
                                        <?php echo formatPrice($rel['price_50ml'] - $rel['discount_50ml']); ?>
                                    </div>
                                    <button onclick="addToCart(<?php echo $rel['id']; ?>, '50ml')" class="btn btn-sm btn-gold px-3" style="font-size:0.75rem;">
                                        Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Page Specific Interaction JS -->
    <script>
        // Currency Symbol used for formatting
        const currencySymbol = '<?php echo $currencies[$current_currency]['symbol']; ?>';
        const exchangeRate = <?php echo $currencies[$current_currency]['rate']; ?>;

        function updatePriceAndStock(radioButton) {
            const priceVal = parseFloat(radioButton.getAttribute('data-price'));
            const strikeVal = parseFloat(radioButton.getAttribute('data-strike'));
            const hasDiscount = radioButton.getAttribute('data-has-discount') === '1';
            const stock = parseInt(radioButton.getAttribute('data-stock'));

            // Calculate converted price
            const finalPrice = priceVal * exchangeRate;
            const finalStrike = strikeVal * exchangeRate;

            document.getElementById('productPriceDisplay').innerText = currencySymbol + finalPrice.toFixed(2);

            const strikeDisplay = document.getElementById('productStrikeDisplay');
            if (hasDiscount) {
                strikeDisplay.innerText = currencySymbol + finalStrike.toFixed(2);
                strikeDisplay.style.display = 'inline';
            } else {
                strikeDisplay.style.display = 'none';
            }

            // Update Stock status badge
            const badge = document.getElementById('stockStatusBadge');
            if (stock > 0) {
                badge.className = "badge bg-success-subtle text-success py-2 px-3 border border-success";
                badge.innerText = "In Stock";
            } else {
                badge.className = "badge bg-danger-subtle text-danger py-2 px-3 border border-danger";
                badge.innerText = "Out of Stock";
            }
            // Reset quantity to 1
            document.getElementById('qtyInput').value = 1;
        }

        function adjustQty(amount) {
            const input = document.getElementById('qtyInput');
            let current = parseInt(input.value);
            current += amount;
            if (current < 1) current = 1;
            
            // Fetch selected size stock limit
            const checkedSize = document.querySelector('input[name="size"]:checked');
            if (checkedSize) {
                const maxStock = parseInt(checkedSize.getAttribute('data-stock'));
                if (current > maxStock) {
                    current = maxStock;
                    showToast("Stock Alert", "Only " + maxStock + " units available for this bottle size.", "warning");
                }
            }
            input.value = current;
        }

        function handleAddToCartClick() {
            const checkedSize = document.querySelector('input[name="size"]:checked');
            const qty = parseInt(document.getElementById('qtyInput').value);
            if (!checkedSize) {
                showToast("Selection Alert", "Please select a bottle size first.", "warning");
                return;
            }
            const size = checkedSize.value;
            const stock = parseInt(checkedSize.getAttribute('data-stock'));
            if (stock <= 0) {
                showToast("Stock Error", "This bottle size is currently sold out.", "danger");
                return;
            }

            addToCart(<?php echo $productId; ?>, size, qty);
        }

        function handleBuyNowClick() {
            const checkedSize = document.querySelector('input[name="size"]:checked');
            const qty = parseInt(document.getElementById('qtyInput').value);
            if (!checkedSize) return;
            const size = checkedSize.value;
            
            // Add to cart via AJAX first, then redirect to checkout
            fetch('api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=add&product_id=<?php echo $productId; ?>&size=${size}&quantity=${qty}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = 'checkout.php';
                }
            });
        }
    </script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
