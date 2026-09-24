<?php
require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Edit Product';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    set_flash('danger', 'Invalid product ID specified.');
    header('Location: ' . ADMIN_URL . 'products.php');
    exit;
}

// Fetch Product
$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$product) {
    set_flash('danger', 'Product not found in database.');
    header('Location: ' . ADMIN_URL . 'products.php');
    exit;
}

// Fetch Categories
$categories = [];
$cat_res = @mysqli_query($conn, "SELECT id, name FROM categories ORDER BY name ASC");
if ($cat_res) {
    while ($c = mysqli_fetch_assoc($cat_res)) {
        $categories[] = $c;
    }
}

$error = '';

/*======================================
        UPDATE PRODUCT
======================================*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = sanitize($_POST['name'] ?? '');
    $metal = sanitize($_POST['metal'] ?? '');
    $jewellery_type = sanitize($_POST['jewellery_type'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $sku = sanitize($_POST['sku'] ?? '');
    $price = floatval($_POST['price'] ?? 0);

    $sale_price = isset($_POST['sale_price']) && $_POST['sale_price'] !== ''
        ? floatval($_POST['sale_price'])
        : NULL;

    $stock = intval($_POST['stock'] ?? 0);

    $description = sanitize($_POST['description'] ?? '');
    $status = sanitize($_POST['status'] ?? 'active');

    /*======================================
        CREATE UNIQUE SLUG
======================================*/

$base_slug = create_slug($name);
$slug = $base_slug;

$slug_count = 1;

