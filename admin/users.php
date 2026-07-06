<?php
/**
 * Admin Panel - Admin Users Management
 * Manage admin accounts for the control panel
 */
require_once __DIR__ . '/header.php';

$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$adminId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$success = '';
$error = '';

// Handle Add Admin Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $username = sanitize($_POST['username']);
    $email    = sanitize($_POST['email']);
    $password = $_POST['password'];
    $role     = sanitize($_POST['role']);

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Username, Email, and Password are required.";
    } else {
        try {
            // Check if email/username already exists
            $check = $pdo->prepare("SELECT COUNT(*) FROM `admin` WHERE email = ? OR username = ?");
            $check->execute([$email, $username]);
            if ($check->fetchColumn() > 0) {
                $error = "An admin with this email or username already exists.";
            } else {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO `admin` (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$username, $email, $hashed, $role]);
                setFlashMessage("success", "Admin user '{$username}' created successfully!");
                header("Location: users.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Failed to create admin. " . $e->getMessage();
        }
    }
}

// Handle Delete Admin
if ($action === 'delete' && $adminId > 0) {
    // Prevent deleting yourself
    if ($adminId == $_SESSION['admin_id']) {
        setFlashMessage("danger", "You cannot delete your own account.");
        header("Location: users.php");
        exit;
    }
    try {
        $stmt = $pdo->prepare("DELETE FROM `admin` WHERE id = ?");
        $stmt->execute([$adminId]);
        setFlashMessage("success", "Admin account deleted.");
        header("Location: users.php");
        exit;
    } catch (PDOException $e) {
        $error = "Could not delete admin. " . $e->getMessage();
    }
}

// Fetch all admins
try {
    $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM `admin` ORDER BY created_at DESC");
    $admins = $stmt->fetchAll();
} catch (PDOException $e) {
    $admins = [];
    $error = "Could not fetch admin list. " . $e->getMessage();
}
?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2 mb-4"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="font-heading text-white m-0"><i class="bi bi-shield-lock-fill text-warning me-2"></i>Admin Users</h2>
        <button class="btn btn-admin-gold" data-bs-toggle="modal" data-bs-target="#addAdminModal">
            <i class="bi bi-person-plus-fill me-2"></i>Add New Admin
        </button>
    </div>

    <!-- Admin Users Table -->
    <div class="admin-card p-4 mb-4">
        <div class="table-responsive">
            <table class="table table-luxury align-middle m-0 text-center">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Email Address</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($admins) > 0): ?>
                        <?php foreach ($admins as $adm): ?>
                            <tr>
                                <td><?php echo $adm['id']; ?></td>
                                <td>
                                    <strong class="text-white"><?php echo htmlspecialchars($adm['username']); ?></strong>
                                    <?php if ($adm['id'] == $_SESSION['admin_id']): ?>
                                        <span class="badge bg-warning text-dark ms-1 small" style="font-size:0.6rem;">You</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-white-50 small"><?php echo htmlspecialchars($adm['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $adm['role'] === 'superadmin' ? 'bg-danger' : 'bg-secondary'; ?> py-1 px-2 text-uppercase small">
                                        <?php echo htmlspecialchars($adm['role']); ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?php echo date('M d, Y', strtotime($adm['created_at'])); ?></td>
                                <td>
                                    <?php if ($adm['id'] != $_SESSION['admin_id']): ?>
                                        <a href="users.php?action=delete&id=<?php echo $adm['id']; ?>"
                                           class="btn btn-sm btn-outline-danger py-1 px-2"
                                           onclick="return confirm('Delete admin account: <?php echo htmlspecialchars($adm['username']); ?>?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No admin accounts found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: #1a1a1a; border: 1px solid #333;">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-white font-heading" id="addAdminModalLabel">
                        <i class="bi bi-person-plus text-warning me-2"></i>Add New Admin User
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Username</label>
                            <input type="text" name="username" class="form-control bg-transparent border-secondary text-white" placeholder="e.g. manager1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Email Address</label>
                            <input type="email" name="email" class="form-control bg-transparent border-secondary text-white" placeholder="admin@aromaluxe.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Password</label>
                            <input type="password" name="password" class="form-control bg-transparent border-secondary text-white" placeholder="Minimum 8 characters" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Role</label>
                            <select name="role" class="form-select bg-transparent border-secondary text-white">
                                <option value="admin">Admin</option>
                                <option value="superadmin">Super Admin</option>
                                <option value="moderator">Moderator</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_admin" class="btn btn-admin-gold">
                            <i class="bi bi-check-lg me-1"></i>Create Admin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/footer.php'; ?>
