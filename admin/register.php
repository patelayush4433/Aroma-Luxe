<?php
/**
 * Administrator Secure Registration Page - Executive Cyber Portal
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
    <title>Register Admin Account — AromaLuxe Executive</title>
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
    <link rel="stylesheet" href="../assets/css/admin.css">
    
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
        
        /* Left Executive High-Tech Cyber Showcase */
        .admin-showcase-col {
            background: radial-gradient(circle at center, rgba(212, 168, 83, 0.18) 0%, rgba(6, 7, 14, 0.98) 75%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            border-right: 1px solid rgba(212, 168, 83, 0.25);
            overflow: hidden;
        }
        
        .admin-showcase-grid-bg {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: linear-gradient(rgba(212, 168, 83, 0.05) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(212, 168, 83, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: 1;
        }

        .admin-showcase-content {
            position: relative;
            z-index: 2;
            max-width: 500px;
        }

        @keyframes pulseEmblem {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 30px rgba(212, 168, 83, 0.3);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 60px rgba(212, 168, 83, 0.6);
            }
        }

        .executive-shield-badge {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(212, 168, 83, 0.25) 0%, rgba(15, 18, 30, 0.9) 100%);
            border: 2px solid rgba(212, 168, 83, 0.5);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            animation: pulseEmblem 4s ease-in-out infinite;
        }

        .dashboard-mock-card {
            background: rgba(15, 18, 30, 0.85);
            border: 1px solid rgba(212, 168, 83, 0.2);
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
            transition: transform 0.3s ease;
        }
        .dashboard-mock-card:hover {
            transform: translateY(-3px);
            border-color: rgba(212, 168, 83, 0.4);
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
            background: rgba(15, 18, 30, 0.85);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1.5px solid rgba(212, 168, 83, 0.28);
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.8), 0 0 50px rgba(212, 168, 83, 0.15);
            width: 100%;
            max-width: 480px;
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
            background: rgba(6, 7, 14, 0.68) !important;
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

            <!-- Left Executive Cyber Dashboard Showcase -->
            <div class="d-none d-lg-flex col-lg-6 admin-showcase-col">
                <div class="admin-showcase-grid-bg"></div>
                <div class="admin-showcase-content text-center">
                    
                    <!-- Glowing Security Shield Emblem -->
                    <div class="executive-shield-badge">
                        <i class="bi bi-person-badge-fill text-warning fs-1"></i>
                    </div>

                    <h2 class="font-display text-white mb-2" style="letter-spacing: 2px; font-size: 2.1rem;">
                        Register Administrator Account
                    </h2>
                    <p class="text-secondary small leading-relaxed mb-4">
                        Provision administrative credentials for catalog management, coupon generation, and executive store supervision.
                    </p>

                    <!-- Interactive Live Metrics Cards Grid -->
                    <div class="row g-3 mb-4 text-start">
                        <div class="col-6">
                            <div class="dashboard-mock-card">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="small text-muted" style="font-size: 0.72rem;">ADMIN ROLE</span>
                                    <i class="bi bi-person-workspace text-warning"></i>
                                </div>
                                <div class="fw-bold text-white fs-6">Full Control</div>
                                <span class="badge bg-warning-subtle text-warning mt-1" style="font-size: 0.65rem;">Super Admin</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="dashboard-mock-card">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="small text-muted" style="font-size: 0.72rem;">ACCESS AUDIT</span>
                                    <i class="bi bi-shield-check text-success"></i>
                                </div>
                                <div class="fw-bold text-white fs-6">Logged Session</div>
                                <span class="badge bg-success-subtle text-success mt-1" style="font-size: 0.65rem;">Encrypted</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-4 text-warning small" style="letter-spacing: 1px;">
                        <span><i class="bi bi-shield-lock me-1"></i> Role Protection</span>
                        <span><i class="bi bi-key-fill me-1"></i> Strong Passwords</span>
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
                        <p class="text-secondary small text-uppercase mt-1 mb-0" style="letter-spacing: 2px; font-size: 0.68rem;">Administrative Portal</p>
                    </div>

                    <h4 class="font-display text-center text-white mb-4" style="letter-spacing: 1px;">Create Admin Credentials</h4>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger bg-danger-subtle border border-danger text-danger small py-2 px-3 rounded-3 mb-4 d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                            <div><?php echo $error; ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success bg-success-subtle border border-success text-success small py-2 px-3 rounded-3 mb-4 d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2 fs-6"></i>
                            <div><?php echo $success; ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <!-- Username -->
                        <div class="mb-3">
                            <label class="form-label text-light small fw-medium mb-1">Admin Username *</label>
                            <div class="auth-input-group">
                                <i class="bi bi-shield-person input-icon"></i>
                                <input type="text" name="username" class="form-control" placeholder="e.g. admin_ayush" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                            </div>
                        </div>

                        <!-- Email Address -->
                        <div class="mb-3">
                            <label class="form-label text-light small fw-medium mb-1">Email Address *</label>
                            <div class="auth-input-group">
                                <i class="bi bi-envelope-at input-icon"></i>
                                <input type="email" name="email" class="form-control" placeholder="admin@domain.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-light small fw-medium m-0">Password *</label>
                                <button type="button" class="btn btn-link p-0 text-warning text-decoration-none small" style="font-size: 0.78rem;" onclick="generateStrongPassword('adminPassword', 'adminConfirmPassword')">
                                    <i class="bi bi-key-fill me-1"></i>Generate Strong Password
                                </button>
                            </div>
                            <div class="auth-input-group">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" name="password" id="adminPassword" class="form-control" placeholder="Minimum 8 characters" required oninput="checkPasswordStrength(this.value)">
                                <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('adminPassword', this)">
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

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label class="form-label text-light small fw-medium mb-1">Confirm Password *</label>
                            <div class="auth-input-group">
                                <i class="bi bi-check2-circle input-icon"></i>
                                <input type="password" name="confirm_password" id="adminConfirmPassword" class="form-control" placeholder="Re-enter password" required>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-auth-gold w-100 mb-3">
                            <i class="bi bi-person-plus-fill me-2"></i>Register Admin
                        </button>

                        <!-- Login Toggle -->
                        <div class="text-center mt-3 pt-2 border-top border-secondary border-opacity-25 small text-secondary">
                            Already registered as admin? 
                            <a href="login.php" class="text-warning fw-semibold text-decoration-none ms-1">Sign In Here</a>
                        </div>
                    </form>

                    <!-- Back to Website -->
                    <div class="text-center mt-4">
                        <a href="../index.php" class="text-secondary small text-decoration-none hover-gold">
                            <i class="bi bi-arrow-left me-1"></i> Return to Main Website
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

        function generateStrongPassword(inputId, confirmId) {
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
            const confirmInput = document.getElementById(confirmId);

            if (input) {
                input.value = password;
                input.type = 'text';
                checkPasswordStrength(password);
            }
            if (confirmInput) {
                confirmInput.value = password;
                confirmInput.type = 'text';
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
