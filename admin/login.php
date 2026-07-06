<?php
/**
 * Administrator Secure Login Page
 */
require_once __DIR__ . '/../config/config.php';

// Handle Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    header("Location: login.php?logged_out=1");
    exit;
}

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['guest_login'])) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `admin` WHERE username = 'guest'");
            $stmt->execute();
            $admin = $stmt->fetch();
            
            if (!$admin) {
                $guestPassword = password_hash('guest123', PASSWORD_DEFAULT);
                $stmtIns = $pdo->prepare("INSERT INTO `admin` (username, password, email, role) VALUES ('guest', ?, 'guest@aromaluxe.com', 'moderator')");
                $stmtIns->execute([$guestPassword]);
                
                $stmt = $pdo->prepare("SELECT * FROM `admin` WHERE username = 'guest'");
                $stmt->execute();
                $admin = $stmt->fetch();
            }
            
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            $error = "Guest login failed: " . $e->getMessage();
        }
    } else {
        $username = sanitize($_POST['username']);
        $password = sanitize($_POST['password']);

        if (empty($username) || empty($password)) {
            $error = "Please enter admin username and password.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM `admin` WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid administrator credentials.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Area - AromaLuxe</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Style CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-dark">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="text-center mb-4">
                    <span class="text-warning text-uppercase small tracking-widest" style="font-size:0.7rem;">Administrative Portal</span>
                    <h2 class="luxury-font text-white mt-1 fw-bold">AROMALUXE</h2>
                </div>

                <div class="admin-card p-4 p-md-5">
                    <h4 class="font-heading text-center text-warning mb-4"><i class="bi bi-shield-lock me-2"></i>Admin Sign In</h4>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if (isset($_GET['logged_out'])): ?>
                        <div class="alert alert-success bg-success-subtle border-0 text-success small py-2">You have been logged out successfully.</div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Username</label>
                            <input type="text" name="username" class="form-control bg-transparent border-secondary text-white" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-white-50 small">Password</label>
                            <input type="password" name="password" class="form-control bg-transparent border-secondary text-white" required>
                        </div>
                        <button type="submit" class="btn btn-admin-gold w-100 py-2">Sign In</button>
                        <button type="submit" name="guest_login" class="btn btn-outline-warning w-100 py-2 mt-2 animate-pulse" formnovalidate>
                            <i class="bi bi-person-badge-fill me-2"></i>Sign In as Guest
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <span class="text-white-50 small">Don't have an account? <a href="register.php" class="text-warning">Create one</a></span>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="../index.php" class="text-white-50 small"><i class="bi bi-arrow-left me-2"></i>Return to Website</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
