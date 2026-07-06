<?php
/**
 * Admin Panel Website Settings Management
 */
require_once __DIR__ . '/header.php';

$success = '';
$error = '';

// Handle Update settings submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    try {
        $pdo->beginTransaction();
        
        $upSettings = [
            'site_name' => sanitize($_POST['site_name']),
            'site_email' => sanitize($_POST['site_email']),
            'site_phone' => sanitize($_POST['site_phone']),
            'site_address' => sanitize($_POST['site_address']),
            'shipping_fee' => (float)$_POST['shipping_fee'],
            'gst_percentage' => (float)$_POST['gst_percentage'],
            'loyalty_multiplier' => (int)$_POST['loyalty_multiplier']
        ];

        $stmt = $pdo->prepare("UPDATE `settings` SET setting_value = ? WHERE setting_key = ?");
        foreach ($upSettings as $key => $val) {
            $stmt->execute([$val, $key]);
        }

        $pdo->commit();
        setFlashMessage("success", "Website settings updated successfully!");
        header("Location: settings.php");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Failed to save settings. " . $e->getMessage();
    }
}
?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2 mb-4"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="font-heading text-white m-0">Website Config Settings</h2>
    </div>

    <div class="admin-card p-4 p-md-5">
        <form method="POST" action="">
            <h5 class="font-heading text-warning mb-3"><i class="bi bi-gear me-2"></i>Global Metadata</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label text-white-50 small">Store Name</label>
                    <input type="text" name="site_name" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'AromaLuxe'); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-white-50 small">Contact Support Email</label>
                    <input type="email" name="site_email" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($settings['site_email'] ?? 'support@aromaluxe.com'); ?>" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label text-white-50 small">Boutique Phone Contact</label>
                    <input type="text" name="site_phone" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($settings['site_phone'] ?? '(800) 799-2766'); ?>" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label text-white-50 small">Store Address</label>
                    <textarea name="site_address" rows="2" class="form-control bg-transparent border-secondary text-white" required><?php echo htmlspecialchars($settings['site_address'] ?? '720 Fifth Avenue, New York, NY 10019'); ?></textarea>
                </div>
            </div>

            <h5 class="font-heading text-warning mb-3"><i class="bi bi-wallet2 me-2"></i>Charges, Taxes & Loyalty Program</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label text-white-50 small">Flat Shipping Fee (INR)</label>
                    <input type="number" step="0.01" name="shipping_fee" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($settings['shipping_fee'] ?? '15.00'); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-white-50 small">GST Tax Percentage (%)</label>
                    <input type="number" step="0.01" name="gst_percentage" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($settings['gst_percentage'] ?? '18.00'); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-white-50 small">Loyalty Multiplier (Points per ₹83)</label>
                    <input type="number" name="loyalty_multiplier" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($settings['loyalty_multiplier'] ?? '10'); ?>" required>
                </div>
            </div>

            <button type="submit" name="save_settings" class="btn btn-admin-gold py-2 px-5 font-heading">Save Configurations</button>
        </form>
    </div>

<?php require_once __DIR__ . '/footer.php'; ?>
