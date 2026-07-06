<?php
/**
 * Customer Login Page
 */
require_once __DIR__ . '/../config/config.php';

if (isset($_SESSION['customer_id'])) {
    header("Location: ../index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM `customers` WHERE email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        if ($customer && password_verify($password, $customer['password'])) {
            if ($customer['is_verified'] == 0) {
                // Redirect to OTP verification simulation
                $_SESSION['verify_email_temp'] = $email;
                setFlashMessage("warning", "Please verify your email address to log in.");
                header("Location: otp-verify.php");
                exit;
            }

            // Set login session
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['name'];
            $_SESSION['customer_email'] = $customer['email'];

            setFlashMessage("success", "Welcome back, " . $customer['name'] . "!");

            // Redirect back to page or profile
            $redirect = isset($_SESSION['redirect_to']) ? $_SESSION['redirect_to'] : '../profile.php';
            unset($_SESSION['redirect_to']);
            header("Location: " . $redirect);
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AromaLuxe</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Style CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100" style="background: radial-gradient(circle, #1a1a1a 0%, #0a0a0a 100%);">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="text-center mb-4">
                    <a href="../index.php" class="luxury-font text-warning fs-2 fw-bold" style="letter-spacing: 4px;">AROMALUXE</a>
                    <p class="text-muted small mt-1">THE ESSENCE OF BOTANICAL PERFECTION</p>
                </div>

                <div class="glass-card p-4 p-md-5">
                    <h3 class="font-heading text-center text-white mb-4">Sign In</h3>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <!-- Email Address -->
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Email Address</label>
                            <input type="email" name="email" class="form-control bg-transparent border-secondary text-white" placeholder="name@example.com" required>
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <label class="form-label text-white-50 small m-0">Password</label>
                                <a href="forgot-password.php" class="text-warning small text-decoration-underline" style="font-size: 0.8rem;">Forgot?</a>
                            </div>
                            <input type="password" name="password" class="form-control bg-transparent border-secondary text-white" placeholder="••••••••" required>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-gold w-100 mb-3">Login</button>

                        <div class="text-center mt-3 small text-muted">
                            New to AromaLuxe? <a href="register.php" class="text-warning text-decoration-underline">Create Account</a>
                        </div>
                    </form>
                </div>

                <div class="text-center mt-4">
                    <a href="../index.php" class="text-white-50 small"><i class="bi bi-arrow-left me-2"></i>Back to Home</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Main JS -->
    <script src="../assets/js/main.js"></script>
</body>
</html>
