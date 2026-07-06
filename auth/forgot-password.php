<?php
/**
 * Forgot Password Simulation Page
 */
require_once __DIR__ . '/../config/config.php';

$error = '';
$success = '';
$step = 1; // 1: Email Request, 2: OTP & New Password Verification

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['request_reset'])) {
        $email = sanitize($_POST['email']);
        
        $stmt = $pdo->prepare("SELECT * FROM `customers` WHERE email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();
        
        if ($customer) {
            $otp = (string)rand(100000, 999999);
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_otp'] = $otp;
            
            // Dispatch notification
            sendSimulatedNotification(
                'Email',
                $customer['name'] . ' <' . $email . '>',
                'Password Reset Token - AromaLuxe',
                "Hello " . $customer['name'] . ",\n\nWe received a request to reset your password. Use the following code to authorize this change:\n\nReset OTP: " . $otp . "\n\nIf you did not request this, please ignore this email."
            );
            
            $success = "Verification code sent to your email. Check Simulated Inbox at bottom left.";
            $step = 2;
        } else {
            $error = "Email address not found.";
        }
    } elseif (isset($_POST['reset_password'])) {
        $otp = sanitize($_POST['otp']);
        $new_pass = sanitize($_POST['new_password']);
        $email = $_SESSION['reset_email'] ?? '';
        $saved_otp = $_SESSION['reset_otp'] ?? '';
        
        if ($otp === $saved_otp && !empty($email)) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE `customers` SET password = ? WHERE email = ?");
            $stmt->execute([$hashed, $email]);
            
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_otp']);
            
            setFlashMessage("success", "Password reset successful. You can now login.");
            header("Location: login.php");
            exit;
        } else {
            $error = "Invalid OTP code. Please try again.";
            $step = 2;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - AromaLuxe</title>
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
                    <h3 class="font-heading text-center text-white mb-3">Reset Password</h3>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success bg-success-subtle border-0 text-success small py-2"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <?php if ($step === 1): ?>
                        <form method="POST" action="">
                            <div class="mb-4">
                                <label class="form-label text-white-50 small">Enter Your Email Address</label>
                                <input type="email" name="email" class="form-control bg-transparent border-secondary text-white" placeholder="sophia@example.com" required>
                            </div>
                            <button type="submit" name="request_reset" class="btn btn-gold w-100 mb-3">Send Reset Code</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label text-white-50 small">Enter Verification OTP</label>
                                <input type="text" name="otp" class="form-control bg-transparent border-secondary text-white text-center font-monospace" placeholder="000000" maxlength="6" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-white-50 small">New Password</label>
                                <input type="password" name="new_password" class="form-control bg-transparent border-secondary text-white" placeholder="••••••••" required>
                            </div>
                            <button type="submit" name="reset_password" class="btn btn-gold w-100 mb-3">Update Password</button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="text-center mt-4">
                    <a href="login.php" class="text-white-50 small"><i class="bi bi-arrow-left me-2"></i>Back to Login</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Main JS -->
    <script src="../assets/js/main.js"></script>
    <?php 
    $no_visible_footer = true;
    include_once __DIR__ . '/../includes/footer.php'; 
    ?>
</body>
</html>
