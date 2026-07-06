<?php
/**
 * Shopping Cart Page
 */
require_once __DIR__ . '/config/config.php';

$cartItems = [];
$subtotal = 0.00;

// Fetch Cart Products
if (isset($_SESSION['customer_id'])) {
    // Registered Customer
    $stmt = $pdo->prepare("
        SELECT c.*, p.name as product_name, p.image_url, p.sku, p.price_30ml, p.price_50ml, p.price_100ml,
               p.discount_30ml, p.discount_50ml, p.discount_100ml, b.name as brand_name
        FROM `cart` c
        LEFT JOIN `products` p ON c.product_id = p.id
        LEFT JOIN `brands` b ON p.brand_id = b.id
        WHERE c.customer_id = ?
    ");
    $stmt->execute([$_SESSION['customer_id']]);
    $dbItems = $stmt->fetchAll();

    foreach ($dbItems as $row) {
        $size = $row['size'];
        $price = (float)$row['price_' . $size];
        $disc = (float)$row['discount_' . $size];
        $finalPrice = $price - $disc;
        $qty = (int)$row['quantity'];
        
        $itemTotal = $finalPrice * $qty;
        $subtotal += $itemTotal;

        $cartItems[] = [
            'product_id' => $row['product_id'],
            'name' => $row['product_name'],
            'brand' => $row['brand_name'],
            'image' => $row['image_url'],
            'size' => $size,
            'price' => $finalPrice,
            'quantity' => $qty,
            'total' => $itemTotal
        ];
    }
} else {
    // Guest Session Cart
    if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
        foreach ($_SESSION['cart'] as $key => $sessionItem) {
            $pId = $sessionItem['product_id'];
            $size = $sessionItem['size'];
            $qty = $sessionItem['quantity'];

            $stmt = $pdo->prepare("SELECT p.*, b.name as brand_name FROM `products` p LEFT JOIN `brands` b ON p.brand_id = b.id WHERE p.id = ?");
            $stmt->execute([$pId]);
            $product = $stmt->fetch();

            if ($product) {
                $price = (float)$product['price_' . $size];
                $disc = (float)$product['discount_' . $size];
                $finalPrice = $price - $disc;
                $itemTotal = $finalPrice * $qty;
                $subtotal += $itemTotal;

                $cartItems[] = [
                    'product_id' => $pId,
                    'name' => $product['name'],
                    'brand' => $product['brand_name'],
                    'image' => $product['image_url'],
                    'size' => $size,
                    'price' => $finalPrice,
                    'quantity' => $qty,
                    'total' => $itemTotal
                ];
            }
        }
    }
}

// Calculate Summary values
$discount = 0.00;
$couponCodeApplied = '';

if (isset($_SESSION['coupon'])) {
    $coupon = $_SESSION['coupon'];
    if ($subtotal >= $coupon['min_spend']) {
        $couponCodeApplied = $coupon['code'];
        if ($coupon['type'] === 'percentage') {
            $discount = $subtotal * ($coupon['value'] / 100);
        } else {
            $discount = $coupon['value'];
        }
    } else {
        // Remove coupon if spend requirements not met anymore
        unset($_SESSION['coupon']);
        setFlashMessage("warning", "Coupon removed. Spend limit not met.");
    }
}

// Gift Wrapping
$giftwrapFee = 0.00;
$hasGiftwrap = isset($_SESSION['giftwrap']) && $_SESSION['giftwrap'] == 1;
if ($hasGiftwrap) {
    $giftwrapFee = 5.00;
}

// Shipping Charges: Free above ₹8,350, otherwise ₹1,245.00
$shippingFee = ($subtotal - $discount >= 100.00 || $subtotal == 0) ? 0.00 : 15.00;

// GST Calculation: 18% of taxable amount
$taxableAmount = $subtotal - $discount;
if ($taxableAmount < 0) $taxableAmount = 0;
$gstAmount = $taxableAmount * 0.18;

// Final Amount calculation
$finalAmount = $taxableAmount + $giftwrapFee + $shippingFee + $gstAmount;

