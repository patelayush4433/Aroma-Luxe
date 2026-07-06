<?php
/**
 * Administrator Secure Registration Page
 */
require_once __DIR__ . '/../config/config.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `admin` WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Username or Email address is already registered.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO `admin` (username, email, password, role) VALUES (?, ?, ?, 'admin')");
                $stmt->execute([$username, $email, $hashed]);
                
                $success = "Registration successful! You can now log in.";
            }
        } catch (PDOException $e) {
            $error = "Error during registration: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - AromaLuxe</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Style CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-dark">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="text-center mb-4">
                    <span class="text-warning text-uppercase small tracking-widest" style="font-size:0.7rem;">Administrative Portal</span>
                    <h2 class="luxury-font text-white mt-1 fw-bold">AROMALUXE</h2>
                </div>

                <div class="admin-card p-4 p-md-5">
                    <h4 class="font-heading text-center text-warning mb-4"><i class="bi bi-person-plus me-2"></i>Create Admin Account</h4>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success bg-success-subtle border-0 text-success small py-2"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Username</label>
                            <input type="text" name="username" class="form-control bg-transparent border-secondary text-white" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Email Address</label>
                            <input type="email" name="email" class="form-control bg-transparent border-secondary text-white" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Password</label>
                            <input type="password" name="password" class="form-control bg-transparent border-secondary text-white" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-white-50 small">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control bg-transparent border-secondary text-white" required>
                        </div>
                        <button type="submit" class="btn btn-admin-gold w-100 py-2">Register Admin</button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <span class="text-white-50 small">Already have an account? <a href="login.php" class="text-warning">Sign In</a></span>
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
