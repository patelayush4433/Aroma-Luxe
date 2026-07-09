<?php
/**
 * Global Header layout file
 */
require_once __DIR__ . '/../config/config.php';

// Calculate cart item count
$cartCount = 0;
if (isset($_SESSION['customer_id'])) {
    // Fetch count from database
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM `cart` WHERE customer_id = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    $cartCount = (int)$stmt->fetchColumn();
} else {
    // Session-based cart count
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $cartCount += $item['quantity'];
        }
    }
}

// Calculate wishlist item count
$wishlistCount = 0;
if (isset($_SESSION['customer_id'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `wishlist` WHERE customer_id = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    $wishlistCount = (int)$stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Luxury Perfume Store</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Premium Style CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Dynamic Theme Styling Script -->
    <script>
        const storedTheme = localStorage.getItem('aromaluxe-theme') || 'dark';
        document.documentElement.setAttribute('data-theme', storedTheme);
    </script>
</head>
<body class="body-fade">

    <!-- Cursor Follower -->
    <div class="cursor-follower"></div>

    <!-- Animated Gradient Mesh Background -->
    <div class="gradient-mesh"></div>

    <!-- Special Offer Banner -->
    <div class="offer-banner text-center py-2 text-white small text-uppercase" style="letter-spacing: 3px; font-size: 0.72rem;">
        <span style="background: linear-gradient(90deg, var(--gold-light), var(--gold), var(--rose-gold)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">✦ Grand Launch Offer: Use Code <strong>LUXURY10</strong> for 10% Off ✦</span>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-luxury sticky-top">
        <div class="container">
            <!-- Brand Logo -->
            <a class="navbar-brand luxury-font fw-bold" href="index.php" style="font-size: 1.6rem; letter-spacing: 4px;">
                <i class="bi bi-gem me-2" style="font-size: 1.1rem;"></i>AROMALUXE
            </a>

            <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <!-- Navigation Links with Mega Menu -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-center">
                    <li class="nav-item">
                        <a class="nav-link nav-link-luxury" href="index.php"><?php echo __('home'); ?></a>
                    </li>
                    <li class="nav-item dropdown mega-menu">
                        <a class="nav-link nav-link-luxury dropdown-toggle" href="#" id="shopDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo __('shop'); ?>
                        </a>
                        <div class="mega-dropdown-menu dropdown-menu" aria-labelledby="shopDropdown">
                            <div class="row text-white text-dark-override">
                                <div class="col-md-3">
                                    <h6 class="text-warning text-uppercase mb-3 font-heading">By Gender</h6>
                                    <ul class="list-unstyled">
                                        <li><a class="dropdown-item py-1" href="shop.php?category=mens-perfume">Men's Perfume</a></li>
                                        <li><a class="dropdown-item py-1" href="shop.php?category=womens-perfume">Women's Perfume</a></li>
                                        <li><a class="dropdown-item py-1" href="shop.php?category=unisex">Unisex Perfumes</a></li>
                                    </ul>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-warning text-uppercase mb-3 font-heading">Collections</h6>
                                    <ul class="list-unstyled">
                                        <li><a class="dropdown-item py-1" href="shop.php?category=luxury">Luxury Collection</a></li>
                                        <li><a class="dropdown-item py-1" href="shop.php?category=arabic-perfume">Arabic Oud & Amber</a></li>
                                        <li><a class="dropdown-item py-1" href="shop.php?category=designer-perfume">Designer Brands</a></li>
                                    </ul>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-warning text-uppercase mb-3 font-heading">Gifts & Travel</h6>
                                    <ul class="list-unstyled">
                                        <li><a class="dropdown-item py-1" href="shop.php?category=gift-sets">Exclusive Gift Sets</a></li>
                                        <li><a class="dropdown-item py-1" href="shop.php?category=travel-size">Travel Size / Minis</a></li>
                                        <li><a class="dropdown-item py-1" href="shop.php?filter_limited=1">Limited Editions</a></li>
                                    </ul>
                                </div>
                                <div class="col-md-3 text-center border-start border-secondary d-none d-md-block">
                                    <img src="assets/images/oud_perfume.png" alt="Perfume Promo" class="img-fluid rounded mb-2 border border-secondary img-3d" style="max-height: 120px; object-fit: cover;">
                                    <div class="small text-warning font-heading">Oud Imperial</div>
                                    <a href="shop.php" class="btn btn-sm btn-gold mt-2 py-1 px-3" style="font-size: 0.75rem;">Explore</a>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-luxury" href="blog.php"><?php echo __('blog'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-luxury" href="contact.php"><?php echo __('contact'); ?></a>
                    </li>
                </ul>

                <!-- Live Search Bar -->
                <div class="position-relative me-3 d-none d-lg-block" style="width: 250px;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="bi bi-search"></i></span>
                        <input type="text" id="navSearchInput" class="form-control bg-transparent border-secondary text-white" placeholder="<?php echo __('search_placeholder'); ?>" autocomplete="off">
                    </div>
                    <div id="navSearchDropdown" class="search-dropdown"></div>
                </div>

                <!-- Utilities (Lang, Currency, Light/Dark, Login, Cart) -->
                <div class="d-flex align-items-center gap-3">
                    
                    <!-- Language Selection -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-0 text-white border-secondary small" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:0.8rem;">
                            <?php echo strtoupper($current_lang); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" style="min-width: 80px;">
                            <li><a class="dropdown-item py-1 text-center" href="?lang=en">EN</a></li>
                            <li><a class="dropdown-item py-1 text-center" href="?lang=fr">FR</a></li>
                            <li><a class="dropdown-item py-1 text-center" href="?lang=ar">AR</a></li>
                        </ul>
                    </div>

                    <!-- Currency Selector -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-0 text-white border-secondary small" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:0.8rem;">
                            <?php echo $current_currency; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" style="min-width: 80px;">
                            <?php foreach ($currencies as $code => $data): ?>
                                <li><a class="dropdown-item py-1 text-center" href="?currency=<?php echo $code; ?>"><?php echo $code; ?> (<?php echo $data['symbol']; ?>)</a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Theme Toggle Toggle -->
                    <button class="btn btn-sm btn-outline-secondary border-secondary text-white rounded-circle p-1 d-flex align-items-center justify-content-center" id="themeToggleBtn" style="width: 28px; height: 28px;">
                        <i class="bi bi-sun-fill"></i>
                    </button>

                    <!-- User Account Dropdown -->
                    <div class="dropdown">
                        <a class="text-white nav-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-fill fs-5"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end border border-secondary" style="margin-top: 10px;">
                            <?php if (isset($_SESSION['customer_id'])): ?>
                                <li class="dropdown-header text-warning">Welcome, <?php echo $_SESSION['customer_name']; ?></li>
                                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
                                <li><a class="dropdown-item" href="track-order.php"><i class="bi bi-truck me-2"></i>Track Order</a></li>
                                <li><hr class="dropdown-divider bg-secondary"></li>
                                <li><a class="dropdown-item text-danger" href="auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="auth/login.php"><i class="bi bi-box-arrow-in-right me-2"></i><?php echo __('login'); ?></a></li>
                                <li><a class="dropdown-item" href="auth/register.php"><i class="bi bi-person-plus me-2"></i><?php echo __('register'); ?></a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider bg-secondary"></li>
                            <li><a class="dropdown-item" href="admin/login.php"><i class="bi bi-shield-lock me-2"></i>Admin Area</a></li>
                        </ul>
                    </div>

                    <!-- Wishlist Icon -->
                    <a href="profile.php?tab=wishlist" class="text-white position-relative me-3" title="Wishlist">
                        <i class="bi bi-heart fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger wishlist-badge" style="font-size: 0.65rem; display: <?php echo $wishlistCount > 0 ? 'inline-block' : 'none'; ?>;">
                            <?php echo $wishlistCount; ?>
                        </span>
                    </a>

                    <!-- Shopping Cart Icon -->
                    <a href="cart.php" class="text-white position-relative">
                        <i class="bi bi-bag-fill fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark cart-badge" style="font-size: 0.65rem; display: <?php echo $cartCount > 0 ? 'inline-block' : 'none'; ?>;">
                            <?php echo $cartCount; ?>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Global Toast Container (Alert Flash message triggers) -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <?php 
    // Check and trigger dynamic on-page notifications if we have any flash session set
    $flash = getFlashMessage();
    if ($flash): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast("<?php echo ucfirst($flash['type']); ?>", "<?php echo $flash['message']; ?>", "<?php echo $flash['type']; ?>");
            });
        </script>
    <?php endif; ?>