// Store calculations in session for checkout confirmation
$_SESSION['checkout_summary'] = [
    'subtotal' => $subtotal,
    'discount' => $discount,
    'coupon_code' => $couponCodeApplied,
    'giftwrap' => $giftwrapFee,
    'shipping' => $shippingFee,
    'gst' => $gstAmount,
    'final_amount' => $finalAmount
];

include_once __DIR__ . '/includes/header.php';
?>

    <!-- Page Title Header -->
    <div class="py-5 bg-black border-bottom border-secondary text-center">
        <div class="container">
            <span class="text-warning text-uppercase small tracking-widest">Cart Summary</span>
            <h1 class="luxury-font text-white display-5 mt-2">Shopping Bag</h1>
            <div class="mx-auto bg-warning mt-3" style="width: 50px; height: 1.5px;"></div>
        </div>
    </div>

    <!-- Main Shopping Cart Details -->
    <div class="container py-5">
        <?php if (count($cartItems) > 0): ?>
            <div class="row g-5">
                
                <!-- Items Table list -->
                <div class="col-lg-8">
                    <div class="glass-card p-4 mb-4">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle m-0" style="--bs-table-bg: transparent; --bs-table-border-color: rgba(212,175,55,0.1);">
                                <thead>
                                    <tr class="text-warning font-heading small text-uppercase">
                                        <th>Product</th>
                                        <th class="text-center">Size</th>
                                        <th class="text-center" style="width: 140px;">Quantity</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                     <?php 
                                                     $customName = '';
                                                     if (strpos($item['name'], 'Bespoke: ') === 0) {
                                                         $customName = substr($item['name'], 9);
                                                     }
                                                     ?>
                                                     <div class="position-relative d-inline-block bg-black rounded border border-secondary" style="width: 60px; height: 60px; overflow: hidden;">
                                                         <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" style="width: 100%; height: 100%; object-fit: contain;">
                                                         <?php if (!empty($customName)): ?>
                                                             <div class="position-absolute text-center text-white" style="bottom: 12px; left: 10%; right: 10%; font-family: 'Cinzel', serif; font-size: 5px; font-weight: 700; letter-spacing: 0.2px; text-transform: uppercase; color: #ffffff; text-shadow: 0 1px 2px #000; word-break: break-all; line-height: 1;">
                                                                 <?php echo htmlspecialchars($customName); ?>
                                                             </div>
                                                         <?php endif; ?>
                                                     </div>
                                                    <div>
                                                        <div class="fw-bold text-white small"><?php echo $item['name']; ?></div>
                                                        <div class="text-muted small"><?php echo $item['brand']; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center text-white-50 small"><?php echo $item['size']; ?></td>
                                            <td class="text-center">
                                                <div class="input-group input-group-sm">
                                                    <button class="btn btn-outline-secondary border-secondary text-white py-0 px-2" onclick="updateItemQty(<?php echo $item['product_id']; ?>, '<?php echo $item['size']; ?>', <?php echo $item['quantity'] - 1; ?>)">-</button>
                                                    <input type="text" class="form-control bg-transparent border-secondary text-center text-white py-0 small" value="<?php echo $item['quantity']; ?>" readonly>
                                                    <button class="btn btn-outline-secondary border-secondary text-white py-0 px-2" onclick="updateItemQty(<?php echo $item['product_id']; ?>, '<?php echo $item['size']; ?>', <?php echo $item['quantity'] + 1; ?>)">+</button>
                                                </div>
                                            </td>
                                            <td class="text-end text-white-50 small"><?php echo formatPrice($item['price']); ?></td>
                                            <td class="text-end text-warning fw-bold small"><?php echo formatPrice($item['total']); ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-link text-danger p-0" onclick="removeItemFromCart(<?php echo $item['product_id']; ?>, '<?php echo $item['size']; ?>')" title="Remove">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Gift Wrapping and Coupon Selection -->
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="glass-card p-4 h-100">
                                <h6 class="font-heading text-white mb-3">Premium Gift Wrapping</h6>
                                <p class="small text-secondary mb-3">Include our signature velvet perfume sleeve, gold-stamped box, and handwritten card. (₹415.00 Flat)</p>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input bg-dark border-secondary" type="checkbox" role="switch" id="giftwrapSwitch" <?php echo $hasGiftwrap ? 'checked' : ''; ?> onchange="toggleGiftwrapOption(this.checked)">
                                    <label class="form-check-label text-warning small font-heading" for="giftwrapSwitch">Add Gift Wrap</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="glass-card p-4 h-100">
                                <h6 class="font-heading text-white mb-3">Apply Coupon</h6>
                                <?php if (!empty($couponCodeApplied)): ?>
                                    <div class="alert alert-success bg-success-subtle border-0 text-success p-2 small d-flex justify-content-between align-items-center">
                                        <span>Coupon <strong><?php echo $couponCodeApplied; ?></strong> Applied!</span>
                                        <a href="#" class="text-danger small" onclick="removeCouponApplied(); return false;">Remove</a>
                                    </div>
                                <?php else: ?>
                                    <p class="small text-secondary mb-3">Enter coupon code to unlock exclusive discounts.</p>
                                    <div class="input-group">
                                        <input type="text" id="couponCodeInput" class="form-control bg-transparent border-secondary text-white small" placeholder="e.g. LUXURY10">
                                        <button class="btn btn-gold py-1" onclick="applyCouponCode()">Apply</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Pricing Totals Summary -->
                <div class="col-lg-4">
                    <div class="glass-card p-4 sticky-lg-top" style="top: 100px;">
                        <h5 class="font-heading text-white mb-4">Summary</h5>

                        <div class="d-flex justify-content-between mb-3 small text-secondary">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>

                        <?php if ($discount > 0): ?>
                            <div class="d-flex justify-content-between mb-3 small text-success">
                                <span>Discount (Coupon):</span>
                                <span>-<?php echo formatPrice($discount); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($hasGiftwrap): ?>
                            <div class="d-flex justify-content-between mb-3 small text-secondary">
                                <span>Gift Wrap:</span>
                                <span><?php echo formatPrice($giftwrapFee); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between mb-3 small text-secondary">
                            <span>Shipping / Delivery:</span>
                            <span><?php echo $shippingFee > 0 ? formatPrice($shippingFee) : '<strong class="text-success">FREE</strong>'; ?></span>
                        </div>

                        <div class="d-flex justify-content-between mb-3 small text-secondary">
                            <span>GST Tax (18%):</span>
                            <span><?php echo formatPrice($gstAmount); ?></span>
                        </div>

                        <hr class="bg-secondary my-3">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="font-heading text-white">Estimated Total:</span>
                            <span class="fs-4 text-warning fw-bold font-heading"><?php echo formatPrice($finalAmount); ?></span>
                        </div>

                        <a href="checkout.php" class="btn btn-gold w-100 py-3 text-uppercase font-heading" style="letter-spacing: 1px;">Proceed to Checkout</a>
                        
                        <div class="text-center mt-3">
                            <a href="shop.php" class="small text-secondary text-white-hover"><i class="bi bi-arrow-left me-1"></i> Continue Shopping</a>
                        </div>
                    </div>
                </div>

            </div>
        <?php else: ?>
            <div class="py-5 text-center">
                <i class="bi bi-bag-x fs-1 text-muted"></i>
                <h4 class="font-heading text-white mt-3">Your Bag is Empty</h4>
                <p class="text-muted small">Choose a fragrance masterpiece from our catalog to add here.</p>
                <a href="shop.php" class="btn btn-gold mt-2">Explore Fragrances</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Page Specific Operations JavaScript -->
    <script>
        function applyCouponCode() {
            const code = document.getElementById('couponCodeInput').value.trim();
            if (code === '') return;

            fetch('api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=coupon&code=${encodeURIComponent(code)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.reload();
                } else {
                    showToast("Coupon Error", data.message, "danger");
                }
            });
        }

        function removeCouponApplied() {
            fetch('api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=remove_coupon`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.reload();
                }
            });
        }

        function toggleGiftwrapOption(isChecked) {
            const state = isChecked ? '1' : '0';
            fetch('api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=giftwrap&giftwrap=${state}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.reload();
                }
            });
        }
    </script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
