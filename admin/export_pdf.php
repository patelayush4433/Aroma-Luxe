<?php
/**
 * Export Sales Report (Print / PDF View)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Force Admin Check
checkAdminAuth();

// Fetch orders list
$stmt = $pdo->query("
    SELECT o.*, c.name as customer_name 
    FROM `orders` o 
    LEFT JOIN `customers` c ON o.customer_id = c.id 
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();

$totalRevenue = 0.00;
foreach ($orders as $o) {
    if ($o['payment_status'] === 'Completed') {
        $totalRevenue += (float)$o['final_amount'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AromaLuxe - Sales Report</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            padding: 40px;
            background: #ffffff;
            color: #222222;
        }
        h2, h5 {
            font-family: 'Cinzel', serif;
            letter-spacing: 1px;
        }
        .header-section {
            border-bottom: 2px solid #d4af37;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .table th {
            background-color: #111 !important;
            color: #fff !important;
        }
        @media print {
            .btn-print {
                display: none !important;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Header Actions -->
    <div class="text-center btn-print mb-4">
        <button class="btn btn-dark px-4 py-2 me-2" onclick="window.print()"><i class="bi bi-printer me-2"></i>Print Report</button>
        <a href="dashboard.php" class="btn btn-outline-secondary px-4 py-2">Return to Dashboard</a>
    </div>

    <!-- Report Sheet -->
    <div class="header-section d-flex justify-content-between align-items-center">
        <div>
            <h2 class="m-0 text-uppercase tracking-wider" style="color:#d4af37;">AromaLuxe Boutique</h2>
            <div class="small text-muted">720 Fifth Avenue, New York, NY 10019</div>
        </div>
        <div class="text-end">
            <h4 class="m-0">Sales Analytics Report</h4>
            <div class="small text-muted">Generated: <?php echo date('M d, Y H:i:s'); ?></div>
        </div>
    </div>

    <!-- Analytics Stats Overview -->
    <div class="row g-3 mb-4 text-center">
        <div class="col-6 col-sm-3">
            <div class="p-3 bg-light rounded border">
                <span class="text-muted small text-uppercase">Total Revenue</span>
                <h4 class="m-0 fw-bold mt-1 text-success"><?php echo formatPrice($totalRevenue); ?></h4>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="p-3 bg-light rounded border">
                <span class="text-muted small text-uppercase">Total Orders</span>
                <h4 class="m-0 fw-bold mt-1 text-dark"><?php echo count($orders); ?></h4>
            </div>
        </div>
    </div>

    <!-- Orders breakdown -->
    <h5 class="mb-3 text-uppercase tracking-wider">Orders Summary</h5>
    <table class="table table-bordered table-striped align-middle">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date / Time</th>
                <th>Customer Profile</th>
                <th>Payment Mode</th>
                <th>Payment Status</th>
                <th class="text-end">Final Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $ord): ?>
                <tr>
                    <td class="font-monospace fw-bold"><?php echo $ord['order_number']; ?></td>
                    <td><?php echo date('M d, Y H:i', strtotime($ord['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($ord['shipping_name'] ?? ($ord['customer_name'] ?? 'Guest')); ?></td>
                    <td><?php echo $ord['payment_method']; ?></td>
                    <td><span class="badge bg-success"><?php echo $ord['payment_status']; ?></span></td>
                    <td class="text-end fw-bold"><?php echo formatPrice($ord['final_amount']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>
