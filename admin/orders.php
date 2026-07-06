<?php
/**
 * Admin Panel Order Dispatch management
 */
require_once __DIR__ . '/../config/config.php';

// Handle AJAX status updates before layouts load
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    header('Content-Type: application/json');
    $orderId = (int)$_POST['order_id'];
    $newStatus = sanitize($_POST['order_status']);

    $validStatuses = ['Pending', 'Packed', 'Shipped', 'Out For Delivery', 'Delivered', 'Cancelled'];
    if (!in_array($newStatus, $validStatuses)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status option.']);
        exit;
    }

    try {
        // Fetch order details
        $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if ($order) {
            $stmt = $pdo->prepare("UPDATE `orders` SET order_status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $orderId]);

            // Dispatch simulated updates
            // Email Notification
            sendSimulatedNotification(
                'Email',
                $order['shipping_name'] . ' <' . $order['shipping_email'] . '>',
                'Order Status Update - ' . $order['order_number'],
                "Hello " . $order['shipping_name'] . ",\n\nGood news! Your order status has been updated to: " . $newStatus . ".\n\nTrack your delivery updates directly under the Client Portal.\n\nThank you for choosing AromaLuxe."
            );

            // SMS Notification
            sendSimulatedNotification(
                'SMS',
                $order['shipping_phone'],
                'AromaLuxe Status Alert',
                "Your AromaLuxe order " . $order['order_number'] . " is now " . $newStatus . ". Track live status details in the client portal."
            );

            echo json_encode(['status' => 'success', 'message' => 'Status updated successfully.']);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error. ' . $e->getMessage()]);
        exit;
    }
}

// Proceed to normal page loading
require_once __DIR__ . '/header.php';

$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$success = '';
$error = '';

if ($action === 'list'):
?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="font-heading text-white m-0">Manage Customer Orders</h2>
        <a href="export_excel.php" class="btn btn-admin-gold"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV Sheet</a>
    </div>

    <div class="admin-card p-4">
        <div class="table-responsive">
            <table class="table table-luxury align-middle m-0 text-center">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Date</th>
                        <th>Customer / Contact</th>
                        <th>Total Amount</th>
                        <th>Payment</th>
                        <th>Update Dispatch Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $stmt = $pdo->query("
                        SELECT o.*, c.name as customer_name 
                        FROM `orders` o
                        LEFT JOIN `customers` c ON o.customer_id = c.id
                        ORDER BY o.created_at DESC
                    ");
                    $orders = $stmt->fetchAll();
                    foreach ($orders as $o):
                    ?>
                        <tr>
                            <td class="font-monospace fw-bold text-white small"><?php echo $o['order_number']; ?></td>
                            <td class="small"><?php echo date('M d, Y', strtotime($o['created_at'])); ?></td>
                            <td class="text-start">
                                <strong class="text-white-50 small"><?php echo htmlspecialchars($o['shipping_name']); ?></strong>
                                <span class="d-block text-muted small" style="font-size:0.75rem;"><i class="bi bi-telephone-fill me-1"></i><?php echo $o['shipping_phone']; ?></span>
                            </td>
                            <td class="text-warning fw-bold small"><?php echo formatPrice($o['final_amount']); ?></td>
                            <td>
                                <span class="badge bg-success py-1 px-2 small" style="font-size:0.7rem;"><?php echo $o['payment_status']; ?></span>
                            </td>
                            <td>
                                <select class="form-select form-select-sm admin-order-status-select badge-status-<?php echo strtolower(str_replace(' ', '-', $o['order_status'])); ?>" data-order-id="<?php echo $o['id']; ?>" style="font-size:0.75rem; width:170px; margin:auto;">
                                    <option value="Pending" <?php echo ($o['order_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Packed" <?php echo ($o['order_status'] === 'Packed') ? 'selected' : ''; ?>>Packed</option>
                                    <option value="Shipped" <?php echo ($o['order_status'] === 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="Out For Delivery" <?php echo ($o['order_status'] === 'Out For Delivery') ? 'selected' : ''; ?>>Out For Delivery</option>
                                    <option value="Delivered" <?php echo ($o['order_status'] === 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="Cancelled" <?php echo ($o['order_status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </td>
                            <td>
                                <a href="../invoice.php?order_id=<?php echo $o['id']; ?>" target="_blank" class="btn btn-sm btn-outline-warning py-1 px-2" title="View Print Invoice"><i class="bi bi-file-earmark-pdf"></i> Invoice</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
