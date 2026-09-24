<?php
require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Add New Product';

// Fetch Categories
$categories = [];
$cat_res = @mysqli_query($conn, "SELECT id, name FROM categories ORDER BY name ASC");
if ($cat_res) {
    while ($c = mysqli_fetch_assoc($cat_res)) {
        $categories[] = $c;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $metal = sanitize($_POST['metal'] ?? '');
    $jewellery_type = sanitize($_POST['jewellery_type'] ?? '');
    $sku = sanitize($_POST['sku'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $sale_price = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : NULL;
    $stock = intval($_POST['stock'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $status = sanitize($_POST['status'] ?? 'active');
    $slug = create_slug($name);

    if (empty($name) || empty($sku) || empty($metal) || empty($jewellery_type) || $category_id <= 0 || $price <= 0) {
        $error = 'Please fill in all required fields (Product Name, Category, SKU, and valid Price).';
    } else {
        // Image Upload Handling
        $image_filename = 'product_default.jpg';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_name = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed)) {
                $image_filename = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                move_uploaded_file($file_tmp, PRODUCT_UPLOAD_DIR . $image_filename);
            } else {
                $error = 'Invalid image format. Allowed formats: JPG, PNG, WEBP.';
            }
        }

        if (empty($error)) {
            $stmt = mysqli_prepare($conn, "INSERT INTO products (category_id, metal, jewellery_type, name, slug, sku, price, sale_price, stock, image, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
               mysqli_stmt_bind_param($stmt, "isssssddisss", $category_id, $metal, $jewellery_type, $name, $slug, $sku, $price, $sale_price, $stock, $image_filename, $description, $status);
                if (mysqli_stmt_execute($stmt)) {
                    set_flash('success', 'Product "' . htmlspecialchars($name) . '" created successfully.');
                    header('Location: ' . ADMIN_URL . 'products.php');
                    exit;
                } else {
                    $error = 'Database error: ' . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            } else {
                $error = 'Failed to prepare database statement: ' . mysqli_error($conn);
            }
        }
    }
}

include(__DIR__ . '/includes/header.php');
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="page-header-title mb-1">Add New Product</h2>
        <p class="page-header-sub mb-0">Add a new luxury jewelry piece to the online store catalog.</p>
    </div>
    <div>
        <a href="<?php echo ADMIN_URL; ?>products.php" class="btn btn-gg-outline">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Products
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
    <div class="row g-4">
        <!-- Main Form Left -->
        <div class="col-12 col-lg-8">
            <div class="gg-card">
                <div class="gg-card-header">
                    <h5 class="gg-card-title"><i class="fa-solid fa-pen-nib"></i> Basic Information</h5>
                </div>
                <div class="gg-card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Royal Solitaire Diamond Ring" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo (($_POST['category_id'] ?? 0) == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="sku" class="form-label">SKU Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="sku" name="sku" placeholder="e.g. GG-RNG-007" required value="<?php echo htmlspecialchars($_POST['sku'] ?? 'GG-' . rand(100, 999)); ?>">
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">

    <div class="col-12 col-md-6">
        <label for="metal" class="form-label">
            Metal <span class="text-danger">*</span>
        </label>

        <select class="form-select" id="metal" name="metal" required>
            <option value="">-- Select Metal --</option>
            <option value="Gold">Gold</option>
            <option value="Diamond">Diamond</option>
            <option value="Silver">Silver</option>
            <option value="Rose Gold">Rose Gold</option>
        </select>
    </div>

    <div class="col-12 col-md-6">
        <label for="jewellery_type" class="form-label">
            Jewellery Type <span class="text-danger">*</span>
        </label>

        <select class="form-select" id="jewellery_type" name="jewellery_type" required>
            <option value="">-- Select Jewellery Type --</option>
            <option value="Bangles">Bangles</option>
            <option value="Necklaces">Necklaces</option>
            <option value="Earring">Earring</option>
            <option value="Brouch">Brouch</option>
            <option value="Bracelet">Bracelet</option>
            <option value="Pendant">Pendant</option>
            <option value="Ring">Ring</option>
        </select>
    </div>

</div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Product Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5" placeholder="Detailed craftsmanship notes, carat weight, metal type..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Pricing & Inventory -->
            <div class="gg-card">
                <div class="gg-card-header">
                    <h5 class="gg-card-title"><i class="fa-solid fa-coins"></i> Pricing & Inventory</h5>
                </div>
                <div class="gg-card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label for="price" class="form-label">Regular Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" placeholder="0.00" required value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="sale_price" class="form-label">Sale / Discounted Price (₹)</label>
                            <input type="number" step="0.01" class="form-control" id="sale_price" name="sale_price" placeholder="Optional" value="<?php echo htmlspecialchars($_POST['sale_price'] ?? ''); ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="stock" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stock" name="stock" placeholder="0" required value="<?php echo htmlspecialchars($_POST['stock'] ?? '10'); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Options & Image Upload -->
        <div class="col-12 col-lg-4">
            <!-- Media Upload Card -->
            <div class="gg-card">
                <div class="gg-card-header">
                    <h5 class="gg-card-title"><i class="fa-solid fa-image"></i> Product Image</h5>
                </div>
                <div class="gg-card-body text-center">
                    <div class="mb-3">
                        <div class="border border-secondary border-dashed rounded p-3 text-center bg-dark bg-opacity-50">
                            <i class="fa-solid fa-cloud-arrow-up fa-2x text-gold mb-2"></i>
                            <p class="fs-8 text-muted mb-2">Upload product photo (JPG, PNG, WEBP)</p>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Card -->
            <div class="gg-card">
                <div class="gg-card-header">
                    <h5 class="gg-card-title"><i class="fa-solid fa-sliders"></i> Status & Options</h5>
                </div>
                <div class="gg-card-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Product Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active" selected>Active (Visible in Store)</option>
                            <option value="inactive">Inactive (Hidden)</option>
                        </select>
                    </div>

                    <hr class="border-secondary">

                    <button type="submit" class="btn btn-gg-gold w-100 py-2">
                        <i class="fa-solid fa-check me-1"></i> Save & Publish Product
                    </button>
                </div>
            </div>
        </div>

    </div>
</form>

<?php include(__DIR__ . '/includes/footer.php'); ?>
