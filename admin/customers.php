<?php
/**
 * Admin Panel Customer Account Management
 */
require_once __DIR__ . '/header.php';

$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$success = '';
$error = '';

// Handle Delete Customer Account
if ($action === 'delete' && $customerId > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM `customers` WHERE id = ?");
        $stmt->execute([$customerId]);
        setFlashMessage("success", "Customer account deleted.");
        header("Location: customers.php");
        exit;
    } catch (PDOException $e) {
        $error = "Could not delete customer account. " . $e->getMessage();
    }
}
?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2 mb-4"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="font-heading text-white m-0">Manage Customers</h2>
    </div>

    <div class="admin-card p-4">
        <div class="table-responsive">
            <table class="table table-luxury align-middle m-0 text-center">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email / Contact</th>
                        <th>Referral Code</th>
                        <th>Loyalty Points</th>
                        <th>Verified</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $stmt = $pdo->query("SELECT * FROM `customers` ORDER BY created_at DESC");
                    $custs = $stmt->fetchAll();
                    foreach ($custs as $c):
                    ?>
                        <tr>
                            <td><?php echo $c['id']; ?></td>
                            <td><strong class="text-white"><?php echo htmlspecialchars($c['name']); ?></strong></td>
                            <td class="text-start">
                                <span class="d-block text-white-50 small"><?php echo htmlspecialchars($c['email']); ?></span>
                                <span class="text-muted small" style="font-size:0.75rem;"><i class="bi bi-telephone-fill me-1"></i><?php echo $c['phone'] ? $c['phone'] : '-'; ?></span>
                            </td>
                            <td><code class="text-warning fw-bold font-monospace"><?php echo $c['referral_code']; ?></code></td>
                            <td class="text-white fw-bold small"><?php echo $c['loyalty_points']; ?> pts</td>
                            <td>
                                <span class="badge <?php echo $c['is_verified'] ? 'bg-success' : 'bg-danger'; ?> small">
                                    <?php echo $c['is_verified'] ? 'Verified' : 'Unverified'; ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?php echo date('M d, Y', strtotime($c['created_at'])); ?></td>
                            <td>
                                <a href="customers.php?action=delete&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger py-1 px-2" onclick="return confirm('Delete customer account?')" title="Delete"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once __DIR__ . '/footer.php'; ?>
