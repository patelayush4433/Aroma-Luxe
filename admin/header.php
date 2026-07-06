<?php
/**
 * Admin Panel Header Layout
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Force Admin Authentication
checkAdminAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AromaLuxe - Control Panel</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Admin styling -->
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <!-- Admin Nav bar header -->
    <nav class="navbar navbar-dark admin-navbar sticky-top">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-secondary border-secondary text-white d-md-none me-2" id="sidebarToggleBtn"><i class="bi bi-list"></i></button>
                <a class="navbar-brand font-heading text-warning m-0 fw-bold" href="dashboard.php" style="font-family:'Cinzel', serif; letter-spacing: 2px;">AROMALUXE CONTROL PANEL</a>
            </div>
            
            <div class="d-flex gap-3 align-items-center">
                <span class="text-white-50 small d-none d-sm-inline">Welcome, <strong><?php echo $_SESSION['admin_username']; ?></strong></span>
                <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-light text-nowrap"><i class="bi bi-box-arrow-up-right me-1"></i>View Site</a>
                <a href="login.php?logout=1" class="btn btn-sm btn-outline-danger" onclick="return confirm('Confirm Administrator Logout?')"><i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>
    </nav>
    
    <?php
    // Admin logout intercept
    if (isset($_GET['logout'])) {
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        header("Location: login.php");
        exit;
    }
    ?>

    <!-- Sidebar Grid Layout -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-3 col-lg-2 admin-sidebar px-0 d-md-block d-none">
                <div class="nav flex-column navbar-dark">
                    <a class="nav-link py-3 active" href="dashboard.php"><i class="bi bi-speedometer2 me-2 text-warning"></i>Dashboard</a>
                    <a class="nav-link py-3" href="products.php"><i class="bi bi-bag-fill me-2 text-warning"></i>Manage Products</a>
                    <a class="nav-link py-3" href="categories.php"><i class="bi bi-grid-fill me-2 text-warning"></i>Manage Categories</a>
                    <a class="nav-link py-3" href="brands.php"><i class="bi bi-tags-fill me-2 text-warning"></i>Manage Brands</a>
                    <a class="nav-link py-3" href="orders.php"><i class="bi bi-cart-fill me-2 text-warning"></i>Manage Orders</a>
                    <a class="nav-link py-3" href="coupons.php"><i class="bi bi-ticket-perforated-fill me-2 text-warning"></i>Manage Coupons</a>
                    <a class="nav-link py-3" href="reviews.php"><i class="bi bi-star-fill me-2 text-warning"></i>Manage Reviews</a>
                    <a class="nav-link py-3" href="customers.php"><i class="bi bi-people-fill me-2 text-warning"></i>Manage Customers</a>
                    <a class="nav-link py-3" href="reports.php"><i class="bi bi-bar-chart-fill me-2 text-warning"></i>Analytics &amp; Reports</a>
                    <a class="nav-link py-3" href="users.php"><i class="bi bi-shield-lock-fill me-2 text-warning"></i>Admin Users</a>
                    <a class="nav-link py-3" href="settings.php"><i class="bi bi-gear-fill me-2 text-warning"></i>Site Settings</a>
                </div>
            </div>

            <!-- Content Area Block open -->
            <div class="col-md-9 col-lg-10 py-4 px-4 px-md-5">
