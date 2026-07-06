<?php
/**
 * Admin Panel Dashboard
 */
require_once __DIR__ . '/header.php';

// Fetch stats counters
try {
    // 1. Gross Earnings
    $stmt = $pdo->query("SELECT SUM(final_amount) FROM `orders` WHERE payment_status = 'Completed'");
    $totalSales = (float)$stmt->fetchColumn();

    // 2. Orders count
    $stmt = $pdo->query("SELECT COUNT(*) FROM `orders`");
    $totalOrders = (int)$stmt->fetchColumn();

    // 3. Customers count
    $stmt = $pdo->query("SELECT COUNT(*) FROM `customers`");
    $totalCustomers = (int)$stmt->fetchColumn();

    // 4. Products count
    $stmt = $pdo->query("SELECT COUNT(*) FROM `products`");
    $totalProducts = (int)$stmt->fetchColumn();

    // 5. Recent Orders (limit 5)
    $stmt = $pdo->query("
        SELECT o.*, c.name as customer_name 
        FROM `orders` o 
        LEFT JOIN `customers` c ON o.customer_id = c.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $recentOrders = $stmt->fetchAll();

    // 6. Low stock products (stock <= 5 in any size)
    $stmt = $pdo->query("
        SELECT id, name, sku, stock_30ml, stock_50ml, stock_100ml 
        FROM `products` 
        WHERE stock_30ml <= 5 OR stock_50ml <= 5 OR stock_100ml <= 5
    ");
    $lowStockProducts = $stmt->fetchAll();

} catch (PDOException $e) {
    $totalSales = 0;
    $totalOrders = 0;
    $totalCustomers = 0;
    $totalProducts = 0;
    $recentOrders = [];
    $lowStockProducts = [];
}
?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="font-heading text-white m-0">Dashboard Overview</h2>
        <div class="d-flex gap-2">
            <a href="export_pdf.php" target="_blank" class="btn btn-sm btn-outline-warning"><i class="bi bi-file-earmark-pdf me-1"></i>Print PDF Report</a>
            <a href="export_excel.php" class="btn btn-sm btn-admin-gold"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Export Excel CSV</a>
        </div>
    </div>

    <!-- Stats Summary Cards row -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="admin-card p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-white-50 small text-uppercase">Gross Revenue</span>
                    <h3 class="text-warning font-heading m-0 mt-1"><?php echo formatPrice($totalSales); ?></h3>
                </div>
                <i class="bi bi-currency-dollar stat-icon"></i>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="admin-card p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-white-50 small text-uppercase">Gross Orders</span>
                    <h3 class="text-white font-heading m-0 mt-1"><?php echo $totalOrders; ?></h3>
                </div>
                <i class="bi bi-cart-check stat-icon"></i>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="admin-card p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-white-50 small text-uppercase">Total Customers</span>
                    <h3 class="text-white font-heading m-0 mt-1"><?php echo $totalCustomers; ?></h3>
                </div>
                <i class="bi bi-people stat-icon"></i>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="admin-card p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-white-50 small text-uppercase">Total Products</span>
                    <h3 class="text-white font-heading m-0 mt-1"><?php echo $totalProducts; ?></h3>
                </div>
                <i class="bi bi-box-seam stat-icon"></i>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Left: Sales analytics graph mockup using SVG -->
        <div class="col-lg-8">
            <div class="analytics-chart-container h-100">
                <h5 class="font-heading text-white mb-4">Sales Analytics (Mock Monthly Trend)</h5>
                
                <!-- SVG Line Graph mockup -->
                <div class="text-center bg-black p-3 rounded" style="border: 1px solid rgba(255,255,255,0.05);">
                    <svg viewBox="0 0 500 200" class="w-100" style="max-height: 250px;">
                        <!-- Grid lines -->
                        <line x1="40" y1="20" x2="480" y2="20" stroke="#333" stroke-dasharray="4" />
                        <line x1="40" y1="70" x2="480" y2="70" stroke="#333" stroke-dasharray="4" />
                        <line x1="40" y1="120" x2="480" y2="120" stroke="#333" stroke-dasharray="4" />
                        <line x1="40" y1="170" x2="480" y2="170" stroke="#444" />
                        
                        <!-- Axis Labels -->
                        <text x="10" y="25" fill="#777" font-size="9">₹249000</text>
                        <text x="10" y="75" fill="#777" font-size="9">₹124500</text>
                        <text x="10" y="125" fill="#777" font-size="9">₹41500</text>
                        <text x="10" y="175" fill="#777" font-size="9">₹0</text>
                        
                        <!-- Month labels -->
                        <text x="50" y="190" fill="#777" font-size="9">Jan</text>
                        <text x="120" y="190" fill="#777" font-size="9">Feb</text>
                        <text x="190" y="190" fill="#777" font-size="9">Mar</text>
                        <text x="260" y="190" fill="#777" font-size="9">Apr</text>
                        <text x="330" y="190" fill="#777" font-size="9">May</text>
                        <text x="400" y="190" fill="#777" font-size="9">Jun</text>

                        <!-- Trend Line -->
                        <path d="M 50,170 Q 120,130 190,140 T 260,90 T 330,60 T 400,30" fill="none" stroke="#d4af37" stroke-width="3" />
                        
                        <!-- Data Points -->
                        <circle cx="50" cy="170" r="4" fill="#fff" stroke="#d4af37" stroke-width="2" />
                        <circle cx="190" cy="140" r="4" fill="#fff" stroke="#d4af37" stroke-width="2" />
                        <circle cx="260" cy="90" r="4" fill="#fff" stroke="#d4af37" stroke-width="2" />
                        <circle cx="400" cy="30" r="4" fill="#fff" stroke="#d4af37" stroke-width="2" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Right: Low stock levels tracking warnings -->
        <div class="col-lg-4">
            <div class="admin-card p-4 h-100">
                <h5 class="font-heading text-white mb-3">Inventory Stock Alerts</h5>
                <div class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;">
                    <?php if (count($lowStockProducts) > 0): ?>
                        <?php foreach ($lowStockProducts as $lowP): ?>
                            <div class="list-group-item bg-transparent text-light border-secondary px-0 py-2">
                                <div class="fw-bold small"><?php echo $lowP['name']; ?></div>
                                <div class="text-muted" style="font-size:0.7rem;">SKU: <?php echo $lowP['sku']; ?></div>
                                <div class="d-flex gap-2 mt-1" style="font-size:0.65rem;">
                                    <span class="badge bg-outline-secondary border <?php echo ($lowP['stock_30ml'] <= 5) ? 'border-danger text-danger' : 'border-secondary'; ?>">30ml: <?php echo $lowP['stock_30ml']; ?></span>
                                    <span class="badge bg-outline-secondary border <?php echo ($lowP['stock_50ml'] <= 5) ? 'border-danger text-danger' : 'border-secondary'; ?>">50ml: <?php echo $lowP['stock_50ml']; ?></span>
                                    <span class="badge bg-outline-secondary border <?php echo ($lowP['stock_100ml'] <= 5) ? 'border-danger text-danger' : 'border-secondary'; ?>">100ml: <?php echo $lowP['stock_100ml']; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-success small m-0"><i class="bi bi-check-circle me-1"></i> All products are fully stocked.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table list -->
    <div class="admin-card p-4 mb-4">
        <h5 class="font-heading text-white mb-4">Recent Booked Orders</h5>
        <div class="table-responsive">
            <table class="table table-luxury align-middle m-0">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Payment Status</th>
                        <th>Order Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recentOrders) > 0): ?>
                        <?php foreach ($recentOrders as $rOrd): ?>
                            <tr>
                                <td class="font-monospace fw-bold text-white small"><?php echo $rOrd['order_number']; ?></td>
                                <td class="small"><?php echo date('M d, Y', strtotime($rOrd['created_at'])); ?></td>
                                <td><?php echo $rOrd['customer_name'] ? $rOrd['customer_name'] : 'Guest'; ?></td>
                                <td class="text-warning fw-bold small"><?php echo formatPrice($rOrd['final_amount']); ?></td>
                                <td><span class="badge bg-success py-1 px-2"><?php echo $rOrd['payment_status']; ?></span></td>
                                <td>
                                    <span class="badge badge-status-<?php echo strtolower(str_replace(' ', '-', $rOrd['order_status'])); ?> py-1 px-2">
                                        <?php echo $rOrd['order_status']; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="orders.php?edit_order=<?php echo $rOrd['id']; ?>" class="btn btn-sm btn-admin-gold py-1 px-2"><i class="bi bi-pencil-square"></i> Dispatch</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">No orders placed yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once __DIR__ . '/footer.php'; ?>
