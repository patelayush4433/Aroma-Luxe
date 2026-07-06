<?php
/**
 * Checkout Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth_check.php';

// Force authentication
checkCustomerAuth();

$customerId = $_SESSION['customer_id'];

// Check if cart is empty
$stmt = $pdo->prepare("SELECT COUNT(*) FROM `cart` WHERE customer_id = ?");
$stmt->execute([$customerId]);
if ($stmt->fetchColumn() == 0) {
    setFlashMessage("warning", "Your shopping cart is empty.");
    header("Location: cart.php");
    exit;
}

// Fetch Customer profile details
$stmt = $pdo->prepare("SELECT * FROM `customers` WHERE id = ?");
$stmt->execute([$customerId]);
$customer = $stmt->fetch();

// Fetch summary variables
$summary = $_SESSION['checkout_summary'] ?? null;
if (!$summary) {
    header("Location: cart.php");
    exit;
}

// Fetch Cart Products for list preview
$stmt = $pdo->prepare("
    SELECT c.*, p.name as product_name, p.price_30ml, p.price_50ml, p.price_100ml,
           p.discount_30ml, p.discount_50ml, p.discount_100ml
    FROM `cart` c
    LEFT JOIN `products` p ON c.product_id = p.id
    WHERE c.customer_id = ?
");
$stmt->execute([$customerId]);
$cartItems = $stmt->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_name = sanitize($_POST['shipping_name']);
    $shipping_email = sanitize($_POST['shipping_email']);
    $shipping_phone = sanitize($_POST['shipping_phone']);
    $shipping_address = sanitize($_POST['shipping_address']);
    $same_as_shipping = isset($_POST['same_as_shipping']) ? 1 : 0;
    
    $billing_address = $same_as_shipping ? $shipping_address : sanitize($_POST['billing_address']);
    $delivery_date = sanitize($_POST['delivery_date']);
    $delivery_time = sanitize($_POST['delivery_time']);
    $payment_method = sanitize($_POST['payment_method']);

    if (empty($shipping_name) || empty($shipping_email) || empty($shipping_phone) || empty($shipping_address) || empty($delivery_date) || empty($payment_method)) {
        $error = "Please fill in all required shipping, delivery and payment details.";
    } else {
        // Save shipping options to session
        $_SESSION['checkout_shipping'] = [
            'name' => $shipping_name,
            'email' => $shipping_email,
            'phone' => $shipping_phone,
            'address' => $shipping_address,
            'billing' => $billing_address,
            'delivery_date' => $delivery_date,
            'delivery_time' => $delivery_time,
            'payment_method' => $payment_method
        ];

        // Redirect to gateway selection
        header("Location: payment-gateway.php");
        exit;
    }
}

include_once __DIR__ . '/includes/header.php';
?>

    <!-- Page Title Header -->
    <div class="py-5 bg-black border-bottom border-secondary text-center">
        <div class="container">
            <span class="text-warning text-uppercase small tracking-widest">Order Details</span>
            <h1 class="luxury-font text-white display-5 mt-2">Checkout Delivery</h1>
            <div class="mx-auto bg-warning mt-3" style="width: 50px; height: 1.5px;"></div>
        </div>
    </div>

    <!-- Main Checkout container -->
    <div class="container py-5">
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2 mb-4"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="row g-5">
                <!-- Left: Shipping and Delivery details -->
                <div class="col-lg-7">
                    
                    <!-- Section 1: Shipping Address -->
                    <div class="glass-card p-4 mb-4">
                        <h5 class="font-heading text-white mb-4"><i class="bi bi-geo-alt-fill text-warning me-2"></i>Shipping Address</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small">Recipient Name *</label>
                                <input type="text" name="shipping_name" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small">Recipient Email *</label>
                                <input type="email" name="shipping_email" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-white-50 small">Contact Phone Number *</label>
                                <input type="tel" name="shipping_phone" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($customer['phone']); ?>" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-white-50 small">Street Address, Apartment, Suite *</label>
                                <textarea name="shipping_address" rows="3" class="form-control bg-transparent border-secondary text-white" placeholder="e.g. 720 Fifth Avenue, Floor 4, New York, NY 10019" required></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Billing Address -->
                    <div class="glass-card p-4 mb-4">
                        <h5 class="font-heading text-white mb-3"><i class="bi bi-receipt-cutoff text-warning me-2"></i>Billing Address</h5>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input bg-dark border-secondary" type="checkbox" name="same_as_shipping" id="sameAsShippingCheck" value="1" checked onchange="toggleBillingArea(this.checked)">
                            <label class="form-check-label text-white-50 small" for="sameAsShippingCheck">Billing address is same as shipping</label>
                        </div>

                        <div id="billingAddressArea" style="display: none;">
                            <label class="form-label text-white-50 small">Billing Address</label>
                            <textarea name="billing_address" id="billingAddressTextarea" rows="3" class="form-control bg-transparent border-secondary text-white" placeholder="Enter billing details if different..."></textarea>
                        </div>
                    </div>

                    <!-- Section 3: Delivery Scheduling -->
                    <div class="glass-card p-4 mb-4">
                        <h5 class="font-heading text-white mb-4"><i class="bi bi-calendar-event-fill text-warning me-2"></i>Delivery Slot Schedule</h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small">Delivery Date *</label>
                                <!-- Restrict past dates using min attribute -->
                                <input type="date" name="delivery_date" class="form-control bg-transparent border-secondary text-white" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small">Preferred Time Window *</label>
                                <select name="delivery_time" class="form-select bg-dark border-secondary text-white" required>
                                    <option value="Standard Hours (9:00 AM - 5:00 PM)">Standard (9 AM - 5 PM)</option>
                                    <option value="Express Noon (12:00 PM - 3:00 PM)">Express (12 PM - 3 PM)</option>
                                    <option value="Evening Luxury Slot (6:00 PM - 9:00 PM)">Evening (6 PM - 9 PM)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Payment Method Gateway -->
                    <div class="glass-card p-4">
                        <h5 class="font-heading text-white mb-4"><i class="bi bi-credit-card-fill text-warning me-2"></i>Select Payment Method</h5>
                        
                        <div class="row g-3">
                            <!-- Cards -->
                            <div class="col-md-6">
                                <div class="border border-secondary p-3 rounded bg-black d-flex align-items-center gap-3 cursor-pointer" onclick="document.getElementById('pay_stripe').click()">
                                    <input type="radio" name="payment_method" id="pay_stripe" value="Stripe" checked>
                                    <label for="pay_stripe" class="text-white small fw-bold cursor-pointer">Stripe Gateway</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border border-secondary p-3 rounded bg-black d-flex align-items-center gap-3 cursor-pointer" onclick="document.getElementById('pay_paypal').click()">
                                    <input type="radio" name="payment_method" id="pay_paypal" value="PayPal">
                                    <label for="pay_paypal" class="text-white small fw-bold cursor-pointer">PayPal Portal</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border border-secondary p-3 rounded bg-black d-flex align-items-center gap-3 cursor-pointer" onclick="document.getElementById('pay_razorpay').click()">
                                    <input type="radio" name="payment_method" id="pay_razorpay" value="Razorpay">
                                    <label for="pay_razorpay" class="text-white small fw-bold cursor-pointer">Razorpay (Cards/Net)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border border-secondary p-3 rounded bg-black d-flex align-items-center gap-3 cursor-pointer" onclick="document.getElementById('pay_qr').click()">
                                    <input type="radio" name="payment_method" id="pay_qr" value="UPI QR Code">
                                    <label for="pay_qr" class="text-white small fw-bold cursor-pointer">UPI QR Code Scanner</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border border-secondary p-3 rounded bg-black d-flex align-items-center gap-3 cursor-pointer" onclick="document.getElementById('pay_cod').click()">
                                    <input type="radio" name="payment_method" id="pay_cod" value="Cash on Delivery">
                                    <label for="pay_cod" class="text-white small fw-bold cursor-pointer">Cash on Delivery (COD)</label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right: Order Summary block -->
                <div class="col-lg-5">
                    <div class="glass-card p-4 sticky-lg-top" style="top: 100px;">
                        <h5 class="font-heading text-white mb-4">Summary</h5>

                        <!-- Order items line preview -->
                        <div class="list-group list-group-flush mb-4" style="max-height: 200px; overflow-y: auto;">
                            <?php foreach ($cartItems as $cItem): 
                                $size = $cItem['size'];
                                $pRaw = (float)$cItem['price_' . $size];
                                $dRaw = (float)$cItem['discount_' . $size];
                                $finalUnit = $pRaw - $dRaw;
                            ?>
                                <div class="list-group-item bg-transparent text-light border-secondary py-2 px-0 d-flex justify-content-between align-items-center small">
                                    <div>
                                        <div class="fw-bold text-white"><?php echo $cItem['product_name']; ?></div>
                                        <span class="text-muted"><?php echo $size; ?> x <?php echo $cItem['quantity']; ?></span>
                                    </div>
                                    <span class="text-warning fw-bold"><?php echo formatPrice($finalUnit * $cItem['quantity']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <hr class="bg-secondary mb-3">

                        <!-- Summary charges totals -->
                        <div class="d-flex justify-content-between mb-2 small text-secondary">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($summary['subtotal']); ?></span>
                        </div>
                        <?php if ($summary['discount'] > 0): ?>
                            <div class="d-flex justify-content-between mb-2 small text-success">
                                <span>Discount (<?php echo $summary['coupon_code']; ?>):</span>
                                <span>-<?php echo formatPrice($summary['discount']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($summary['giftwrap'] > 0): ?>
                            <div class="d-flex justify-content-between mb-2 small text-secondary">
                                <span>Gift Wrapping:</span>
                                <span><?php echo formatPrice($summary['giftwrap']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-2 small text-secondary">
                            <span>Shipping & Handling:</span>
                            <span><?php echo $summary['shipping'] > 0 ? formatPrice($summary['shipping']) : '<span class="text-success fw-bold">FREE</span>'; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 small text-secondary">
                            <span>GST Tax (18%):</span>
                            <span><?php echo formatPrice($summary['gst']); ?></span>
                        </div>

                        <hr class="bg-secondary mb-4">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="font-heading text-white">Final Amount:</span>
                            <span class="fs-3 text-warning fw-bold font-heading"><?php echo formatPrice($summary['final_amount']); ?></span>
                        </div>

                        <button type="submit" class="btn btn-gold w-100 py-3 text-uppercase font-heading" style="letter-spacing: 1.5px;">Place Order</button>
                        
                        <div class="text-center mt-3">
                            <a href="cart.php" class="small text-muted text-white-hover"><i class="bi bi-arrow-left me-1"></i> Back to Cart</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Toggle Billing display scripts -->
    <script>
        function toggleBillingArea(sameAsShipping) {
            const area = document.getElementById('billingAddressArea');
            const txt = document.getElementById('billingAddressTextarea');
            if (sameAsShipping) {
                area.style.display = 'none';
                txt.required = false;
            } else {
                area.style.display = 'block';
                txt.required = true;
            }
        }
    </script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
