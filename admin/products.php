<?php
/**
 * Admin Panel Product Management
 */
require_once __DIR__ . '/header.php';

$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$success = '';
$error = '';

// Handle Deletion
if ($action === 'delete' && $productId > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM `products` WHERE id = ?");
        $stmt->execute([$productId]);
        setFlashMessage("success", "Product deleted successfully.");
        header("Location: products.php");
        exit;
    } catch (PDOException $e) {
        $error = "Could not delete product. " . $e->getMessage();
    }
}

// Handle Add/Edit Form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    $name = sanitize($_POST['name']);
    $brand_id = (int)$_POST['brand_id'];
    $category_id = (int)$_POST['category_id'];
    $sku = sanitize($_POST['sku']);
    $description = sanitize($_POST['description']);
    $ingredients = sanitize($_POST['ingredients']);
    $top_notes = sanitize($_POST['top_notes']);
    $middle_notes = sanitize($_POST['middle_notes']);
    $base_notes = sanitize($_POST['base_notes']);
    
    $price_30ml = !empty($_POST['price_30ml']) ? (float)$_POST['price_30ml'] : null;
    $price_50ml = !empty($_POST['price_50ml']) ? (float)$_POST['price_50ml'] : null;
    $price_100ml = !empty($_POST['price_100ml']) ? (float)$_POST['price_100ml'] : null;

    $discount_30ml = (float)$_POST['discount_30ml'];
    $discount_50ml = (float)$_POST['discount_50ml'];
    $discount_100ml = (float)$_POST['discount_100ml'];

    $stock_30ml = (int)$_POST['stock_30ml'];
    $stock_50ml = (int)$_POST['stock_50ml'];
    $stock_100ml = (int)$_POST['stock_100ml'];

    $image_url = sanitize($_POST['image_url']);
    $image_gallery = sanitize($_POST['image_gallery']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;
    $is_new_arrival = isset($_POST['is_new_arrival']) ? 1 : 0;
    $is_limited_edition = isset($_POST['is_limited_edition']) ? 1 : 0;

    if (empty($name) || empty($sku) || empty($description)) {
        $error = "Name, SKU and Description fields are required.";
    } else {
        if ($action === 'add') {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO `products` (
                        name, brand_id, category_id, sku, description, ingredients, top_notes, middle_notes, base_notes,
                        price_30ml, price_50ml, price_100ml, discount_30ml, discount_50ml, discount_100ml,
                        stock_30ml, stock_50ml, stock_100ml, image_url, image_gallery, is_featured, is_best_seller, is_new_arrival, is_limited_edition
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )
                ");
                $stmt->execute([
                    $name, $brand_id, $category_id, $sku, $description, $ingredients, $top_notes, $middle_notes, $base_notes,
                    $price_30ml, $price_50ml, $price_100ml, $discount_30ml, $discount_50ml, $discount_100ml,
                    $stock_30ml, $stock_50ml, $stock_100ml, $image_url, $image_gallery, $is_featured, $is_best_seller, $is_new_arrival, $is_limited_edition
                ]);

                setFlashMessage("success", "Product added successfully!");
                header("Location: products.php");
                exit;
            } catch (PDOException $e) {
                $error = "Failed to add product. " . $e->getMessage();
            }
        } else { // edit
            try {
                $stmt = $pdo->prepare("
                    UPDATE `products` SET 
                        name = ?, brand_id = ?, category_id = ?, sku = ?, description = ?, ingredients = ?, top_notes = ?, middle_notes = ?, base_notes = ?,
                        price_30ml = ?, price_50ml = ?, price_100ml = ?, discount_30ml = ?, discount_50ml = ?, discount_100ml = ?,
                        stock_30ml = ?, stock_50ml = ?, stock_100ml = ?, image_url = ?, image_gallery = ?, 
                        is_featured = ?, is_best_seller = ?, is_new_arrival = ?, is_limited_edition = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $name, $brand_id, $category_id, $sku, $description, $ingredients, $top_notes, $middle_notes, $base_notes,
                    $price_30ml, $price_50ml, $price_100ml, $discount_30ml, $discount_50ml, $discount_100ml,
                    $stock_30ml, $stock_50ml, $stock_100ml, $image_url, $image_gallery, 
                    $is_featured, $is_best_seller, $is_new_arrival, $is_limited_edition, $productId
                ]);

                setFlashMessage("success", "Product updated successfully!");
                header("Location: products.php");
                exit;
            } catch (PDOException $e) {
                $error = "Failed to update product. " . $e->getMessage();
            }
        }
    }
}

