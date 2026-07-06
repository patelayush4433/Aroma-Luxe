<?php
/**
 * OTP Verification Simulation Page
 */
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['verify_email_temp'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['verify_email_temp'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = sanitize($_POST['otp']);

    if (empty($otp)) {
        $error = "Please enter the verification code.";
    } else {
        // Query to check OTP
        $stmt = $pdo->prepare("SELECT * FROM `customers` WHERE email = ? AND verification_otp = ?");
        $stmt->execute([$email, $otp]);
        $customer = $stmt->fetch();

        if ($customer) {
            // Success! Set verified
            $update = $pdo->prepare("UPDATE `customers` SET is_verified = 1, verification_otp = NULL WHERE id = ?");
            $update->execute([$customer['id']]);

            // Set login session
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['name'];
            $_SESSION['customer_email'] = $customer['email'];
            
            unset($_SESSION['verify_email_temp']);

            // Send welcoming SMS notification
            sendSimulatedNotification(
                'SMS',
                $customer['phone'] ? $customer['phone'] : 'Customer',
                'AromaLuxe Welcome Alert',
                "Welcome to AromaLuxe! Your account is verified. You have been awarded 100 bonus loyalty points. Start exploring our luxury collections."
            );

            // Add loyalty points
            $pointsStmt = $pdo->prepare("UPDATE `customers` SET loyalty_points = loyalty_points + 100 WHERE id = ?");
            $pointsStmt->execute([$customer['id']]);

            setFlashMessage("success", "Account verified successfully! Welcome to AromaLuxe.");
            header("Location: ../profile.php");
            exit;
        } else {
            $error = "Invalid OTP code. Please check your simulated notification center logs at the bottom left of the screen.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - AromaLuxe</title>
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
                    <h3 class="font-heading text-center text-white mb-2">Verification</h3>
                    <p class="small text-muted text-center mb-4">An OTP has been dispatched to <b><?php echo $email; ?></b>. View the simulated inbox logs at the bottom left to retrieve it.</p>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <!-- OTP Input -->
                        <div class="mb-4">
                            <label class="form-label text-white-50 small">Enter 6-Digit OTP</label>
                            <input type="text" name="otp" class="form-control bg-transparent border-secondary text-white text-center font-monospace fs-4" placeholder="000000" maxlength="6" required>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-gold w-100 mb-3">Verify Account</button>
                    </form>
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
    <!-- Include Footer template just for logs helper window -->
    <?php 
    $no_visible_footer = true;
    include_once __DIR__ . '/../includes/footer.php'; 
    ?>
</body>
</html>
