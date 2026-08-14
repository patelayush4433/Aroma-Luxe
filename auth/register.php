<?php
/**
 * Customer Registration Page - Ultra Luxury Split Layout
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
                    $stmt = $pdo->prepare("SELECT id, name FROM `customers` WHERE referral_code = ?");
                    $stmt->execute([$referred_by]);
                    $referrer = $stmt->fetch();
                    
                    if ($referrer) {
                        $validReferral = $referred_by;
                        $updateStmt = $pdo->prepare("UPDATE `customers` SET loyalty_points = loyalty_points + 50 WHERE id = ?");
                        $updateStmt->execute([$referrer['id']]);
                        
                        sendSimulatedNotification(
                            'Email',
                            $referrer['name'] . ' <ref@example.com>',
                            'Your Referral Reward - AromaLuxe',
                            "Hello " . $referrer['name'] . ",\n\nCongratulations! " . $name . " has registered using your referral code. 50 Loyalty Points have been credited to your account!"
                        );
                    }
                }

                $stmt = $pdo->prepare("INSERT INTO `customers` (name, email, password, phone, is_verified, verification_otp, referral_code, referred_by, loyalty_points, birthday) VALUES (?, ?, ?, ?, 0, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hashedPass, $phone, $otpCode, $uniqueRef, $validReferral, $loyaltyPoints, $birthday]);
                
                $pdo->commit();

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
    <title>Create Account — AromaLuxe Private Atelier</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Style CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        body {
            background-color: #06070e;
            color: #f8fafc;
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .auth-split-wrapper {
            min-height: 100vh;
            width: 100%;
        }
        
        /* Left Luxury Showcase Column */
        .auth-showcase-col {
            background: radial-gradient(circle at center, rgba(212, 168, 83, 0.15) 0%, rgba(6, 7, 14, 0.98) 75%), url('../assets/images/hero_luxury_banner.png') center center / cover no-repeat;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            border-right: 1px solid rgba(212, 168, 83, 0.2);
            overflow: hidden;
        }
        
        .auth-showcase-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(6,7,14,0.85) 0%, rgba(6,7,14,0.55) 50%, rgba(6,7,14,0.92) 100%);
            z-index: 1;
        }
        
        .auth-showcase-content {
            position: relative;
            z-index: 2;
            max-width: 480px;
        }

        .auth-bottle-img {
            max-width: 260px;
            width: 100%;
            height: auto;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.8), 0 0 35px rgba(212,168,83,0.3);
            border: 1px solid rgba(212,168,83,0.35);
            animation: floatLevitate 5s ease-in-out infinite;
        }

        /* Right Glassmorphic Form Column */
        .auth-form-col {
            background: radial-gradient(circle at top right, rgba(212, 168, 83, 0.08) 0%, #06070e 65%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
        }
        
        .auth-glass-card {
            background: rgba(15, 18, 30, 0.82);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1.5px solid rgba(212, 168, 83, 0.25);
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.75), 0 0 50px rgba(212, 168, 83, 0.12);
            width: 100%;
            max-width: 520px;
            padding: 2.75rem 2.5rem;
            position: relative;
            z-index: 2;
        }

        .auth-input-group {
            position: relative;
        }

        .auth-input-group .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #d4a853;
            font-size: 1.1rem;
            z-index: 5;
        }

        .auth-input-group .form-control {
            background: rgba(6, 7, 14, 0.65) !important;
            border: 1px solid rgba(212, 168, 83, 0.25) !important;
            color: #f8fafc !important;
            border-radius: 12px !important;
            padding: 13px 16px 13px 48px !important;
            font-size: 0.92rem;
            transition: all 0.3s ease;
        }

        .auth-input-group .form-control:focus {
            border-color: #d4a853 !important;
            box-shadow: 0 0 20px rgba(212, 168, 83, 0.25) !important;
            background: rgba(15, 18, 30, 0.95) !important;
        }

        /* Fix webkit autofill pale background */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 50px #0c0e18 inset !important;
            -webkit-text-fill-color: #f8fafc !important;
            border-color: rgba(212, 168, 83, 0.4) !important;
        }

        .toggle-password-btn {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            z-index: 5;
        }
        .toggle-password-btn:hover {
            color: #d4a853;
        }

        .btn-auth-gold {
            background: linear-gradient(135deg, #d4a853 0%, #c98b6e 100%);
            color: #06070e;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            border: none;
            border-radius: 12px;
            padding: 14px 20px;
            font-size: 0.9rem;
            box-shadow: 0 10px 30px rgba(212, 168, 83, 0.35);
            transition: all 0.3s ease;
        }
        .btn-auth-gold:hover {
            background: linear-gradient(135deg, #f5e6c4 0%, #d4a853 100%);
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(212, 168, 83, 0.5);
            color: #06070e;
        }
    </style>
</head>
<body>

    <div class="auth-split-wrapper container-fluid p-0">
        <div class="row g-0 min-vh-100">

            <!-- Left Luxury Visual Showcase -->
            <div class="d-none d-lg-flex col-lg-6 auth-showcase-col">
                <div class="auth-showcase-overlay"></div>
                <div class="auth-showcase-content text-center">
                    <div class="mb-4">
                        <span class="badge bg-outline-gold text-warning border border-warning px-3 py-2 text-uppercase mb-3" style="letter-spacing: 3px; font-size: 0.7rem;">✦ Join The Atelier ✦</span>
                    </div>

                    <div class="my-4">
                        <img src="../assets/images/hero_luxury_banner.png" alt="AromaLuxe Perfume" class="auth-bottle-img">
                    </div>

                    <h2 class="font-display text-white mb-3" style="letter-spacing: 2px; font-size: 2.2rem;">
                        Begin Your Fragrance Journey
                    </h2>
                    <p class="text-secondary small leading-relaxed mb-4" style="line-height: 1.8;">
                        Create your complimentary private account today to unlock 50 Loyalty Points, welcome discount vouchers, personalized scent profiling, and priority launch access.
                    </p>

                    <div class="d-flex justify-content-center gap-4 text-warning small" style="letter-spacing: 1px;">
                        <span><i class="bi bi-gift me-1"></i> 50 Bonus Points</span>
                        <span><i class="bi bi-percent me-1"></i> 10% Off First Order</span>
                        <span><i class="bi bi-stars me-1"></i> VIP Access</span>
                    </div>
                </div>
            </div>

            <!-- Right Glassmorphic Form Column -->
            <div class="col-12 col-lg-6 auth-form-col">
                <div class="auth-glass-card">

                    <!-- Brand Header -->
                    <div class="text-center mb-4">
                        <a href="../index.php" class="font-display text-warning fs-3 fw-bold text-decoration-none d-inline-block" style="letter-spacing: 4px;">
                            AROMALUXE
                        </a>
                        <p class="text-secondary small text-uppercase mt-1 mb-0" style="letter-spacing: 2px; font-size: 0.68rem;">Haute Parfumerie Paris</p>
                    </div>

                    <h4 class="font-display text-center text-white mb-4" style="letter-spacing: 1px;">Create Your Atelier Account</h4>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger bg-danger-subtle border border-danger text-danger small py-2 px-3 rounded-3 mb-4 d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                            <div><?php echo $error; ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row g-3">
                            <!-- Full Name -->
                            <div class="col-12">
                                <label class="form-label text-light small fw-medium mb-1">Full Name *</label>
                                <div class="auth-input-group">
                                    <i class="bi bi-person input-icon"></i>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Ayush Patel" required>
                                </div>
                            </div>

                            <!-- Email Address -->
                            <div class="col-12">
                                <label class="form-label text-light small fw-medium mb-1">Email Address *</label>
                                <div class="auth-input-group">
                                    <i class="bi bi-envelope-at input-icon"></i>
                                    <input type="email" name="email" class="form-control" placeholder="your.email@domain.com" required>
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label text-light small fw-medium m-0">Create Password *</label>
                                    <button type="button" class="btn btn-link p-0 text-warning text-decoration-none small" style="font-size: 0.78rem;" onclick="generateStrongPassword('regPassword')">
                                        <i class="bi bi-key-fill me-1"></i>Generate Strong Password
                                    </button>
                                </div>
                                <div class="auth-input-group">
                                    <i class="bi bi-lock input-icon"></i>
                                    <input type="password" name="password" id="regPassword" class="form-control" placeholder="Minimum 8 characters" required oninput="checkPasswordStrength(this.value)">
                                    <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('regPassword', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>

                                <!-- Live Strength Meter -->
                                <div class="mt-2 p-2 rounded-3" id="passwordStrengthBox" style="display: none; background: rgba(6,7,14,0.6); border: 1px solid rgba(212,168,83,0.2);">
                                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.72rem;">
                                        <span class="text-secondary">Password Strength:</span>
                                        <span class="fw-bold" id="strengthBadge">Weak</span>
                                    </div>
                                    <div class="progress bg-dark border border-secondary border-opacity-50" style="height: 5px;">
                                        <div id="strengthBar" class="progress-bar bg-danger" role="progressbar" style="width: 0%; transition: all 0.3s ease;"></div>
                                    </div>
                                    <div class="row g-1 mt-2 text-secondary" style="font-size: 0.7rem;">
                                        <div class="col-6" id="checkLength"><i class="bi bi-circle me-1"></i> 8+ characters</div>
                                        <div class="col-6" id="checkUpper"><i class="bi bi-circle me-1"></i> Uppercase (A-Z)</div>
                                        <div class="col-6" id="checkNumber"><i class="bi bi-circle me-1"></i> Number (0-9)</div>
                                        <div class="col-6" id="checkSymbol"><i class="bi bi-circle me-1"></i> Symbol (@,#,$)</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Phone Number -->
                            <div class="col-md-6">
                                <label class="form-label text-light small fw-medium mb-1">Phone Number</label>
                                <div class="auth-input-group">
                                    <i class="bi bi-telephone input-icon"></i>
                                    <input type="tel" name="phone" class="form-control" placeholder="+91 98765 43210">
                                </div>
                            </div>

                            <!-- Date of Birth -->
                            <div class="col-md-6">
                                <label class="form-label text-light small fw-medium mb-1">Birthday (Gift Note)</label>
                                <div class="auth-input-group">
                                    <i class="bi bi-cake2 input-icon"></i>
                                    <input type="date" name="birthday" class="form-control">
                                </div>
                            </div>

                            <!-- Referral Code -->
                            <div class="col-12">
                                <label class="form-label text-light small fw-medium mb-1">Referral Code (Optional)</label>
                                <div class="auth-input-group">
                                    <i class="bi bi-ticket-perforated input-icon"></i>
                                    <input type="text" name="referred_by" class="form-control" placeholder="e.g. FRIEND-9821">
                                </div>
                            </div>
                        </div>

                        <!-- Terms Checkbox -->
                        <div class="form-check mt-3 mb-4">
                            <input class="form-check-input border-secondary" type="checkbox" id="termsCheck" required checked>
                            <label class="form-check-label text-secondary small" for="termsCheck">
                                I agree to AromaLuxe's <a href="#" class="text-warning text-decoration-none">Terms of Service</a> and <a href="#" class="text-warning text-decoration-none">Privacy Policy</a>.
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-auth-gold w-100 mb-3">
                            <i class="bi bi-person-plus-fill me-2"></i>Create My Account
                        </button>

                        <!-- Login Toggle -->
                        <div class="text-center mt-3 pt-2 border-top border-secondary border-opacity-25 small text-secondary">
                            Already have an Atelier account? 
                            <a href="login.php" class="text-warning fw-semibold text-decoration-none ms-1">Sign In Here</a>
                        </div>
                    </form>

                    <!-- Back to Home -->
                    <div class="text-center mt-4">
                        <a href="../index.php" class="text-secondary small text-decoration-none hover-gold">
                            <i class="bi bi-arrow-left me-1"></i> Return to Storefront
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        function generateStrongPassword(inputId) {
            const uppers = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            const lowers = 'abcdefghijklmnopqrstuvwxyz';
            const numbers = '0123456789';
            const symbols = '!@#$%^&*';
            const allChars = uppers + lowers + numbers + symbols;

            let password = '';
            password += uppers.charAt(Math.floor(Math.random() * uppers.length));
            password += lowers.charAt(Math.floor(Math.random() * lowers.length));
            password += numbers.charAt(Math.floor(Math.random() * numbers.length));
            password += symbols.charAt(Math.floor(Math.random() * symbols.length));

            for (let i = 4; i < 16; i++) {
                password += allChars.charAt(Math.floor(Math.random() * allChars.length));
            }
            password = password.split('').sort(() => 0.5 - Math.random()).join('');

            const input = document.getElementById(inputId);
            if (input) {
                input.value = password;
                input.type = 'text';
                checkPasswordStrength(password);
            }
        }

        function checkPasswordStrength(password) {
            const box = document.getElementById('passwordStrengthBox');
            if (!box) return;
            if (!password) {
                box.style.display = 'none';
                return;
            }
            box.style.display = 'block';

            let score = 0;
            const hasLength = password.length >= 8;
            const hasUpper = /[A-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSymbol = /[^A-Za-z0-9]/.test(password);

            if (hasLength) score++;
            if (hasUpper) score++;
            if (hasNumber) score++;
            if (hasSymbol) score++;

            updateCheckItem('checkLength', hasLength, '8+ characters');
            updateCheckItem('checkUpper', hasUpper, 'Uppercase (A-Z)');
            updateCheckItem('checkNumber', hasNumber, 'Number (0-9)');
            updateCheckItem('checkSymbol', hasSymbol, 'Symbol (@,#,$)');

            const bar = document.getElementById('strengthBar');
            const badge = document.getElementById('strengthBadge');

            if (score <= 1) {
                bar.style.width = '25%';
                bar.className = 'progress-bar bg-danger';
                badge.innerText = 'Weak';
                badge.className = 'fw-bold text-danger';
            } else if (score === 2) {
                bar.style.width = '50%';
                bar.className = 'progress-bar bg-warning';
                badge.innerText = 'Fair';
                badge.className = 'fw-bold text-warning';
            } else if (score === 3) {
                bar.style.width = '75%';
                bar.className = 'progress-bar bg-info';
                badge.innerText = 'Good';
                badge.className = 'fw-bold text-info';
            } else {
                bar.style.width = '100%';
                bar.className = 'progress-bar bg-success';
                badge.innerText = 'Strong 🔒';
                badge.className = 'fw-bold text-success';
            }
        }

        function updateCheckItem(elementId, isValid, text) {
            const el = document.getElementById(elementId);
            if (!el) return;
            if (isValid) {
                el.className = 'col-6 text-success';
                el.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>' + text;
            } else {
                el.className = 'col-6 text-secondary';
                el.innerHTML = '<i class="bi bi-circle me-1"></i>' + text;
            }
        }
    </script>
</body>
</html>
