<?php
/**
 * Printable Order Invoice Page
 */
require_once __DIR__ . '/config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($orderId <= 0) {
    die("Invalid order ID.");
}

// Security Check: Customer owns the order OR admin is logged in
$isAdmin = isset($_SESSION['admin_id']);
$customerId = $_SESSION['customer_id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM `orders` WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found.");
}

if (!$isAdmin && $order['customer_id'] != $customerId) {
    die("Unauthorized access to this invoice.");
}

// Fetch Order Items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.sku 
    FROM `order_items` oi
    LEFT JOIN `products` p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

// Fetch Customer Name for Invoice
$stmt = $pdo->prepare("SELECT name, email FROM `customers` WHERE id = ?");
$stmt->execute([$order['customer_id']]);
$client = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo $order['order_number']; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Font Cinzel & Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #ffffff;
            color: #222222;
            padding: 40px 20px;
        }
        .luxury-heading {
            font-family: 'Cinzel', serif;
            letter-spacing: 2px;
            color: #d4af37;
            font-weight: 700;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            border: 1px solid #eaeaea;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-radius: 8px;
        }
        .table-invoice th {
            font-family: 'Cinzel', serif;
            background-color: #000000;
            color: #ffffff;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }
        .table-invoice td {
            font-size: 0.9rem;
            border-bottom: 1px solid #eaeaea;
            padding: 12px 8px;
        }
        .invoice-divider {
            height: 1px;
            background-color: #d4af37;
            margin: 25px 0;
            opacity: 0.3;
        }
        @media print {
            .btn-print-area {
                display: none !important;
            }
            body {
                padding: 0;
            }
            .invoice-box {
                border: none;
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Printable Area Button Controls -->
    <div class="container btn-print-area text-center mb-4">
        <button onclick="window.print()" class="btn btn-dark px-4 py-2 me-2"><i class="bi bi-printer me-2"></i>Print Invoice</button>
        <?php if($isAdmin): ?>
            <a href="admin/orders.php" class="btn btn-outline-secondary px-4 py-2">Back to Orders</a>
        <?php else: ?>
            <a href="profile.php" class="btn btn-outline-secondary px-4 py-2">Back to Profile</a>
        <?php endif; ?>
    </div>

    <!-- Invoice Sheet -->
    <div class="invoice-box">
        <!-- Logo Row -->
        <div class="row align-items-center mb-4">
            <div class="col-sm-6 text-center text-sm-start">
                <h2 class="luxury-heading m-0">AROMALUXE</h2>
                <div class="small text-muted mt-1">THE ESSENCE OF BOTANICAL PERFECTION</div>
            </div>
            <div class="col-sm-6 text-center text-sm-end mt-3 mt-sm-0">
                <h4 class="m-0 text-uppercase tracking-wider">Invoice</h4>
                <div class="small fw-bold">No: <?php echo $order['order_number']; ?></div>
                <div class="small text-muted">Date: <?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
            </div>
        </div>

        <div class="invoice-divider"></div>

        <!-- Addresses Row -->
        <div class="row mb-4">
            <div class="col-6 col-sm-6">
                <h6 class="text-uppercase fw-bold" style="letter-spacing:1px; color:#d4af37;">From:</h6>
                <div class="small">
                    <strong>AromaLuxe Boutique</strong><br>
                    720 Fifth Avenue, Floor 4<br>
                    New York, NY 10019<br>
                    Email: support@aromaluxe.com<br>
                    Phone: (800) 799-2766
                </div>
            </div>
            <div class="col-6 col-sm-6 text-end">
                <h6 class="text-uppercase fw-bold" style="letter-spacing:1px; color:#d4af37;">To:</h6>
                <div class="small">
                    <strong><?php echo htmlspecialchars($order['shipping_name']); ?></strong><br>
                    <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?><br>
                    Phone: <?php echo htmlspecialchars($order['shipping_phone']); ?><br>
                    Email: <?php echo htmlspecialchars($order['shipping_email']); ?>
                </div>
            </div>
        </div>

        <!-- Schedule / Method Row -->
        <div class="row mb-4 bg-light p-3 rounded mx-0">
            <div class="col-sm-6">
                <div class="small"><strong>Payment Method:</strong> <?php echo $order['payment_method']; ?></div>
                <div class="small"><strong>Payment Status:</strong> <?php echo $order['payment_status']; ?></div>
            </div>
            <div class="col-sm-6 text-sm-end">
                <div class="small"><strong>Scheduled Date:</strong> <?php echo date('M d, Y', strtotime($order['delivery_date'])); ?></div>
                <div class="small"><strong>Delivery Window:</strong> <?php echo $order['delivery_time']; ?></div>
            </div>
        </div>

        <!-- Line Items Table -->
        <table class="table table-invoice align-middle mb-4">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th class="text-center" style="width: 80px;">Size</th>
                    <th class="text-center" style="width: 80px;">Qty</th>
                    <th class="text-end" style="width: 120px;">Unit Price</th>
                    <th class="text-end" style="width: 120px;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <strong><?php echo $item['product_name'] ? $item['product_name'] : 'Luxury Fragrance'; ?></strong>
                            <div class="small text-muted font-monospace">SKU: <?php echo $item['sku'] ? $item['sku'] : 'ALX-MOCK-00'; ?></div>
                        </td>
                        <td class="text-center"><?php echo $item['size']; ?></td>
                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                        <td class="text-end"><?php echo formatPrice($item['price']); ?></td>
                        <td class="text-end fw-bold"><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals Row -->
        <div class="row justify-content-end">
            <div class="col-md-5 text-end">
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>Subtotal:</span>
                    <span><?php echo formatPrice($order['total_amount']); ?></span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                    <div class="d-flex justify-content-between mb-2 small text-success">
                        <span>Discount (<?php echo $order['coupon_code'] ? $order['coupon_code'] : ''; ?>):</span>
                        <span>-<?php echo formatPrice($order['discount_amount']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($order['gift_wrap'] == 1): ?>
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span>Gift Wrap:</span>
                        <span><?php echo formatPrice(5.00); ?></span>
                    </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>Shipping Charges:</span>
                    <span><?php echo $order['shipping_fee'] > 0 ? formatPrice($order['shipping_fee']) : 'FREE'; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>GST Tax (18%):</span>
                    <span><?php echo formatPrice($order['gst_amount']); ?></span>
                </div>
                
                <div class="invoice-divider"></div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="text-uppercase">Total Paid:</strong>
                    <h4 class="m-0 luxury-heading"><?php echo formatPrice($order['final_amount']); ?></h4>
                </div>
            </div>
        </div>

        <div class="invoice-divider"></div>

        <div class="text-center small text-muted mt-4">
            Thank you for your patronage. If you have questions regarding this invoice, please support at <strong>support@aromaluxe.com</strong>.
        </div>
    </div>

</body>
</html>
