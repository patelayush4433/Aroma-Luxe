<?php
/**
 * Order Tracking Page
 */
require_once __DIR__ . '/config/config.php';

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$searchNumber = isset($_GET['order_number']) ? sanitize($_GET['order_number']) : '';

$order = null;
$error = '';

if ($orderId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
} elseif (!empty($searchNumber)) {
    $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE order_number = ?");
    $stmt->execute([$searchNumber]);
    $order = $stmt->fetch();
    if (!$order) {
        $error = "Order number not found. Please verify details.";
    }
}

// Security Check: If order found, check if it belongs to current customer if logged in
if ($order && isset($_SESSION['customer_id'])) {
    if ($order['customer_id'] != $_SESSION['customer_id']) {
        $order = null;
        $error = "Unauthorized access to this order tracking detail.";
    }
}

// Status definitions and tracking stages
$statuses = ['Pending', 'Packed', 'Shipped', 'Out For Delivery', 'Delivered'];
$currentStatusIdx = -1;

if ($order) {
    $currentStatusIdx = array_search($order['order_status'], $statuses);
    if ($order['order_status'] === 'Cancelled') {
        $currentStatusIdx = -2; // Special cancel state
    }
}

include_once __DIR__ . '/includes/header.php';
?>

    <!-- Page Title Header -->
    <div class="py-5 bg-black border-bottom border-secondary text-center">
        <div class="container">
            <span class="text-warning text-uppercase small tracking-widest">Real-time status</span>
            <h1 class="luxury-font text-white display-5 mt-2">Track Your Order</h1>
            <div class="mx-auto bg-warning mt-3" style="width: 50px; height: 1.5px;"></div>
        </div>
    </div>

    <!-- Main tracking content -->
    <div class="container py-5">
        <div class="row justify-content-center">
            
            <div class="col-lg-8">
                <!-- Search panel if no order selected yet -->
                <div class="glass-card p-4 mb-4">
                    <h5 class="font-heading text-white mb-3">Track by Order Number</h5>
                    <form method="GET" action="" class="row g-3">
                        <div class="col-sm-9">
                            <input type="text" name="order_number" class="form-control bg-transparent border-secondary text-white font-monospace text-center fs-5" placeholder="e.g. ALX-1A2B3C4D" value="<?php echo htmlspecialchars($searchNumber); ?>" required>
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-gold w-100 py-2 h-100">Search</button>
                        </div>
                    </form>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2 mt-3 mb-0"><?php echo $error; ?></div>
                    <?php endif; ?>
                </div>

                <?php if ($order): ?>
                    <!-- Order Tracking visual logs panel -->
                    <div class="glass-card p-4 p-md-5">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 pb-3 border-bottom border-secondary">
                            <div>
                                <span class="text-muted small">Tracking Order:</span>
                                <h4 class="font-heading text-warning m-0"><?php echo $order['order_number']; ?></h4>
                            </div>
                            <div class="text-sm-end mt-2 mt-sm-0">
                                <span class="text-muted small">Status:</span>
                                <div class="fw-bold text-white"><?php echo $order['order_status']; ?></div>
                            </div>
                        </div>

                        <!-- Progress Steps indicator -->
                        <?php if ($currentStatusIdx === -2): ?>
                            <!-- Cancelled view -->
                            <div class="alert alert-danger bg-danger-subtle text-danger border-0 p-3 text-center mb-5">
                                <i class="bi bi-x-circle-fill me-2 fs-4 align-middle"></i>
                                <span>This order was <strong>Cancelled</strong>. Please reach out to customer care for resolution support.</span>
                            </div>
                        <?php else: ?>
                            <!-- Flow Steps -->
                            <div class="row text-center position-relative mb-5 g-0">
                                <!-- Horizontal connector line -->
                                <div class="position-absolute top-50 start-0 w-100 translate-middle-y bg-secondary d-none d-md-block" style="height: 3px; z-index: 1;"></div>
                                
                                <?php foreach ($statuses as $idx => $st): 
                                    $isPassed = $idx <= $currentStatusIdx;
                                    $isCurrent = $idx === $currentStatusIdx;
                                    
                                    // Circle styling
                                    $circleClass = "bg-black border-secondary text-muted";
                                    if ($isPassed) {
                                        $circleClass = "bg-warning text-dark border-warning fw-bold shadow";
                                    }
                                    if ($isCurrent) {
                                        $circleClass = "bg-warning text-dark border-warning fw-bold shadow-lg animate-pulse";
                                    }
                                ?>
                                    <div class="col-12 col-md mb-4 mb-md-0 position-relative" style="z-index: 2;">
                                        <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center border fs-5" style="width: 50px; height: 50px; transition: var(--transition-smooth); <?php echo ($isPassed) ? 'background-color: var(--gold) !important; color:#000 !important; border-color:var(--gold) !important;' : ''; ?>">
                                            <?php if ($isPassed && !$isCurrent): ?>
                                                <i class="bi bi-check-lg"></i>
                                            <?php else: ?>
                                                <?php echo $idx + 1; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-2 font-heading small <?php echo $isPassed ? 'text-warning' : 'text-muted'; ?>">
                                            <?php echo $st; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Delivery details box -->
                        <div class="p-4 bg-black rounded border border-secondary">
                            <h6 class="font-heading text-warning mb-3">Delivery Information</h6>
                            
                            <div class="row g-3 small">
                                <div class="col-sm-6">
                                    <div class="text-muted mb-1">Scheduled Delivery Date:</div>
                                    <div class="text-white fw-bold"><?php echo date('M d, Y', strtotime($order['delivery_date'])); ?></div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="text-muted mb-1">Preferred Time Slot:</div>
                                    <div class="text-white fw-bold"><?php echo $order['delivery_time']; ?></div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="text-muted mb-1">Shipping Destination:</div>
                                    <div class="text-white-50"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></div>
                                </div>
                            </div>
                        </div>

                    </div>
                <?php elseif (!empty($searchNumber)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-search fs-1 text-muted"></i>
                        <h4 class="font-heading text-white mt-3">No matching orders</h4>
                        <p class="text-muted small">Please verify that you typed the correct order number format.</p>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
