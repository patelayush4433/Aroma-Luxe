<?php
/**
 * Admin Panel Review Moderation management
 */
require_once __DIR__ . '/header.php';

$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$reviewId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$success = '';
$error = '';

// Helper to recalculate average product ratings
function updateAverageRating($pdo, $productId) {
    $avgRatingStmt = $pdo->prepare("SELECT AVG(rating) FROM `reviews` WHERE product_id = ? AND status = 'Approved'");
    $avgRatingStmt->execute([$productId]);
    $newAvgRating = (float)$avgRatingStmt->fetchColumn();

    $upProduct = $pdo->prepare("UPDATE `products` SET rating = ? WHERE id = ?");
    $upProduct->execute([$newAvgRating, $productId]);
}

if ($reviewId > 0) {
    // Fetch review to know product_id
    $stmt = $pdo->prepare("SELECT product_id FROM `reviews` WHERE id = ?");
    $stmt->execute([$reviewId]);
    $pId = $stmt->fetchColumn();

    if ($action === 'approve') {
        try {
            $stmt = $pdo->prepare("UPDATE `reviews` SET status = 'Approved' WHERE id = ?");
            $stmt->execute([$reviewId]);
            updateAverageRating($pdo, $pId);
            setFlashMessage("success", "Review approved successfully.");
            header("Location: reviews.php");
            exit;
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    } elseif ($action === 'reject') {
        try {
            $stmt = $pdo->prepare("UPDATE `reviews` SET status = 'Rejected' WHERE id = ?");
            $stmt->execute([$reviewId]);
            updateAverageRating($pdo, $pId);
            setFlashMessage("success", "Review rejected.");
            header("Location: reviews.php");
            exit;
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    } elseif ($action === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM `reviews` WHERE id = ?");
            $stmt->execute([$reviewId]);
            updateAverageRating($pdo, $pId);
            setFlashMessage("success", "Review deleted.");
            header("Location: reviews.php");
            exit;
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    }
}
?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2 mb-4"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="font-heading text-white m-0">Moderate Customer Reviews</h2>
    </div>

    <div class="admin-card p-4">
        <div class="table-responsive">
            <table class="table table-luxury align-middle m-0 text-center">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product / Rating</th>
                        <th>Customer</th>
                        <th style="width: 40%;">Comment Details</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $stmt = $pdo->query("
                        SELECT r.*, p.name as product_name, c.name as customer_name 
                        FROM `reviews` r
                        LEFT JOIN `products` p ON r.product_id = p.id
                        LEFT JOIN `customers` c ON r.customer_id = c.id
                        ORDER BY r.created_at DESC
                    ");
                    $reviews = $stmt->fetchAll();
                    foreach ($reviews as $rev):
                    ?>
                        <tr>
                            <td><?php echo $rev['id']; ?></td>
                            <td>
                                <strong class="text-white small d-block"><?php echo $rev['product_name']; ?></strong>
                                <span class="text-warning small">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi <?php echo ($i <= $rev['rating']) ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                                    <?php endfor; ?>
                                </span>
                            </td>
                            <td><?php echo $rev['customer_name'] ? $rev['customer_name'] : 'Guest'; ?></td>
                            <td class="text-muted text-start small"><?php echo htmlspecialchars($rev['comment']); ?></td>
                            <td>
                                <?php 
                                $statusBadgeMap = [
                                    'Approved' => 'bg-success',
                                    'Rejected' => 'bg-danger',
                                    'Pending' => 'bg-warning text-dark'
                                ];
                                ?>
                                <span class="badge <?php echo $statusBadgeMap[$rev['status']] ?? 'bg-secondary'; ?> small">
                                    <?php echo $rev['status']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($rev['status'] !== 'Approved'): ?>
                                    <a href="reviews.php?action=approve&id=<?php echo $rev['id']; ?>" class="btn btn-sm btn-outline-success py-1 px-2 me-1" title="Approve"><i class="bi bi-check-circle"></i></a>
                                <?php endif; ?>
                                <?php if ($rev['status'] !== 'Rejected'): ?>
                                    <a href="reviews.php?action=reject&id=<?php echo $rev['id']; ?>" class="btn btn-sm btn-outline-warning py-1 px-2 me-1" title="Reject"><i class="bi bi-x-circle"></i></a>
                                <?php endif; ?>
                                <a href="reviews.php?action=delete&id=<?php echo $rev['id']; ?>" class="btn btn-sm btn-outline-danger py-1 px-2" onclick="return confirm('Delete review?')" title="Delete"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once __DIR__ . '/footer.php'; ?>
