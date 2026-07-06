<?php
/**
 * Admin Panel Coupon Code Management
 */
require_once __DIR__ . '/header.php';

$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$couponId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$success = '';
$error = '';

// Handle Delete
if ($action === 'delete' && $couponId > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM `coupons` WHERE id = ?");
        $stmt->execute([$couponId]);
        setFlashMessage("success", "Coupon deleted successfully.");
        header("Location: coupons.php");
        exit;
    } catch (PDOException $e) {
        $error = "Could not delete coupon. " . $e->getMessage();
    }
}

// Handle Add/Edit Form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    $code = sanitize($_POST['code']);
    $type = sanitize($_POST['type']);
    $value = (float)$_POST['value'];
    $min_spend = (float)$_POST['min_spend'];
    $expiry_date = sanitize($_POST['expiry_date']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($code) || empty($type) || empty($expiry_date)) {
        $error = "Coupon Code, Type and Expiry Date are required.";
    } else {
        if ($action === 'add') {
            try {
                $stmt = $pdo->prepare("INSERT INTO `coupons` (code, type, value, min_spend, expiry_date, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$code, $type, $value, $min_spend, $expiry_date, $is_active]);
                setFlashMessage("success", "Coupon added successfully!");
                header("Location: coupons.php");
                exit;
            } catch (PDOException $e) {
                $error = "Failed to add coupon. " . $e->getMessage();
            }
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE `coupons` SET code = ?, type = ?, value = ?, min_spend = ?, expiry_date = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$code, $type, $value, $min_spend, $expiry_date, $is_active, $couponId]);
                setFlashMessage("success", "Coupon updated successfully!");
                header("Location: coupons.php");
                exit;
            } catch (PDOException $e) {
                $error = "Failed to update coupon. " . $e->getMessage();
            }
        }
    }
}
?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2 mb-4"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="font-heading text-white m-0">Manage Coupons</h2>
            <a href="coupons.php?action=add" class="btn btn-admin-gold"><i class="bi bi-plus-circle me-1"></i>Add Coupon</a>
        </div>

        <div class="admin-card p-4">
            <div class="table-responsive">
                <table class="table table-luxury align-middle m-0 text-center">
                    <thead>
                        <tr>
                            <th>Coupon Code</th>
                            <th>Discount Type</th>
                            <th>Discount Value</th>
                            <th>Min Spend</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $coupons = $pdo->query("SELECT * FROM `coupons` ORDER BY created_at DESC")->fetchAll();
                        foreach ($coupons as $c):
                        ?>
                            <tr>
                                <td><code class="text-warning fw-bold fs-6 font-monospace"><?php echo $c['code']; ?></code></td>
                                <td class="text-white-50 text-uppercase small"><?php echo $c['type']; ?></td>
                                <td class="text-white fw-bold small">
                                    <?php echo ($c['type'] === 'percentage') ? $c['value'] . '%' : formatPrice($c['value']); ?>
                                </td>
                                <td class="text-muted small"><?php echo formatPrice($c['min_spend']); ?></td>
                                <td class="text-muted small"><?php echo date('M d, Y', strtotime($c['expiry_date'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $c['is_active'] ? 'bg-success' : 'bg-danger'; ?> small">
                                        <?php echo $c['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="coupons.php?action=edit&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-warning py-1 px-2 me-1"><i class="bi bi-pencil-square"></i></a>
                                    <a href="coupons.php?action=delete&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger py-1 px-2" onclick="return confirm('Delete this coupon code?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <?php 
        $title = "Create Promotion Coupon";
        $cData = ['code' => '', 'type' => 'percentage', 'value' => 10.00, 'min_spend' => 0.00, 'expiry_date' => date('Y-12-31'), 'is_active' => 1];
        if ($action === 'edit' && $couponId > 0) {
            $title = "Edit Promotion Coupon";
            $stmt = $pdo->prepare("SELECT * FROM `coupons` WHERE id = ?");
            $stmt->execute([$couponId]);
            $cData = $stmt->fetch();
        }
        ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="font-heading text-white m-0"><?php echo $title; ?></h2>
            <a href="coupons.php" class="btn btn-sm btn-outline-secondary text-white"><i class="bi bi-arrow-left me-1"></i>Back to List</a>
        </div>

        <div class="admin-card p-4 p-md-5">
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label text-white-50 small">Coupon Voucher Code *</label>
                    <input type="text" name="code" class="form-control bg-transparent border-secondary text-white font-monospace" placeholder="e.g. SUMMER25" value="<?php echo htmlspecialchars($cData['code']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-white-50 small">Discount Type</label>
                    <select name="type" class="form-select bg-dark border-secondary text-white">
                        <option value="percentage" <?php echo ($cData['type'] === 'percentage') ? 'selected' : ''; ?>>Percentage (e.g. 10%)</option>
                        <option value="fixed" <?php echo ($cData['type'] === 'fixed') ? 'selected' : ''; ?>>Fixed Subtraction (e.g. ₹2,075)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-white-50 small">Discount Value *</label>
                    <input type="number" step="0.01" name="value" class="form-control bg-transparent border-secondary text-white" value="<?php echo $cData['value']; ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-white-50 small">Minimum Spend Limit *</label>
                    <input type="number" step="0.01" name="min_spend" class="form-control bg-transparent border-secondary text-white" value="<?php echo $cData['min_spend']; ?>" required>
                </div>
                <div class="mb-4">
                    <label class="form-label text-white-50 small">Expiry Date *</label>
                    <input type="date" name="expiry_date" class="form-control bg-transparent border-secondary text-white" value="<?php echo $cData['expiry_date']; ?>" required>
                </div>
                
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input bg-dark border-secondary" type="checkbox" name="is_active" id="chkActive" value="1" <?php echo ($cData['is_active'] == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label text-white-50 small" for="chkActive">Voucher is Active</label>
                </div>

                <button type="submit" class="btn btn-admin-gold py-2 px-5 font-heading">Save Coupon</button>
            </form>
        </div>
    <?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
