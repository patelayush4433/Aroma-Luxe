<?php
/**
 * Customer Login Page - Ultra Luxury Split Layout
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
                $_SESSION['verify_email_temp'] = $email;
                setFlashMessage("warning", "Please verify your email address to log in.");
                header("Location: otp-verify.php");
                exit;
            }

            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['name'];
            $_SESSION['customer_email'] = $customer['email'];

            setFlashMessage("success", "Welcome back, " . $customer['name'] . "!");

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
    <title>Sign In — AromaLuxe Private Atelier</title>
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
            max-width: 450px;
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
            font-size: 1.15rem;
            z-index: 5;
        }

        .auth-input-group .form-control {
            background: rgba(6, 7, 14, 0.65) !important;
            border: 1px solid rgba(212, 168, 83, 0.25) !important;
            color: #f8fafc !important;
            border-radius: 12px !important;
            padding: 14px 16px 14px 48px !important;
            font-size: 0.95rem;
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
                        <span class="badge bg-outline-gold text-warning border border-warning px-3 py-2 text-uppercase mb-3" style="letter-spacing: 3px; font-size: 0.7rem;">✦ Private Atelier ✦</span>
                    </div>

                    <div class="my-4">
                        <img src="../assets/images/hero_luxury_banner.png" alt="AromaLuxe Perfume" class="auth-bottle-img">
                    </div>

                    <h2 class="font-display text-white mb-3" style="letter-spacing: 2px; font-size: 2.2rem;">
                        Where Scent Meets Soul
                    </h2>
                    <p class="text-secondary small leading-relaxed mb-4" style="line-height: 1.8;">
                        Sign in to access your curated wishlist, bespoke custom fragrances, private order history, and exclusive member-only releases.
                    </p>

                    <div class="d-flex justify-content-center gap-4 text-warning small" style="letter-spacing: 1px;">
                        <span><i class="bi bi-shield-check me-1"></i> 100% Authentic</span>
                        <span><i class="bi bi-truck me-1"></i> Express Shipping</span>
                        <span><i class="bi bi-gem me-1"></i> Atelier Rewards</span>
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

                    <h4 class="font-display text-center text-white mb-4" style="letter-spacing: 1px;">Sign In to Your Account</h4>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger bg-danger-subtle border border-danger text-danger small py-2 px-3 rounded-3 mb-4 d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                            <div><?php echo $error; ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <!-- Email Input -->
                        <div class="mb-3">
                            <label class="form-label text-light small fw-medium mb-1">Email Address</label>
                            <div class="auth-input-group">
                                <i class="bi bi-envelope-at input-icon"></i>
                                <input type="email" name="email" id="loginEmail" class="form-control" placeholder="your.email@domain.com" required>
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-light small fw-medium m-0">Password</label>
                                <a href="forgot-password.php" class="text-warning small text-decoration-none" style="font-size: 0.8rem;">Forgot Password?</a>
                            </div>
                            <div class="auth-input-group">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" name="password" id="loginPassword" class="form-control" placeholder="••••••••" required>
                                <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('loginPassword', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-auth-gold w-100 mb-3">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </button>

                        <!-- Demo Customer Quick Fill -->
                        <div class="p-2 mb-3 rounded-3 text-center" style="background: rgba(212,168,83,0.08); border: 1px dashed rgba(212,168,83,0.3);">
                            <span class="small text-muted me-2">Quick Test Account:</span>
                            <button type="button" class="btn btn-sm btn-outline-warning py-0 px-2" style="font-size: 0.75rem;" onclick="fillDemoCustomer()">
                                <i class="bi bi-person-check me-1"></i>Fill Demo Customer
                            </button>
                        </div>

                        <!-- Register Toggle -->
                        <div class="text-center mt-4 pt-2 border-top border-secondary border-opacity-25 small text-secondary">
                            Don't have an Atelier account? 
                            <a href="register.php" class="text-warning fw-semibold text-decoration-none ms-1">Create Account</a>
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

        function fillDemoCustomer() {
            document.getElementById('loginEmail').value = 'heett@gmail.com';
            document.getElementById('loginPassword').value = 'heett';
        }
    </script>
</body>
</html>