// Fetch categories & brands for selector menus
$categories = $pdo->query("SELECT * FROM `categories`")->fetchAll();
$brands = $pdo->query("SELECT * FROM `brands`")->fetchAll();
?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2 mb-4"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <!-- Listing Products view -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="font-heading text-white m-0">Manage Perfumes</h2>
            <a href="products.php?action=add" class="btn btn-admin-gold"><i class="bi bi-plus-circle me-1"></i>Add Perfume</a>
        </div>

        <div class="admin-card p-4">
            <div class="table-responsive">
                <table class="table table-luxury align-middle m-0 text-center">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Sku / Name</th>
                            <th>Brand / Category</th>
                            <th>Stock Levels (30/50/100ml)</th>
                            <th>Prices (30/50/100ml)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $stmt = $pdo->query("
                            SELECT p.*, b.name as brand_name, c.name as category_name 
                            FROM `products` p
                            LEFT JOIN `brands` b ON p.brand_id = b.id
                            LEFT JOIN `categories` c ON p.category_id = c.id
                            ORDER BY p.created_at DESC
                        ");
                        $prods = $stmt->fetchAll();
                        foreach ($prods as $p):
                        ?>
                            <tr>
                                <td>
                                    <?php 
                                    $imgSrc = $p['image_url'];
                                    if (!empty($imgSrc) && strpos($imgSrc, 'http') !== 0) {
                                        $imgSrc = '../' . $imgSrc;
                                    }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="rounded bg-black border border-secondary" style="width: 50px; height: 50px; object-fit: contain;">
                                </td>
                                <td class="text-start">
                                    <span class="small font-monospace text-warning d-block"><?php echo $p['sku']; ?></span>
                                    <strong class="text-white"><?php echo $p['name']; ?></strong>
                                </td>
                                <td>
                                    <span class="d-block text-white-50 small"><?php echo $p['brand_name'] ? $p['brand_name'] : 'AromaLuxe'; ?></span>
                                    <span class="text-muted small"><?php echo $p['category_name']; ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo $p['stock_30ml']; ?></span> /
                                    <span class="badge bg-secondary"><?php echo $p['stock_50ml']; ?></span> /
                                    <span class="badge bg-secondary"><?php echo $p['stock_100ml']; ?></span>
                                </td>
                                <td class="text-warning fw-bold small">
                                    <?php echo $settings['currency_symbol'] ?? '₹'; ?><?php echo $p['price_30ml'] ?? '-'; ?> /
                                    <?php echo $settings['currency_symbol'] ?? '₹'; ?><?php echo $p['price_50ml'] ?? '-'; ?> /
                                    <?php echo $settings['currency_symbol'] ?? '₹'; ?><?php echo $p['price_100ml'] ?? '-'; ?>
                                </td>
                                <td>
                                    <a href="products.php?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-warning py-1 px-2 me-1" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                    <a href="products.php?action=delete&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-danger py-1 px-2" onclick="return confirm('Delete this perfume?')" title="Delete"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <!-- Add/Edit view Form -->
        <?php 
        $title = "Add New Fragrance";
        $pData = [
            'name' => '', 'brand_id' => 0, 'category_id' => 0, 'sku' => '', 'description' => '', 'ingredients' => '',
            'top_notes' => '', 'middle_notes' => '', 'base_notes' => '',
            'price_30ml' => '', 'price_50ml' => '', 'price_100ml' => '',
            'discount_30ml' => 0, 'discount_50ml' => 0, 'discount_100ml' => 0,
            'stock_30ml' => 10, 'stock_50ml' => 10, 'stock_100ml' => 10,
            'image_url' => '', 'image_gallery' => '', 'is_featured' => 0, 'is_best_seller' => 0, 'is_new_arrival' => 0, 'is_limited_edition' => 0
        ];
        
        if ($action === 'edit' && $productId > 0) {
            $title = "Edit Fragrance Details";
            $stmt = $pdo->prepare("SELECT * FROM `products` WHERE id = ?");
            $stmt->execute([$productId]);
            $pData = $stmt->fetch();
        }
        ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="font-heading text-white m-0"><?php echo $title; ?></h2>
            <a href="products.php" class="btn btn-sm btn-outline-secondary text-white"><i class="bi bi-arrow-left me-1"></i>Back to List</a>
        </div>

        <div class="admin-card p-4 p-md-5">
            <form method="POST" action="">
                <!-- General Info -->
                <h5 class="font-heading text-warning mb-3">General Information</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-white-50 small">Perfume Name *</label>
                        <input type="text" name="name" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($pData['name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white-50 small">SKU Code *</label>
                        <input type="text" name="sku" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($pData['sku']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white-50 small">Brand / Perfume House</label>
                        <select name="brand_id" class="form-select bg-dark border-secondary text-white">
                            <?php foreach ($brands as $b): ?>
                                <option value="<?php echo $b['id']; ?>" <?php echo ($b['id'] == $pData['brand_id']) ? 'selected' : ''; ?>><?php echo $b['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white-50 small">Category</label>
                        <select name="category_id" class="form-select bg-dark border-secondary text-white">
                            <?php foreach ($categories as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($c['id'] == $pData['category_id']) ? 'selected' : ''; ?>><?php echo $c['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-white-50 small">Description *</label>
                        <textarea name="description" rows="4" class="form-control bg-transparent border-secondary text-white" required><?php echo htmlspecialchars($pData['description']); ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-white-50 small">Ingredients List</label>
                        <textarea name="ingredients" rows="2" class="form-control bg-transparent border-secondary text-white"><?php echo htmlspecialchars($pData['ingredients']); ?></textarea>
                    </div>
                </div>

                <!-- Accord profile notes -->
                <h5 class="font-heading text-warning mb-3">Fragrance Accords / Notes</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label text-white-50 small">Top Notes</label>
                        <input type="text" name="top_notes" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($pData['top_notes']); ?>" placeholder="e.g. Saffron, Nutmeg">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white-50 small">Middle / Heart Notes</label>
                        <input type="text" name="middle_notes" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($pData['middle_notes']); ?>" placeholder="e.g. Cambodian Oud, Rose">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-white-50 small">Base Notes</label>
                        <input type="text" name="base_notes" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($pData['base_notes']); ?>" placeholder="e.g. Amber, Musk">
                    </div>
                </div>

                <!-- Sizes & Pricing Grid -->
                <h5 class="font-heading text-warning mb-3">Sizes, Pricing & Inventory Stock</h5>
                <div class="row g-3 mb-4 text-center">
                    <!-- Column 30ml -->
                    <div class="col-md-4 border-end border-secondary">
                        <div class="text-white fw-bold mb-2">30ml Bottle Size</div>
                        <div class="mb-2 text-start">
                            <label class="form-label text-white-50 small">Price (INR)</label>
                            <input type="number" step="0.01" name="price_30ml" class="form-control bg-transparent border-secondary text-white" value="<?php echo $pData['price_30ml']; ?>">
                        </div>
                        <div class="mb-2 text-start">
                            <label class="form-label text-white-50 small">Discount (INR)</label>
                            <input type="number" step="0.01" name="discount_30ml" class="form-control bg-transparent border-secondary text-white" value="<?php echo $pData['discount_30ml']; ?>">
                        </div>
                        <div class="text-start">
                            <label class="form-label text-white-50 small">Refill Stock</label>
                            <input type="number" name="stock_30ml" class="form-control bg-transparent border-secondary text-white" value="<?php echo $pData['stock_30ml']; ?>">
                        </div>
                    </div>

                    <!-- Column 50ml -->
                    <div class="col-md-4 border-end border-secondary">
                        <div class="text-white fw-bold mb-2">50ml Bottle Size</div>
                        <div class="mb-2 text-start">
                            <label class="form-label text-white-50 small">Price (INR)</label>
                            <input type="number" step="0.01" name="price_50ml" class="form-control bg-transparent border-secondary text-white" value="<?php echo $pData['price_50ml']; ?>">
                        </div>
                        <div class="mb-2 text-start">
                            <label class="form-label text-white-50 small">Discount (INR)</label>
                            <input type="number" step="0.01" name="discount_50ml" class="form-control bg-transparent border-secondary text-white" value="<?php echo $pData['discount_50ml']; ?>">
                        </div>
                        <div class="text-start">
                            <label class="form-label text-white-50 small">Refill Stock</label>
                            <input type="number" name="stock_50ml" class="form-control bg-transparent border-secondary text-white" value="<?php echo $pData['stock_50ml']; ?>">
                        </div>
                    </div>

                    <!-- Column 100ml -->
                    <div class="col-md-4">
                        <div class="text-white fw-bold mb-2">100ml Bottle Size</div>
                        <div class="mb-2 text-start">
                            <label class="form-label text-white-50 small">Price (INR)</label>
                            <input type="number" step="0.01" name="price_100ml" class="form-control bg-transparent border-secondary text-white" value="<?php echo $pData['price_100ml']; ?>">
                        </div>
                        <div class="mb-2 text-start">
                            <label class="form-label text-white-50 small">Discount (INR)</label>
                            <input type="number" step="0.01" name="discount_100ml" class="form-control bg-transparent border-secondary text-white" value="<?php echo $pData['discount_100ml']; ?>">
                        </div>
                        <div class="text-start">
                            <label class="form-label text-white-50 small">Refill Stock</label>
                            <input type="number" name="stock_100ml" class="form-control bg-transparent border-secondary text-white" value="<?php echo $pData['stock_100ml']; ?>">
                        </div>
                    </div>
                </div>

                <!-- Media Asset links -->
                <h5 class="font-heading text-warning mb-3">Images Gallery Links</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-white-50 small">Primary Image URL</label>
                        <input type="url" name="image_url" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($pData['image_url']); ?>" placeholder="https://unsplash.com/photo-..." required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-white-50 small">Gallery Images (Comma separated URLs)</label>
                        <input type="text" name="image_gallery" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($pData['image_gallery']); ?>" placeholder="url1, url2, url3">
                    </div>
                </div>

                <!-- Spotlight attributes toggles -->
                <h5 class="font-heading text-warning mb-3">Catalog Spotlight Placements</h5>
                <div class="row g-3 mb-5 text-start">
                    <div class="col-6 col-sm-3">
                        <div class="form-check">
                            <input class="form-check-input bg-dark border-secondary" type="checkbox" name="is_featured" id="chkFeatured" value="1" <?php echo ($pData['is_featured'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label text-white-50 small" for="chkFeatured">Featured Product</label>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="form-check">
                            <input class="form-check-input bg-dark border-secondary" type="checkbox" name="is_best_seller" id="chkBest" value="1" <?php echo ($pData['is_best_seller'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label text-white-50 small" for="chkBest">Bestseller Badge</label>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="form-check">
                            <input class="form-check-input bg-dark border-secondary" type="checkbox" name="is_new_arrival" id="chkNew" value="1" <?php echo ($pData['is_new_arrival'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label text-white-50 small" for="chkNew">New Arrival Badge</label>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="form-check">
                            <input class="form-check-input bg-dark border-secondary" type="checkbox" name="is_limited_edition" id="chkLimit" value="1" <?php echo ($pData['is_limited_edition'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label text-white-50 small" for="chkLimit">Limited Edition</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-admin-gold py-2 px-5 font-heading">Save Product</button>
            </form>
        </div>
    <?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
