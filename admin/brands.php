<?php
/**
 * Admin Panel Brand Management
 */
require_once __DIR__ . '/header.php';

$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$brandId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$success = '';
$error = '';

// Handle Delete
if ($action === 'delete' && $brandId > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM `brands` WHERE id = ?");
        $stmt->execute([$brandId]);
        setFlashMessage("success", "Brand deleted successfully.");
        header("Location: brands.php");
        exit;
    } catch (PDOException $e) {
        $error = "Could not delete brand. " . $e->getMessage();
    }
}

// Handle Add/Edit Form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    $name = sanitize($_POST['name']);
    $slug = sanitize($_POST['slug']);
    $description = sanitize($_POST['description']);

    if (empty($name) || empty($slug)) {
        $error = "Brand Name and Slug are required fields.";
    } else {
        if ($action === 'add') {
            try {
                $stmt = $pdo->prepare("INSERT INTO `brands` (name, slug, description) VALUES (?, ?, ?)");
                $stmt->execute([$name, $slug, $description]);
                setFlashMessage("success", "Brand added successfully!");
                header("Location: brands.php");
                exit;
            } catch (PDOException $e) {
                $error = "Failed to add brand. " . $e->getMessage();
            }
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE `brands` SET name = ?, slug = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $description, $brandId]);
                setFlashMessage("success", "Brand updated successfully!");
                header("Location: brands.php");
                exit;
            } catch (PDOException $e) {
                $error = "Failed to update brand. " . $e->getMessage();
            }
        }
    }
}
?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2 mb-4"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="font-heading text-white m-0">Manage Perfume Houses (Brands)</h2>
            <a href="brands.php?action=add" class="btn btn-admin-gold"><i class="bi bi-plus-circle me-1"></i>Add Brand</a>
        </div>

        <div class="admin-card p-4">
            <div class="table-responsive">
                <table class="table table-luxury align-middle m-0 text-center">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>House Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $brandsList = $pdo->query("SELECT * FROM `brands` ORDER BY id ASC")->fetchAll();
                        foreach ($brandsList as $b):
                        ?>
                            <tr>
                                <td><?php echo $b['id']; ?></td>
                                <td><strong class="text-white"><?php echo $b['name']; ?></strong></td>
                                <td class="font-monospace text-warning small"><?php echo $b['slug']; ?></td>
                                <td class="text-muted text-start small"><?php echo $b['description']; ?></td>
                                <td>
                                    <a href="brands.php?action=edit&id=<?php echo $b['id']; ?>" class="btn btn-sm btn-outline-warning py-1 px-2 me-1"><i class="bi bi-pencil-square"></i></a>
                                    <a href="brands.php?action=delete&id=<?php echo $b['id']; ?>" class="btn btn-sm btn-outline-danger py-1 px-2" onclick="return confirm('Delete this brand house?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <?php 
        $title = "Add Perfume House";
        $bData = ['name' => '', 'slug' => '', 'description' => ''];
        if ($action === 'edit' && $brandId > 0) {
            $title = "Edit Perfume House details";
            $stmt = $pdo->prepare("SELECT * FROM `brands` WHERE id = ?");
            $stmt->execute([$brandId]);
            $bData = $stmt->fetch();
        }
        ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="font-heading text-white m-0"><?php echo $title; ?></h2>
            <a href="brands.php" class="btn btn-sm btn-outline-secondary text-white"><i class="bi bi-arrow-left me-1"></i>Back to List</a>
        </div>

        <div class="admin-card p-4 p-md-5">
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label text-white-50 small">Brand Name *</label>
                    <input type="text" name="name" class="form-control bg-transparent border-secondary text-white" value="<?php echo htmlspecialchars($bData['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-white-50 small">Brand Slug *</label>
                    <input type="text" name="slug" class="form-control bg-transparent border-secondary text-white font-monospace" value="<?php echo htmlspecialchars($bData['slug']); ?>" required>
                </div>
                <div class="mb-4">
                    <label class="form-label text-white-50 small">Description / History</label>
                    <textarea name="description" rows="3" class="form-control bg-transparent border-secondary text-white"><?php echo htmlspecialchars($bData['description']); ?></textarea>
                </div>
                <button type="submit" class="btn btn-admin-gold py-2 px-5 font-heading">Save Brand</button>
            </form>
        </div>
    <?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
