<?php
/**
 * Interactive Payment Gateway Simulator
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth_check.php';

// Force authentication
checkCustomerAuth();

$customerId = $_SESSION['customer_id'];
$shipping = $_SESSION['checkout_shipping'] ?? null;
$summary = $_SESSION['checkout_summary'] ?? null;

if (!$shipping || !$summary) {
    header("Location: checkout.php");
    exit;
}

// Fetch Cart Products
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
    if (isset($_POST['simulate_fail'])) {
        // Redirect to payment failed page
        header("Location: payment-failed.php");
        exit;
    }

    if (isset($_POST['confirm_payment'])) {
        try {
            $pdo->beginTransaction();

            // 1. Generate Order details
            $orderNumber = 'ALX-' . strtoupper(bin2hex(random_bytes(4)));
            $couponCode = !empty($summary['coupon_code']) ? $summary['coupon_code'] : null;
            $giftwrap = $summary['giftwrap'] > 0 ? 1 : 0;
            $paymentStatus = ($shipping['payment_method'] === 'Cash on Delivery') ? 'Pending' : 'Completed';

            $stmt = $pdo->prepare("
                INSERT INTO `orders` (
                    customer_id, order_number, total_amount, discount_amount, gst_amount, shipping_fee, final_amount,
                    coupon_code, gift_wrap, shipping_name, shipping_email, shipping_phone, shipping_address, billing_address,
                    delivery_date, delivery_time, order_status, payment_method, payment_status
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, 'Pending', ?, ?
                )
            ");
            $stmt->execute([
                $customerId, $orderNumber, $summary['subtotal'], $summary['discount'], $summary['gst'], $summary['shipping'], $summary['final_amount'],
                $couponCode, $giftwrap, $shipping['name'], $shipping['email'], $shipping['phone'], $shipping['address'], $shipping['billing'],
                $shipping['delivery_date'], $shipping['delivery_time'], $shipping['payment_method'], $paymentStatus
            ]);

            $orderId = $pdo->lastInsertId();

            // 2. Add Order Line items and update inventory stock levels
            foreach ($cartItems as $item) {
                $pId = $item['product_id'];
                $size = $item['size'];
                $qty = (int)$item['quantity'];

                $priceField = 'price_' . $size;
                $discField = 'discount_' . $size;
                $stockField = 'stock_' . $size;

                $unitPrice = (float)$item[$priceField] - (float)$item[$discField];
                $unitDiscount = (float)$item[$discField];

                // Insert Line item
                $lineTotal = $unitPrice * $qty;
                $insItem = $pdo->prepare("INSERT INTO `order_items` (order_id, product_id, size, quantity, price, discount, line_total) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insItem->execute([$orderId, $pId, $size, $qty, $unitPrice, $unitDiscount, $lineTotal]);

                // Update product stock levels
                $upStock = $pdo->prepare("UPDATE `products` SET `$stockField` = `$stockField` - ? WHERE id = ?");
                $upStock->execute([$qty, $pId]);

                // Record inventory movement logs
                $insInvLog = $pdo->prepare("INSERT INTO `inventory` (product_id, size, change_type, quantity, notes) VALUES (?, ?, 'sale', ?, ?)");
                $insInvLog->execute([$pId, $size, $qty, "Order ID: #$orderId ($orderNumber)"]);
            }

            // 3. Clear Shopping Cart
            $stmt = $pdo->prepare("DELETE FROM `cart` WHERE customer_id = ?");
            $stmt->execute([$customerId]);

            // 4. Record Payment transaction
            $txnId = 'TXN-' . strtoupper(bin2hex(random_bytes(5)));
            $insPayment = $pdo->prepare("INSERT INTO `payments` (order_id, transaction_id, payment_method, amount, payment_status) VALUES (?, ?, ?, ?, ?)");
            $insPayment->execute([$orderId, $txnId, $shipping['payment_method'], $summary['final_amount'], $paymentStatus]);

            // 5. Credit Loyalty points to customer
            $loyaltyMultiplier = (int)($settings['loyalty_multiplier'] ?? 10);
            $pointsEarned = (int)($summary['final_amount'] * $loyaltyMultiplier);
            $upLoyalty = $pdo->prepare("UPDATE `customers` SET loyalty_points = loyalty_points + ? WHERE id = ?");
            $upLoyalty->execute([$pointsEarned, $customerId]);

            $pdo->commit();

            // 6. Dispatch simulated notifications
            $emailMsg = "Hello " . $shipping['name'] . ",\n\nYour order at AromaLuxe has been processed successfully!\n\nOrder Details:\nOrder Number: " . $orderNumber . "\nTotal Amount: " . formatPrice($summary['final_amount']) . "\nPayment Method: " . $shipping['payment_method'] . "\nEstimated Delivery Date: " . date('M d, Y', strtotime($shipping['delivery_date'])) . "\nDelivery Slot: " . $shipping['delivery_time'];
            if ($shipping['payment_method'] === 'Cash on Delivery') {
                $emailMsg .= "\n\nNote: You have selected Cash on Delivery. Please pay " . formatPrice($summary['final_amount']) . " in cash to our courier associate when you receive your package.";
            } else {
                $emailMsg .= "\nPayment Status: Paid (Simulated)";
            }
            $emailMsg .= "\n\nWe will update you once your package is Shipped. Enjoy botanical luxury!";

            $smsMsg = "Order confirmed! Your AromaLuxe order " . $orderNumber . " of " . formatPrice($summary['final_amount']) . " is placed via " . $shipping['payment_method'] . ". ";
            if ($shipping['payment_method'] === 'Cash on Delivery') {
                $smsMsg .= "Amount will be collected upon delivery. ";
            } else {
                $smsMsg .= "Paid successfully. ";
            }
            $smsMsg .= "Delivery scheduled for " . date('M d, Y', strtotime($shipping['delivery_date'])) . ".";

            // Email Notification
            sendSimulatedNotification(
                'Email',
                $shipping['name'] . ' <' . $shipping['email'] . '>',
                'Order Confirmation - ' . $orderNumber,
                $emailMsg
            );

            // SMS Notification
            sendSimulatedNotification(
                'SMS',
                $shipping['phone'],
                'AromaLuxe Order Alert',
                $smsMsg
            );

            // Save order ID details in session
            $_SESSION['last_placed_order_id'] = $orderId;
            
            // Clear temporary checkouts
            unset($_SESSION['checkout_shipping']);
            unset($_SESSION['checkout_summary']);

            header("Location: payment-success.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Order processing failed. " . $e->getMessage();
        }
    }
}

include_once __DIR__ . '/includes/header.php';
?>

    <!-- Page Title Header -->
    <div class="py-5 bg-black border-bottom border-secondary text-center">
        <div class="container">
            <span class="text-warning text-uppercase small tracking-widest">AromaLuxe Vault</span>
            <h1 class="luxury-font text-white display-5 mt-2">Secure Checkout Payment</h1>
            <div class="mx-auto bg-warning mt-3" style="width: 50px; height: 1.5px;"></div>
        </div>
    </div>

    <!-- Main Payment Gateways content -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2 mb-4"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="glass-card p-4 p-md-5">
                    <div class="text-center mb-4">
                        <span class="text-muted small">Merchant: <strong>AromaLuxe Boutique</strong></span>
                        <h3 class="text-warning font-heading mt-2">Amount to Pay: <?php echo formatPrice($summary['final_amount']); ?></h3>
                        <span class="badge bg-secondary text-white-50 mt-1 small">Gateway: <?php echo $shipping['payment_method']; ?></span>
                    </div>

                    <form method="POST" action="">
                        <!--Cash on Delivery gateway-->
                        <?php if ($shipping['payment_method'] === 'Cash on Delivery'): ?>
                            <div class="alert alert-info bg-info-subtle text-dark border-0 p-3 small mb-4">
                                <i class="bi bi-info-circle-fill me-2 text-info"></i>
                                <strong>Cash on Delivery (COD):</strong> Your order is ready to be finalized. No payment is required right now. The total amount will be collected in cash by our courier associate upon delivery.
                            </div>
                            <div class="text-center mb-4 p-4 border border-secondary rounded bg-black">
                                <i class="bi bi-cash-stack fs-1 text-warning"></i>
                                <div class="text-white mt-2 small">Total Collectable: <strong><?php echo formatPrice($summary['final_amount']); ?></strong></div>
                            </div>
                        <?php endif; ?>

                        <!--stripe entry layout-->
                        <?php if ($shipping['payment_method'] === 'Stripe'): ?>
                            <div class="mb-3">
                                <label class="form-label text-white-50 small">Cardholder Name</label>
                                <input type="text" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($shipping['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white-50 small">Card Number</label>
                                <input type="text" class="form-control bg-transparent border-secondary text-white font-monospace text-center fs-5" placeholder="4111 2222 3333 4444" maxlength="19" required>
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <label class="form-label text-white-50 small">Expiry (MM/YY)</label>
                                    <input type="text" class="form-control bg-transparent border-secondary text-white text-center font-monospace" placeholder="12/28" maxlength="5" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-white-50 small">CVC / CVV</label>
                                    <input type="password" class="form-control bg-transparent border-secondary text-white text-center font-monospace" placeholder="•••" maxlength="3" required>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!--paypal entry-->
                        <?php if ($shipping['payment_method'] === 'PayPal'): ?>
                            <div class="alert alert-warning bg-warning-subtle text-dark border-0 p-3 small mb-4">
                                <strong>PayPal Gateway Integration:</strong> Clicking below simulates logging in to your PayPal credentials panel to execute transactions securely.
                            </div>
                            <div class="text-center mb-4 p-4 border border-secondary rounded bg-black">
                                <i class="bi bi-paypal fs-1 text-primary"></i>
                                <div class="text-white mt-2 small">Sophia Loren (sophia@example.com)</div>
                            </div>
                        <?php endif; ?>

                        <!--razorpay gateway-->
                        <?php if ($shipping['payment_method'] === 'Razorpay'): ?>
                            <div class="mb-3">
                                <label class="form-label text-white-50 small">Choose Preferred Netbanking Node</label>
                                <select class="form-select bg-dark border-secondary text-white">
                                    <option>State Bank of India</option>
                                    <option>HDFC Bank</option>
                                    <option>ICICI Bank</option>
                                    <option>Axis Bank</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-white-50 small">UPI VPA Address</label>
                                <input type="text" class="form-control bg-transparent border-secondary text-white font-monospace" placeholder="sophia@okhdfcbank">
                            </div>
                        <?php endif; ?>

                        <!--upi qr code gateway-->
                        <?php if ($shipping['payment_method'] === 'UPI QR Code'): ?>
                            <div class="text-center mb-4">
                                <p class="small text-muted mb-3">Scan the QR code using any UPI app (Google Pay, PhonePe, Paytm, BHIM) to pay.</p>
                                
                                <div class="bg-black-50 p-3 rounded border border-secondary mb-3">
                                    <div class="p-3 bg-white d-inline-block rounded shadow">
                                        <!-- User's uploaded custom QR code -->
                                        <img src="assets/images/payment_qr.jpg" alt="Static UPI QR Code" style="width: 180px; height: 180px; object-fit: contain;">
                                    </div>
                                    <p class="small text-white-50 mt-2 mb-0">Scan and enter the final amount manually.</p>
                                </div>

                                <!-- UPI ID Copy block -->
                                <div class="d-flex align-items-center justify-content-center gap-2 mb-2 bg-dark border border-secondary rounded p-2 mx-auto" style="max-width: 320px;">
                                    <span class="small text-white-50 font-monospace" id="upiIdText">7284077032@fam</span>
                                    <button type="button" class="btn btn-sm btn-outline-warning py-0 px-2" onclick="copyUPIAddress()" style="font-size: 0.75rem;"><i class="bi bi-copy"></i> Copy</button>
                                </div>
                                <span id="copyFeedback" class="small text-success d-block mb-3" style="display:none; font-size: 0.8rem;"></span>

                                <div class="text-danger small mt-2">
                                    <i class="bi bi-clock-history me-1"></i> QR Expires in: <strong id="timerText">02:00</strong>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex gap-3">
                            <button type="submit" name="confirm_payment" class="btn btn-gold flex-fill py-3 font-heading text-uppercase tracking-wider">
                                <?php echo ($shipping['payment_method'] === 'Cash on Delivery') ? 'Confirm Order' : 'Confirm Payment'; ?>
                            </button>
                            <?php if ($shipping['payment_method'] !== 'Cash on Delivery'): ?>
                                <button type="submit" name="simulate_fail" class="btn btn-outline-danger py-3 font-heading text-uppercase px-3" title="Simulate Declined Card"><i class="bi bi-x-circle"></i> Decline</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Countdown Timer for UPI QR Code -->
    <?php if ($shipping['payment_method'] === 'UPI QR Code'): ?>
        <script>
            let duration = 120; // 2 mins
            const timerEl = document.getElementById('timerText');
            
            const timer = setInterval(() => {
                let mins = Math.floor(duration / 60);
                let secs = duration % 60;
                
                mins = mins < 10 ? '0' + mins : mins;
                secs = secs < 10 ? '0' + secs : secs;
                
                timerEl.innerText = mins + ':' + secs;
                
                if (duration <= 0) {
                    clearInterval(timer);
                    alert("QR Code expired. Redirecting to payment failed page.");
                    window.location.href = 'payment-failed.php';
                }
                duration--;
            }, 1000);

            // Copy UPI address helper
            function copyUPIAddress() {
                const upiText = document.getElementById('upiIdText').innerText;
                navigator.clipboard.writeText(upiText).then(() => {
                    const feedback = document.getElementById('copyFeedback');
                    feedback.style.display = 'block';
                    feedback.innerText = 'UPI VPA Copied successfully!';
                    setTimeout(() => {
                        feedback.style.display = 'none';
                    }, 2000);
                }).catch(err => {
                    console.error('Failed to copy text: ', err);
                });
            }

            // Tab styling active toggle script (removed)
        </script>
    <?php endif; ?>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
