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
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-white-50 small m-0">Password *</label>
                                <button type="button" class="btn btn-link p-0 text-warning text-decoration-none small" style="font-size: 0.78rem;" onclick="generateStrongPassword('adminPassword', 'adminConfirmPassword')">
                                    <i class="bi bi-key-fill me-1"></i>Generate Strong Password
                                </button>
                            </div>
                            <input type="password" name="password" id="adminPassword" class="form-control bg-transparent border-secondary text-white" required oninput="checkPasswordStrength(this.value)">
                            
                            <!-- Live Strength Meter -->
                            <div class="mt-2 p-2 rounded-3" id="passwordStrengthBox" style="display: none; background: rgba(15,20,29,0.8); border: 1px solid rgba(212,175,55,0.2);">
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

                        <div class="mb-4">
                            <label class="form-label text-white-50 small">Confirm Password *</label>
                            <input type="password" name="confirm_password" id="adminConfirmPassword" class="form-control bg-transparent border-secondary text-white" required>
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

    <script>
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
