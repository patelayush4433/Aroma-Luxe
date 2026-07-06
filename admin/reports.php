<?php
/**
 * Admin Panel - Reports & Analytics
 * Comprehensive sales, products and customer analytics
 */
require_once __DIR__ . '/header.php';

// Date range filter
$fromDate = isset($_GET['from']) ? sanitize($_GET['from']) : date('Y-m-01');
$toDate   = isset($_GET['to'])   ? sanitize($_GET['to'])   : date('Y-m-d');

try {
    // Total Revenue in range
    $stmt = $pdo->prepare("SELECT SUM(final_amount) FROM `orders` WHERE payment_status = 'Completed' AND DATE(created_at) BETWEEN ? AND ?");
    $stmt->execute([$fromDate, $toDate]);
    $rangeRevenue = (float)$stmt->fetchColumn();

    // Total Orders in range
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `orders` WHERE DATE(created_at) BETWEEN ? AND ?");
    $stmt->execute([$fromDate, $toDate]);
    $rangeOrders = (int)$stmt->fetchColumn();

    // New Customers in range
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `customers` WHERE DATE(created_at) BETWEEN ? AND ?");
    $stmt->execute([$fromDate, $toDate]);
    $rangeCustomers = (int)$stmt->fetchColumn();

    // Average Order Value
    $avgOrder = ($rangeOrders > 0) ? $rangeRevenue / $rangeOrders : 0;

    // Top 5 Best-Selling Products (all time by order items)
    $stmt = $pdo->query("
        SELECT p.name, p.sku, SUM(oi.quantity) as total_qty, SUM(oi.line_total) as total_revenue
        FROM `order_items` oi
        LEFT JOIN `products` p ON oi.product_id = p.id
        GROUP BY oi.product_id
        ORDER BY total_qty DESC
        LIMIT 8
    ");
    $topProducts = $stmt->fetchAll();

    // Monthly revenue data (last 6 months)
    $monthlyData = [];
    for ($i = 5; $i >= 0; $i--) {
        $month  = date('Y-m', strtotime("-$i months"));
        $label  = date('M Y', strtotime("-$i months"));
        $mStmt  = $pdo->prepare("SELECT SUM(final_amount) FROM `orders` WHERE payment_status = 'Completed' AND DATE_FORMAT(created_at, '%Y-%m') = ?");
        $mStmt->execute([$month]);
        $monthlyData[$label] = (float)$mStmt->fetchColumn();
    }

    // Order Status Distribution
    $stmt = $pdo->query("SELECT order_status, COUNT(*) as cnt FROM `orders` GROUP BY order_status ORDER BY cnt DESC");
    $statusDist = $stmt->fetchAll();

    // Payment Method breakdown
    $stmt = $pdo->query("SELECT payment_method, COUNT(*) as cnt, SUM(final_amount) as revenue FROM `orders` GROUP BY payment_method ORDER BY revenue DESC");
    $paymentDist = $stmt->fetchAll();

    // Category performance
    $stmt = $pdo->query("
        SELECT c.name as category, COUNT(DISTINCT p.id) as product_count,
               COALESCE(SUM(oi.quantity), 0) as units_sold
        FROM `categories` c
        LEFT JOIN `products` p ON p.category_id = c.id
        LEFT JOIN `order_items` oi ON oi.product_id = p.id
        GROUP BY c.id
        ORDER BY units_sold DESC
    ");
    $categoryPerf = $stmt->fetchAll();

    // Recent reviews summary
    $stmt = $pdo->query("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM `reviews` WHERE status = 'Approved'");
    $reviewSummary = $stmt->fetch();

    // Newsletter subscribers
    $stmt = $pdo->query("SELECT COUNT(*) FROM `newsletter`");
    $subscriberCount = (int)$stmt->fetchColumn();

    // Coupon usage stats
    $stmt = $pdo->query("SELECT code, usage_count, discount_value, discount_type FROM `coupons` ORDER BY usage_count DESC LIMIT 5");
    $couponStats = $stmt->fetchAll();

} catch (PDOException $e) {
    $rangeRevenue = $rangeOrders = $rangeCustomers = $avgOrder = 0;
    $topProducts = $statusDist = $paymentDist = $categoryPerf = $couponStats = [];
    $monthlyData = [];
    $reviewSummary = ['avg_rating' => 0, 'total_reviews' => 0];
    $subscriberCount = 0;
}

$maxMonthly = $monthlyData ? max(array_values($monthlyData)) : 1;
$maxMonthly = $maxMonthly > 0 ? $maxMonthly : 1;
?>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="font-heading text-white m-0"><i class="bi bi-bar-chart-fill text-warning me-2"></i>Analytics &amp; Reports</h2>
        <div class="d-flex gap-2 flex-wrap">
            <a href="export_pdf.php" target="_blank" class="btn btn-sm btn-outline-warning"><i class="bi bi-file-earmark-pdf me-1"></i>PDF Report</a>
            <a href="export_excel.php" class="btn btn-sm btn-admin-gold"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV</a>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="admin-card p-3 mb-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label text-white-50 small mb-1">From Date</label>
                <input type="date" name="from" value="<?php echo $fromDate; ?>" class="form-control form-control-sm bg-transparent border-secondary text-white">
            </div>
            <div class="col-md-4">
                <label class="form-label text-white-50 small mb-1">To Date</label>
                <input type="date" name="to" value="<?php echo $toDate; ?>" class="form-control form-control-sm bg-transparent border-secondary text-white">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-sm btn-admin-gold w-100"><i class="bi bi-funnel me-1"></i>Filter Reports</button>
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="admin-card p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-white-50 small text-uppercase">Revenue (Range)</span>
                    <h3 class="text-warning font-heading m-0 mt-1"><?php echo formatPrice($rangeRevenue); ?></h3>
                    <small class="text-muted"><?php echo date('M d', strtotime($fromDate)); ?> – <?php echo date('M d', strtotime($toDate)); ?></small>
                </div>
                <i class="bi bi-currency-dollar stat-icon text-warning" style="font-size:2rem; opacity:0.3;"></i>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="admin-card p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-white-50 small text-uppercase">Orders (Range)</span>
                    <h3 class="text-white font-heading m-0 mt-1"><?php echo $rangeOrders; ?></h3>
                    <small class="text-muted">Avg: <?php echo formatPrice($avgOrder); ?>/order</small>
                </div>
                <i class="bi bi-cart-check stat-icon text-white" style="font-size:2rem; opacity:0.3;"></i>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="admin-card p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-white-50 small text-uppercase">New Customers</span>
                    <h3 class="text-white font-heading m-0 mt-1"><?php echo $rangeCustomers; ?></h3>
                    <small class="text-muted"><?php echo $subscriberCount; ?> Newsletter Subs</small>
                </div>
                <i class="bi bi-people stat-icon text-white" style="font-size:2rem; opacity:0.3;"></i>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="admin-card p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-white-50 small text-uppercase">Avg. Rating</span>
                    <h3 class="text-warning font-heading m-0 mt-1"><?php echo number_format((float)$reviewSummary['avg_rating'], 1); ?> ★</h3>
                    <small class="text-muted"><?php echo $reviewSummary['total_reviews']; ?> Reviews</small>
                </div>
                <i class="bi bi-star-fill stat-icon text-warning" style="font-size:2rem; opacity:0.3;"></i>
            </div>
        </div>
    </div>

    <!-- Monthly Revenue Chart -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="admin-card p-4 h-100">
                <h5 class="font-heading text-white mb-4">Monthly Revenue Trend (Last 6 Months)</h5>
                <div class="bg-black p-3 rounded" style="border:1px solid rgba(255,255,255,0.05);">
                    <svg viewBox="0 0 560 220" class="w-100" style="max-height:250px;">
                        <!-- Grid lines -->
                        <line x1="60" y1="20" x2="540" y2="20" stroke="#2a2a2a" stroke-dasharray="4"/>
                        <line x1="60" y1="60" x2="540" y2="60" stroke="#2a2a2a" stroke-dasharray="4"/>
                        <line x1="60" y1="100" x2="540" y2="100" stroke="#2a2a2a" stroke-dasharray="4"/>
                        <line x1="60" y1="140" x2="540" y2="140" stroke="#2a2a2a" stroke-dasharray="4"/>
                        <line x1="60" y1="180" x2="540" y2="180" stroke="#333"/>
                        <?php
                        $labels = array_keys($monthlyData);
                        $values = array_values($monthlyData);
                        $n = count($values);
                        $points = [];
                        for ($i = 0; $i < $n; $i++) {
                            $x = 60 + ($i / ($n - 1)) * 480;
                            $y = 180 - (($values[$i] / $maxMonthly) * 160);
                            $points[] = "$x,$y";
                            // Bars
                            $barH = max(2, ($values[$i] / $maxMonthly) * 160);
                            $barX = $x - 20;
                            echo "<rect x='$barX' y='" . (180 - $barH) . "' width='40' height='$barH' fill='rgba(212,175,55,0.2)' rx='3'/>";
                            echo "<text x='$x' y='200' fill='#666' font-size='9' text-anchor='middle'>" . date('M', strtotime($labels[$i])) . "</text>";
                            echo "<text x='$x' y='" . (180 - $barH - 6) . "' fill='#d4af37' font-size='8' text-anchor='middle'>" . ($settings['currency_symbol'] ?? '₹') . number_format($values[$i]) . "</text>";
                        }
                        $polyline = implode(' ', $points);
                        echo "<polyline points='$polyline' fill='none' stroke='#d4af37' stroke-width='2.5'/>";
                        foreach ($points as $pt) {
                            list($px, $py) = explode(',', $pt);
                            echo "<circle cx='$px' cy='$py' r='4' fill='#fff' stroke='#d4af37' stroke-width='2'/>";
                        }
                        ?>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Order Status Distribution -->
        <div class="col-lg-4">
            <div class="admin-card p-4 h-100">
                <h5 class="font-heading text-white mb-3">Order Status Distribution</h5>
                <?php
                $totalOrdAll = array_sum(array_column($statusDist, 'cnt'));
                $statusColors = [
                    'Pending'          => '#f59e0b',
                    'Packed'           => '#3b82f6',
                    'Shipped'          => '#8b5cf6',
                    'Out For Delivery' => '#06b6d4',
                    'Delivered'        => '#10b981',
                    'Cancelled'        => '#ef4444',
                ];
                foreach ($statusDist as $sd):
                    $pct = $totalOrdAll > 0 ? round(($sd['cnt'] / $totalOrdAll) * 100) : 0;
                    $color = $statusColors[$sd['order_status']] ?? '#888';
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-white-50"><?php echo $sd['order_status']; ?></span>
                        <span class="text-white fw-bold"><?php echo $sd['cnt']; ?> (<?php echo $pct; ?>%)</span>
                    </div>
                    <div class="progress" style="height: 6px; background: #222;">
                        <div class="progress-bar" style="width:<?php echo $pct; ?>%; background:<?php echo $color; ?>;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($statusDist)): ?>
                    <p class="text-muted small">No orders yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top Products + Payment Methods -->
    <div class="row g-4 mb-4">
        <!-- Top Products -->
        <div class="col-lg-7">
            <div class="admin-card p-4 h-100">
                <h5 class="font-heading text-white mb-3"><i class="bi bi-trophy-fill text-warning me-2"></i>Top Selling Products</h5>
                <div class="table-responsive">
                    <table class="table table-luxury align-middle m-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th class="text-end">Units Sold</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($topProducts) > 0): ?>
                                <?php foreach ($topProducts as $i => $tp): ?>
                                    <tr>
                                        <td>
                                            <?php if ($i === 0): ?>
                                                <span class="text-warning">🥇</span>
                                            <?php elseif ($i === 1): ?>
                                                <span>🥈</span>
                                            <?php elseif ($i === 2): ?>
                                                <span>🥉</span>
                                            <?php else: ?>
                                                <span class="text-muted"><?php echo $i + 1; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold text-white small"><?php echo htmlspecialchars($tp['name']); ?></td>
                                        <td><code class="text-warning" style="font-size:0.75rem;"><?php echo htmlspecialchars($tp['sku']); ?></code></td>
                                        <td class="text-end text-white small"><?php echo number_format($tp['total_qty']); ?></td>
                                        <td class="text-end text-warning fw-bold small"><?php echo formatPrice($tp['total_revenue']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">No order data yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment Methods + Category Performance -->
        <div class="col-lg-5">
            <div class="admin-card p-4 mb-4">
                <h5 class="font-heading text-white mb-3"><i class="bi bi-credit-card-fill text-warning me-2"></i>Payment Methods</h5>
                <?php foreach ($paymentDist as $pm): ?>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary">
                    <span class="text-white-50 small"><?php echo htmlspecialchars($pm['payment_method']); ?></span>
                    <div class="text-end">
                        <div class="text-warning fw-bold small"><?php echo formatPrice($pm['revenue']); ?></div>
                        <div class="text-muted" style="font-size:0.7rem;"><?php echo $pm['cnt']; ?> orders</div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($paymentDist)): ?><p class="text-muted small m-0">No data.</p><?php endif; ?>
            </div>

            <div class="admin-card p-4">
                <h5 class="font-heading text-white mb-3"><i class="bi bi-grid-fill text-warning me-2"></i>Top Coupon Usage</h5>
                <?php foreach ($couponStats as $coup): ?>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary">
                    <div>
                        <code class="text-warning fw-bold"><?php echo htmlspecialchars($coup['code']); ?></code>
                        <div class="text-muted" style="font-size:0.7rem;">
                            <?php echo $coup['discount_value']; ?>
                            <?php echo $coup['discount_type'] === 'percentage' ? '%' : ($settings['currency_symbol'] ?? '₹'); ?> off
                        </div>
                    </div>
                    <span class="badge bg-secondary small"><?php echo $coup['usage_count']; ?> uses</span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($couponStats)): ?><p class="text-muted small m-0">No coupons used yet.</p><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Category Performance Table -->
    <div class="admin-card p-4 mb-4">
        <h5 class="font-heading text-white mb-3"><i class="bi bi-pie-chart-fill text-warning me-2"></i>Category Performance</h5>
        <div class="table-responsive">
            <table class="table table-luxury align-middle m-0 text-center">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Products Listed</th>
                        <th>Units Sold</th>
                        <th>Performance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $maxUnits = $categoryPerf ? max(array_column($categoryPerf, 'units_sold')) : 1;
                    $maxUnits = $maxUnits > 0 ? $maxUnits : 1;
                    foreach ($categoryPerf as $cat):
                        $pct = round(($cat['units_sold'] / $maxUnits) * 100);
                    ?>
                    <tr>
                        <td class="text-white fw-bold text-start"><?php echo htmlspecialchars($cat['category']); ?></td>
                        <td><?php echo $cat['product_count']; ?></td>
                        <td class="text-warning"><?php echo $cat['units_sold']; ?></td>
                        <td style="min-width:150px;">
                            <div class="progress" style="height:6px; background:#222;">
                                <div class="progress-bar bg-warning" style="width:<?php echo $pct; ?>%;"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categoryPerf)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">No category data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once __DIR__ . '/footer.php'; ?>
