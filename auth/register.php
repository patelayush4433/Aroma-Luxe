<?php
/**
 * Customer Registration Page
 */
require_once __DIR__ . '/../config/config.php';

if (isset($_SESSION['customer_id'])) {
    header("Location: ../index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);
    $phone = sanitize($_POST['phone']);
    $birthday = sanitize($_POST['birthday']);
    $referred_by = sanitize($_POST['referred_by']);

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Name, email and password are required.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `customers` WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Email address is already registered.";
        } else {
            // Hash password
            $hashedPass = password_hash($password, PASSWORD_DEFAULT);
            
            // Generate user's unique referral code
            $cleanName = preg_replace('/[^A-Za-z0-9]/', '', strtoupper(explode(' ', $name)[0]));
            $uniqueRef = $cleanName . '-' . rand(1000, 9999);
            
            // Set up verification code
            $otpCode = (string)rand(100000, 999999);

            try {
                $pdo->beginTransaction();
                
                // Track referral rewards
                $loyaltyPoints = 0;
                $validReferral = null;
                
                if (!empty($referred_by)) {
                    // Check if referral code is valid
                    $stmt = $pdo->prepare("SELECT id, name FROM `customers` WHERE referral_code = ?");
                    $stmt->execute([$referred_by]);
                    $referrer = $stmt->fetch();
                    
                    if ($referrer) {
                        $validReferral = $referred_by;
                        // Reward referrer with 50 loyalty points
                        $updateStmt = $pdo->prepare("UPDATE `customers` SET loyalty_points = loyalty_points + 50 WHERE id = ?");
                        $updateStmt->execute([$referrer['id']]);
                        
                        // Send notification to referrer
                        sendSimulatedNotification(
                            'Email',
                            $referrer['name'] . ' <ref@example.com>',
                            'Your Referral Reward - AromaLuxe',
                            "Hello " . $referrer['name'] . ",\n\nCongratulations! " . $name . " has registered using your referral code. 50 Loyalty Points have been credited to your account!"
                        );
                    }
                }

                // Insert customer
                $stmt = $pdo->prepare("INSERT INTO `customers` (name, email, password, phone, is_verified, verification_otp, referral_code, referred_by, loyalty_points, birthday) VALUES (?, ?, ?, ?, 0, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hashedPass, $phone, $otpCode, $uniqueRef, $validReferral, $loyaltyPoints, $birthday]);
                
                $pdo->commit();

                // Send simulated email with OTP
                sendSimulatedNotification(
                    'Email',
                    $name . ' <' . $email . '>',
                    'Verify Your Account - AromaLuxe',
                    "Hello " . $name . ",\n\nThank you for choosing AromaLuxe! To complete your registration, please verify your email address using this OTP:\n\nVerification OTP: " . $otpCode . "\n\nWelcome to botanical luxury."
                );

                $_SESSION['verify_email_temp'] = $email;
                setFlashMessage("info", "A verification code has been sent to your email.");
                header("Location: otp-verify.php");
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Registration failed. Try again. " . $e->getMessage();
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
    <title>Register - AromaLuxe</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Style CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-5" style="background: radial-gradient(circle, #1a1a1a 0%, #0a0a0a 100%);">

    <div class="container my-auto">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="text-center mb-4">
                    <a href="../index.php" class="luxury-font text-warning fs-2 fw-bold" style="letter-spacing: 4px;">AROMALUXE</a>
                    <p class="text-muted small mt-1">THE ESSENCE OF BOTANICAL PERFECTION</p>
                </div>

                <div class="glass-card p-4 p-md-5">
                    <h3 class="font-heading text-center text-white mb-4">Create Account</h3>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <!-- Name -->
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Full Name</label>
                            <input type="text" name="name" class="form-control bg-transparent border-secondary text-white" placeholder="Sophia Loren" required>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Email Address</label>
                            <input type="email" name="email" class="form-control bg-transparent border-secondary text-white" placeholder="sophia@example.com" required>
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Phone Number</label>
                            <input type="tel" name="phone" class="form-control bg-transparent border-secondary text-white" placeholder="+1234567890">
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Password</label>
                            <input type="password" name="password" class="form-control bg-transparent border-secondary text-white" placeholder="••••••••" required>
                        </div>

                        <!-- Birthday -->
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Date of Birth (For Birthday Discounts!)</label>
                            <input type="date" name="birthday" class="form-control bg-transparent border-secondary text-white">
                        </div>

                        <!-- Referral Code -->
                        <div class="mb-4">
                            <label class="form-label text-white-50 small">Referral Code (Optional)</label>
                            <input type="text" name="referred_by" class="form-control bg-transparent border-secondary text-white" placeholder="SOPHIA-1234">
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-gold w-100 mb-3">Sign Up</button>

                        <div class="text-center mt-3 small text-muted">
                            Already have an account? <a href="login.php" class="text-warning text-decoration-underline">Sign In</a>
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
