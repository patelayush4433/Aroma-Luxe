<?php
/**
 * Payment Success Landing Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth_check.php';

checkCustomerAuth();

$orderId = $_SESSION['last_placed_order_id'] ?? 0;

if ($orderId <= 0) {
    header("Location: index.php");
    exit;
}

// Fetch Order info
$stmt = $pdo->prepare("SELECT * FROM `orders` WHERE id = ? AND customer_id = ?");
$stmt->execute([$orderId, $_SESSION['customer_id']]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: index.php");
    exit;
}

include_once __DIR__ . '/includes/header.php';
?>

    <div class="container py-5 text-center my-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                
                <div class="glass-card p-5 border border-warning shadow-lg">
                    <!-- Animated Check icon -->
                    <div class="mb-4">
                        <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center border border-warning bg-black text-warning" style="width: 80px; height: 80px; font-size: 2.5rem; box-shadow: 0 0 20px rgba(212,175,55,0.4);">
                            <i class="bi bi-check-lg"></i>
                        </div>
                    </div>

                    <span class="text-warning font-heading text-uppercase small tracking-widest">Transaction Successful</span>
                    <h2 class="font-heading text-white mt-2 mb-3">Thank You For Your Order!</h2>
                    
                    <p class="text-secondary small mb-4">Your request has been processed successfully. An order confirmation detailing delivery tracks has been logged in your simulated inbox.</p>

                    <div class="p-3 bg-black rounded border border-secondary text-start mb-4">
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Order Number:</span>
                            <strong class="text-white"><?php echo $order['order_number']; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Final Paid Amount:</span>
                            <strong class="text-warning"><?php echo formatPrice($order['final_amount']); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Estimated Delivery:</span>
                            <strong class="text-white"><?php echo date('M d, Y', strtotime($order['delivery_date'])); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Delivery Slot:</span>
                            <strong class="text-white-50"><?php echo $order['delivery_time']; ?></strong>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <a href="invoice.php?order_id=<?php echo $orderId; ?>" target="_blank" class="btn btn-gold flex-fill"><i class="bi bi-file-earmark-pdf me-2"></i>Invoice PDF</a>
                        <a href="track-order.php?order_id=<?php echo $orderId; ?>" class="btn btn-outline-gold flex-fill"><i class="bi bi-truck me-2"></i>Track Delivery</a>
                    </div>

                    <div class="mt-4 pt-3 border-top border-secondary">
                        <a href="shop.php" class="small text-muted text-white-hover"><i class="bi bi-arrow-left me-1"></i> Continue Shopping</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