while (true) {

    $check_slug = mysqli_prepare(
        $conn,
        "SELECT id FROM products WHERE slug = ? AND id != ? LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $check_slug,
        "si",
        $slug,
        $id
    );

    mysqli_stmt_execute($check_slug);
    mysqli_stmt_store_result($check_slug);

    if (mysqli_stmt_num_rows($check_slug) === 0) {
        mysqli_stmt_close($check_slug);
        break;
    }

    mysqli_stmt_close($check_slug);

    $slug_count++;

    $slug = $base_slug . '-' . $slug_count;
}

    /*======================================
            VALIDATION
    ======================================*/

    if (
        empty($name) ||
        empty($metal) ||
        empty($jewellery_type) ||
        empty($sku) ||
        $category_id <= 0 ||
        $price <= 0
    ) {

        $error = 'Please fill in all required fields.';

    } else {

        /* Keep old image if no new image selected */

        $image_filename = $product['image'];

        /*======================================
                NEW IMAGE
        ======================================*/

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] === UPLOAD_ERR_OK
        ) {

            $file_tmp = $_FILES['image']['tmp_name'];
            $file_name = $_FILES['image']['name'];

            $ext = strtolower(
                pathinfo($file_name, PATHINFO_EXTENSION)
            );

            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed)) {

                $new_filename =
                    'prod_' .
                    time() .
                    '_' .
                    rand(1000, 9999) .
                    '.' .
                    $ext;

                if (
                    move_uploaded_file(
                        $file_tmp,
                        PRODUCT_UPLOAD_DIR . $new_filename
                    )
                ) {

                    $image_filename = $new_filename;

                } else {

                    $error = 'Failed to upload product image.';
                }

            } else {

                $error = 'Invalid image format. Allowed: JPG, JPEG, PNG, WEBP.';
            }
        }

        /*======================================
                DATABASE UPDATE
        ======================================*/

        if (empty($error)) {

            $sql = "
                UPDATE products
                SET
                    category_id = ?,
                    metal = ?,
                    jewellery_type = ?,
                    name = ?,
                    slug = ?,
                    sku = ?,
                    price = ?,
                    sale_price = ?,
                    stock = ?,
                    image = ?,
                    description = ?,
                    status = ?
                WHERE id = ?
            ";

            $stmt = mysqli_prepare($conn, $sql);

            if (!$stmt) {

                $error = 'Database prepare error: ' . mysqli_error($conn);

            } else {

                mysqli_stmt_bind_param(
                    $stmt,
                    "isssssddisssi",
                    $category_id,
                    $metal,
                    $jewellery_type,
                    $name,
                    $slug,
                    $sku,
                    $price,
                    $sale_price,
                    $stock,
                    $image_filename,
                    $description,
                    $status,
                    $id
                );

                if (mysqli_stmt_execute($stmt)) {

                    set_flash(
                        'success',
                        'Product "' .
                        htmlspecialchars($name) .
                        '" updated successfully.'
                    );

                    mysqli_stmt_close($stmt);

                    header(
                        'Location: ' .
                        ADMIN_URL .
                        'products.php'
                    );

                    exit;

                } else {

                    $error =
                        'Database update failed: ' .
                        mysqli_stmt_error($stmt);

                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}

include(__DIR__ . '/includes/header.php');
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="page-header-title mb-1">Edit Product</h2>
        <p class="page-header-sub mb-0">Update item #<?php echo $product['id']; ?> - <?php echo htmlspecialchars($product['name']); ?></p>
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
                    <h5 class="gg-card-title"><i class="fa-solid fa-pen-nib"></i> Basic Details</h5>
                </div>
                <div class="gg-card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? $product['name']); ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo (($_POST['category_id'] ?? $product['category_id']) == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Metal -->
<div class="col-12 col-md-6">
    <label for="metal" class="form-label">Metal <span class="text-danger">*</span></label>
    <select class="form-select" id="metal" name="metal" required>
        <option value="">-- Select Metal --</option>
        <option value="Gold" <?php echo (($_POST['metal'] ?? $product['metal']) === 'Gold') ? 'selected' : ''; ?>>Gold</option>
        <option value="Silver" <?php echo (($_POST['metal'] ?? $product['metal']) === 'Silver') ? 'selected' : ''; ?>>Silver</option>
        <option value="Diamond" <?php echo (($_POST['metal'] ?? $product['metal']) === 'Diamond') ? 'selected' : ''; ?>>Diamond</option>
        <option value="Rose Gold" <?php echo (($_POST['metal'] ?? $product['metal']) === 'Rose Gold') ? 'selected' : ''; ?>>Rose Gold</option>
    </select>
</div>

<!-- Jewellery Type -->
<div class="col-12 col-md-6">
    <label for="jewellery_type" class="form-label">Jewellery Type <span class="text-danger">*</span></label>
    <select class="form-select" id="jewellery_type" name="jewellery_type" required>
        <option value="">-- Select Jewellery Type --</option>
        <option value="Necklace" <?php echo (($_POST['jewellery_type'] ?? $product['jewellery_type']) === 'Necklace') ? 'selected' : ''; ?>>Necklace</option>
        <option value="Earring" <?php echo (($_POST['jewellery_type'] ?? $product['jewellery_type']) === 'Earring') ? 'selected' : ''; ?>>Earring</option>
        <option value="Ring" <?php echo (($_POST['jewellery_type'] ?? $product['jewellery_type']) === 'Ring') ? 'selected' : ''; ?>>Ring</option>
        <option value="Bracelet" <?php echo (($_POST['jewellery_type'] ?? $product['jewellery_type']) === 'Bracelet') ? 'selected' : ''; ?>>Bracelet</option>
        <option value="Bangle" <?php echo (($_POST['jewellery_type'] ?? $product['jewellery_type']) === 'Bangle') ? 'selected' : ''; ?>>Bangle</option>
        <option value="Pendant" <?php echo (($_POST['jewellery_type'] ?? $product['jewellery_type']) === 'Pendant') ? 'selected' : ''; ?>>Pendant</option>
        <option value="Chain" <?php echo (($_POST['jewellery_type'] ?? $product['jewellery_type']) === 'Chain') ? 'selected' : ''; ?>>Chain</option>
        <option value="Set" <?php echo (($_POST['jewellery_type'] ?? $product['jewellery_type']) === 'Set') ? 'selected' : ''; ?>>Set</option>
    </select>
</div>

                        <div class="col-12 col-md-6">
                            <label for="sku" class="form-label">SKU Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="sku" name="sku" required value="<?php echo htmlspecialchars($_POST['sku'] ?? $product['sku']); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Product Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($_POST['description'] ?? $product['description']); ?></textarea>
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
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required value="<?php echo htmlspecialchars($_POST['price'] ?? $product['price']); ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="sale_price" class="form-label">Sale Price (₹)</label>
                            <input type="number" step="0.01" class="form-control" id="sale_price" name="sale_price" value="<?php echo htmlspecialchars($_POST['sale_price'] ?? $product['sale_price']); ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="stock" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stock" name="stock" required value="<?php echo htmlspecialchars($_POST['stock'] ?? $product['stock']); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Options & Image Preview -->
        <div class="col-12 col-lg-4">
            <!-- Media Card -->
            <div class="gg-card">
                <div class="gg-card-header">
                    <h5 class="gg-card-title"><i class="fa-solid fa-image"></i> Product Image</h5>
                </div>
                <div class="gg-card-body text-center">
                    <div class="mb-3">
                        <?php
                        $curr_img = BASE_URL . 'uploads/products/' . $product['image'];
                        if (empty($product['image']) || !file_exists(PRODUCT_UPLOAD_DIR . $product['image'])) {
                            $curr_img = 'https://placehold.co/200x200/1e2430/d4af37?text=Jewelry';
                        }
                        ?>
                        <img src="<?php echo $curr_img; ?>" alt="Current Product Image" class="img-fluid rounded border border-gold mb-3" style="max-height: 180px; object-fit: cover;">
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <small class="text-muted fs-8 mt-1 d-block">Leave blank to keep existing image</small>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="gg-card">
                <div class="gg-card-header">
                    <h5 class="gg-card-title"><i class="fa-solid fa-sliders"></i> Options & Update</h5>
                </div>
                <div class="gg-card-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active" <?php echo ($product['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($product['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <hr class="border-secondary">

                    <button type="submit" class="btn btn-gg-gold w-100 py-2">
                        <i class="fa-solid fa-arrows-rotate me-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php include(__DIR__ . '/includes/footer.php'); ?>
