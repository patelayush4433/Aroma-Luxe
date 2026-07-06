<?php
/**
 * Customer Profile Dashboard Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth_check.php';

// Protect page access
checkCustomerAuth();

$customerId = $_SESSION['customer_id'];
$error = '';
$success = '';

// Update Profile Details Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $birthday = sanitize($_POST['birthday']);

    if (empty($name)) {
        $error = "Name cannot be empty.";
    } else {
        $stmt = $pdo->prepare("UPDATE `customers` SET name = ?, phone = ?, birthday = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $birthday, $customerId]);
        
        $_SESSION['customer_name'] = $name;
        $success = "Profile details updated successfully.";
    }
}

// Fetch Customer info
$stmt = $pdo->prepare("SELECT * FROM `customers` WHERE id = ?");
$stmt->execute([$customerId]);
$customer = $stmt->fetch();

// Fetch Order History
$stmt = $pdo->prepare("SELECT * FROM `orders` WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->execute([$customerId]);
$orderHistory = $stmt->fetchAll();

// Fetch Wishlist Items
$stmt = $pdo->prepare("
    SELECT w.id as wishlist_entry_id, p.*, b.name as brand_name 
    FROM `wishlist` w
    LEFT JOIN `products` p ON w.product_id = p.id
    LEFT JOIN `brands` b ON p.brand_id = b.id
    WHERE w.customer_id = ?
");
$stmt->execute([$customerId]);
$wishlistItems = $stmt->fetchAll();

// Auto-create saved_cards table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS `saved_cards` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `card_label` VARCHAR(50) DEFAULT 'Personal',
    `cardholder_name` VARCHAR(100) NOT NULL,
    `card_last4` VARCHAR(4) NOT NULL,
    `card_type` VARCHAR(20) DEFAULT 'Visa',
    `expiry_month` VARCHAR(2) NOT NULL,
    `expiry_year` VARCHAR(2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Handle Add Card
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_card'])) {
    $cardholderName = sanitize($_POST['cardholder_name']);
    $cardNumber = preg_replace('/\s+/', '', sanitize($_POST['card_number']));
    $expiryMonth = sanitize($_POST['expiry_month']);
    $expiryYear = sanitize($_POST['expiry_year']);
    $cardLabel = sanitize($_POST['card_label'] ?? 'Personal');

    if (empty($cardholderName) || strlen($cardNumber) < 13 || empty($expiryMonth) || empty($expiryYear)) {
        $error = "Please fill in all card fields correctly.";
    } else {
        $last4 = substr($cardNumber, -4);
        // Detect card type from first digit
        $firstDigit = $cardNumber[0];
        $cardType = 'Visa';
        if ($firstDigit === '5') $cardType = 'Mastercard';
        elseif ($firstDigit === '3') $cardType = 'Amex';
        elseif ($firstDigit === '6') $cardType = 'Discover';

        $stmt = $pdo->prepare("INSERT INTO `saved_cards` (customer_id, card_label, cardholder_name, card_last4, card_type, expiry_month, expiry_year) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$customerId, $cardLabel, $cardholderName, $last4, $cardType, $expiryMonth, $expiryYear]);
        $success = "Card ending in •••• " . $last4 . " saved successfully.";
    }
}

// Handle Delete Card
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_card'])) {
    $cardId = (int)$_POST['card_id'];
    $stmt = $pdo->prepare("DELETE FROM `saved_cards` WHERE id = ? AND customer_id = ?");
    $stmt->execute([$cardId, $customerId]);
    $success = "Card removed from your vault.";
}

// Fetch saved cards
$stmt = $pdo->prepare("SELECT * FROM `saved_cards` WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->execute([$customerId]);
$savedCards = $stmt->fetchAll();

include_once __DIR__ . '/includes/header.php';
?>

    <!-- Page Title Header -->
    <div class="py-5 bg-black border-bottom border-secondary text-center">
        <div class="container">
            <span class="text-warning text-uppercase small tracking-widest">Client Portal</span>
            <h1 class="luxury-font text-white display-5 mt-2">My Profile</h1>
            <div class="mx-auto bg-warning mt-3" style="width: 50px; height: 1.5px;"></div>
        </div>
    </div>

    <!-- Profile Dashboard -->
    <div class="container py-5">
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success bg-success-subtle border-0 text-success small py-2 mb-4"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2 mb-4"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row g-4">
            
            <!-- Left Summary Info Card -->
            <div class="col-md-4 col-lg-3">
                <div class="glass-card p-4 text-center">
                    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center border border-warning bg-black text-warning mb-3" style="width: 70px; height: 70px; font-size: 2rem;">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h5 class="text-white font-heading mb-1"><?php echo htmlspecialchars($customer['name']); ?></h5>
                    <p class="small text-secondary mb-3"><?php echo htmlspecialchars($customer['email']); ?></p>
                    
                    <hr class="bg-secondary">

                    <!-- Loyalty points overview -->
                    <div class="bg-black p-3 rounded border border-secondary mb-3">
                        <div class="small text-secondary text-uppercase tracking-wider" style="font-size: 0.65rem;">Loyalty Points Balance</div>
                        <h3 class="text-warning font-heading m-0 mt-1"><?php echo $customer['loyalty_points']; ?></h3>
                        <span class="small text-secondary">Redeemable on next orders</span>
                    </div>

                    <!-- Referral Program -->
                    <div class="bg-black p-3 rounded border border-secondary">
                        <div class="small text-secondary text-uppercase tracking-wider" style="font-size: 0.65rem;">Referral Code</div>
                        <code class="text-white fw-bold d-block mt-1 font-monospace" style="font-size: 1.1rem;"><?php echo $customer['referral_code']; ?></code>
                        <span class="small text-secondary d-block mt-1" style="font-size: 0.7rem;">Share with friends to earn 50 points!</span>
                    </div>
                </div>
            </div>

            <!-- Right Dashboard tabs -->
            <div class="col-md-8 col-lg-9">
                <div class="glass-card p-4">
                    <ul class="nav nav-tabs border-secondary mb-4" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link nav-link-luxury active border-0" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders-pane" type="button" role="tab" aria-controls="orders-pane" aria-selected="true">
                                Orders History
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link nav-link-luxury border-0" id="wishlist-tab" data-bs-toggle="tab" data-bs-target="#wishlist-pane" type="button" role="tab" aria-controls="wishlist-pane" aria-selected="false">
                                Wishlist (<?php echo count($wishlistItems); ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link nav-link-luxury border-0" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-pane" type="button" role="tab" aria-controls="details-pane" aria-selected="false">
                                Account Details
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link nav-link-luxury border-0" id="cards-tab" data-bs-toggle="tab" data-bs-target="#cards-pane" type="button" role="tab" aria-controls="cards-pane" aria-selected="false">
                                Saved Cards
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="profileTabsContent">
                        
                        <!-- Tab: Orders History -->
                        <div class="tab-pane fade show active" id="orders-pane" role="tabpanel" aria-labelledby="orders-tab">
                            <?php if (count($orderHistory) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-dark table-hover align-middle m-0" style="--bs-table-bg: transparent; --bs-table-border-color: rgba(255,255,255,0.05);">
                                        <thead>
                                            <tr class="text-warning font-heading small text-uppercase">
                                                <th>Order Number</th>
                                                <th>Date</th>
                                                <th>Payment Method</th>
                                                <th>Total Paid</th>
                                                <th>Status</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($orderHistory as $ord): ?>
                                                <tr>
                                                    <td class="font-monospace fw-bold text-white small"><?php echo $ord['order_number']; ?></td>
                                                    <td class="text-white-50 small"><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></td>
                                                    <td class="text-white-50 small"><?php echo $ord['payment_method']; ?></td>
                                                    <td class="text-warning fw-bold small"><?php echo formatPrice($ord['final_amount']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $statusMap = [
                                                            'Pending' => 'bg-warning text-dark',
                                                            'Packed' => 'bg-info text-dark',
                                                            'Shipped' => 'bg-primary text-white',
                                                            'Out For Delivery' => 'bg-info text-white',
                                                            'Delivered' => 'bg-success text-white',
                                                            'Cancelled' => 'bg-danger text-white'
                                                        ];
                                                        ?>
                                                        <span class="badge <?php echo $statusMap[$ord['order_status']] ?? 'bg-secondary'; ?> small" style="font-size:0.7rem;">
                                                            <?php echo $ord['order_status']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="invoice.php?order_id=<?php echo $ord['id']; ?>" target="_blank" class="btn btn-sm btn-outline-gold py-1 px-2 me-1" style="font-size:0.7rem;" title="View Invoice"><i class="bi bi-file-earmark-pdf"></i></a>
                                                        <a href="track-order.php?order_id=<?php echo $ord['id']; ?>" class="btn btn-sm btn-gold py-1 px-2" style="font-size:0.7rem;" title="Track Delivery"><i class="bi bi-truck"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-secondary small p-3 text-center">You haven't placed any orders yet.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Tab: Wishlist -->
                        <div class="tab-pane fade" id="wishlist-pane" role="tabpanel" aria-labelledby="wishlist-tab">
                            <?php if (count($wishlistItems) > 0): ?>
                                <div class="row g-4">
                                    <?php foreach ($wishlistItems as $item): ?>
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="product-card h-100 d-flex flex-column justify-content-between text-center">
                                                <div>
                                                     <?php 
                                                     $customName = '';
                                                     if (strpos($item['name'], 'Bespoke: ') === 0) {
                                                         $customName = substr($item['name'], 9);
                                                     }
                                                     ?>
                                                     <div class="product-image-wrap position-relative d-flex align-items-center justify-content-center" style="height: 180px; overflow: hidden;">
                                                         <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" class="img-fluid" style="max-height: 100%;">
                                                         <?php if (!empty($customName)): ?>
                                                             <div class="position-absolute text-center text-white" style="bottom: 35px; left: 10%; right: 10%; font-family: 'Cinzel', serif; font-size: 8px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; color: #ffffff; text-shadow: 0 1px 3px #000; word-break: break-all; line-height: 1.2;">
                                                                 <?php echo htmlspecialchars($customName); ?>
                                                             </div>
                                                         <?php endif; ?>
                                                     </div>
                                                    <div class="small text-secondary font-heading mt-2"><?php echo $item['brand_name']; ?></div>
                                                    <h6 class="text-white font-heading mt-1 mb-2" style="font-size:0.85rem;"><?php echo $item['name']; ?></h6>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-secondary">
                                                    <span class="text-warning fw-bold small"><?php echo formatPrice($item['price_50ml'] - $item['discount_50ml']); ?></span>
                                                    <div class="d-flex gap-2">
                                                        <button onclick="addToCart(<?php echo $item['id']; ?>, '50ml')" class="btn btn-sm btn-gold py-1 px-2" style="font-size:0.7rem;"><i class="bi bi-bag-plus"></i></button>
                                                        <button onclick="toggleWishlist(<?php echo $item['id']; ?>)" class="btn btn-sm btn-outline-danger py-1 px-2" style="font-size:0.7rem;"><i class="bi bi-trash"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-secondary small p-3 text-center">Your wishlist is empty.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Tab: Account Details Form -->
                        <div class="tab-pane fade" id="details-pane" role="tabpanel" aria-labelledby="details-tab">
                            <form method="POST" action="">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-white-50 small">Full Name</label>
                                        <input type="text" name="name" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-white-50 small">Email Address (Read-only)</label>
                                        <input type="email" class="form-control bg-transparent border-secondary text-secondary" value="<?php echo htmlspecialchars($customer['email']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-white-50 small">Phone Number</label>
                                        <input type="tel" name="phone" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($customer['phone']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-white-50 small">Date of Birth</label>
                                        <input type="date" name="birthday" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($customer['birthday']); ?>">
                                    </div>
                                    <div class="col-12 mt-4 text-end">
                                        <button type="submit" name="update_profile" class="btn btn-gold px-4">Save Changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Tab: Saved Cards -->
                        <div class="tab-pane fade" id="cards-pane" role="tabpanel" aria-labelledby="cards-tab">
                            <div class="row g-3">
                                <?php 
                                $cardGradients = [
                                    'linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 100%)',
                                    'linear-gradient(135deg, #1a0a2e 0%, #2d1b4e 100%)',
                                    'linear-gradient(135deg, #0a1a2e 0%, #1b3d5c 100%)',
                                    'linear-gradient(135deg, #2e1a0a 0%, #4e2d1b 100%)',
                                ];
                                $cardIcons = [
                                    'Visa' => 'bi-credit-card-2-front',
                                    'Mastercard' => 'bi-credit-card',
                                    'Amex' => 'bi-credit-card-2-back',
                                    'Discover' => 'bi-credit-card-fill',
                                ];
                                foreach ($savedCards as $idx => $card): 
                                    $gradient = $cardGradients[$idx % count($cardGradients)];
                                    $icon = $cardIcons[$card['card_type']] ?? 'bi-credit-card-2-front';
                                ?>
                                <div class="col-md-6">
                                    <div class="p-3 rounded border border-warning position-relative" style="height: 180px; background: <?php echo $gradient; ?>;">
                                        <!-- Delete button -->
                                        <form method="POST" action="profile.php?tab=cards" class="position-absolute" style="top: 8px; right: 8px; z-index: 5;">
                                            <input type="hidden" name="card_id" value="<?php echo $card['id']; ?>">
                                            <button type="submit" name="delete_card" class="btn btn-sm p-0" style="color: rgba(255,100,100,0.7); font-size: 0.85rem;" title="Remove Card" onclick="return confirm('Remove this card?')">
                                                <i class="bi bi-x-circle-fill"></i>
                                            </button>
                                        </form>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="font-heading" style="font-size:0.7rem; color: #e5c060; letter-spacing: 1.5px;"><?php echo strtoupper($card['card_label']); ?></span>
                                            <i class="bi <?php echo $icon; ?> fs-4" style="color: #e5c060;"></i>
                                        </div>
                                        <div class="font-monospace fs-5 mb-3" style="color: #ffffff;">•••• •••• •••• <?php echo $card['card_last4']; ?></div>
                                        <div class="d-flex justify-content-between small" style="color: rgba(255,255,255,0.5);">
                                            <div>
                                                <span class="d-block" style="font-size:0.55rem; letter-spacing: 1px;">CARDHOLDER</span>
                                                <strong style="color: #ffffff; font-size: 0.8rem;"><?php echo strtoupper($card['cardholder_name']); ?></strong>
                                            </div>
                                            <div class="text-end">
                                                <span class="d-block" style="font-size:0.55rem; letter-spacing: 1px;">EXPIRES</span>
                                                <strong style="color: #ffffff; font-size: 0.8rem;"><?php echo $card['expiry_month']; ?> / <?php echo $card['expiry_year']; ?></strong>
                                            </div>
                                        </div>
                                        <div class="position-absolute small" style="bottom: 6px; right: 12px; color: rgba(229,192,96,0.4); font-size: 0.6rem; letter-spacing: 1px;">
                                            <?php echo $card['card_type']; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <!-- Add New Card Button -->
                                <div class="col-md-6 d-flex align-items-center justify-content-center">
                                    <button class="btn btn-outline-secondary p-4 w-100 rounded d-flex flex-column align-items-center justify-content-center" style="border-color: var(--border-color); color: var(--text-muted); min-height: 180px; border-style: dashed;" data-bs-toggle="modal" data-bs-target="#addCardModal">
                                        <i class="bi bi-plus-circle fs-3 mb-2 text-warning"></i>
                                        <span class="small font-heading text-warning">Add New Card</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Add Card Modal -->
    <div class="modal fade" id="addCardModal" tabindex="-1" aria-labelledby="addCardModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="background: var(--bg-secondary); border: 1px solid var(--border-color) !important; border-radius: 16px;">
                <div class="modal-header border-0 pb-0" style="border-bottom: 1px solid var(--border-color) !important;">
                    <h5 class="modal-title font-heading" style="color: var(--gold);" id="addCardModalLabel">
                        <i class="bi bi-shield-lock me-2"></i>Add Payment Card
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="profile.php?tab=cards">
                    <div class="modal-body pt-3">
                        <p class="small mb-3" style="color: var(--text-muted);">Card details are stored securely. This is a simulated vault for demonstration purposes.</p>

                        <div class="mb-3">
                            <label class="form-label small" style="color: var(--text-secondary);">Card Label</label>
                            <select name="card_label" class="form-select form-select-sm" style="background-color: var(--bg-tertiary); color: var(--text-primary); border-color: var(--border-color);">
                                <option value="Personal">Personal</option>
                                <option value="Business">Business</option>
                                <option value="Family">Family</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small" style="color: var(--text-secondary);">Cardholder Name</label>
                            <input type="text" name="cardholder_name" class="form-control form-control-sm" style="background-color: var(--bg-tertiary); color: var(--text-primary); border-color: var(--border-color);" placeholder="e.g. SOPHIA LOREN" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small" style="color: var(--text-secondary);">Card Number</label>
                            <input type="text" name="card_number" class="form-control form-control-sm font-monospace" style="background-color: var(--bg-tertiary); color: var(--text-primary); border-color: var(--border-color);" placeholder="4111 2222 3333 4444" maxlength="19" required>
                        </div>

                        <div class="row g-2">
                            <div class="col-4">
                                <label class="form-label small" style="color: var(--text-secondary);">Month</label>
                                <select name="expiry_month" class="form-select form-select-sm" style="background-color: var(--bg-tertiary); color: var(--text-primary); border-color: var(--border-color);" required>
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label small" style="color: var(--text-secondary);">Year</label>
                                <select name="expiry_year" class="form-select form-select-sm" style="background-color: var(--bg-tertiary); color: var(--text-primary); border-color: var(--border-color);" required>
                                    <?php for ($y = 26; $y <= 35; $y++): ?>
                                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label small" style="color: var(--text-secondary);">CVV</label>
                                <input type="password" class="form-control form-control-sm font-monospace text-center" style="background-color: var(--bg-tertiary); color: var(--text-primary); border-color: var(--border-color);" placeholder="•••" maxlength="4">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0" style="border-top: 1px solid var(--border-color) !important;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" style="color: var(--text-muted); border-color: var(--border-color);" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_card" class="btn btn-sm btn-gold px-4">Save Card</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Active Tab Selector JS -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            const hash = window.location.hash;
            
            let targetTabId = null;
            if (tabParam === 'wishlist' || hash === '#wishlist') {
                targetTabId = 'wishlist-tab';
            } else if (tabParam === 'details' || hash === '#details') {
                targetTabId = 'details-tab';
            } else if (tabParam === 'cards' || hash === '#cards') {
                targetTabId = 'cards-tab';
            } else if (tabParam === 'orders' || hash === '#orders') {
                targetTabId = 'orders-tab';
            }
            
            if (targetTabId) {
                const tabEl = document.getElementById(targetTabId);
                if (tabEl) {
                    const tab = new bootstrap.Tab(tabEl);
                    tab.show();
                }
            }
        });
    </script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
