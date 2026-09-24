<?php
require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Product Management';

// Fetch Categories for Filter Dropdown
$categories = [];
$cat_res = @mysqli_query($conn, "SELECT id, name FROM categories ORDER BY name ASC");
if ($cat_res) {
    while ($c = mysqli_fetch_assoc($cat_res)) {
        $categories[] = $c;
    }
}

// Search & Filter parameters
$search = sanitize($_GET['search'] ?? '');
$category_id = intval($_GET['category_id'] ?? 0);

// Build SQL Query
$sql = "SELECT p.*, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";

if (!empty($search)) {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (p.name LIKE '%$safe_search%' OR p.sku LIKE '%$safe_search%')";
}

if ($category_id > 0) {
    $sql .= " AND p.category_id = $category_id";
}

$sql .= " ORDER BY p.id DESC";

$products = [];
$res = @mysqli_query($conn, $sql);
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $products[] = $row;
    }
}

include(__DIR__ . '/includes/header.php');
?>

<!-- Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="page-header-title mb-1">Product Inventory</h2>
        <p class="page-header-sub mb-0">Manage jewelry catalog items, pricing, inventory stock, and details.</p>
    </div>
    <div>
        <a href="<?php echo ADMIN_URL; ?>add-product.php" class="btn btn-gg-gold">
            <i class="fa-solid fa-plus me-2"></i> Add New Product
        </a>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="gg-card mb-4">
    <div class="gg-card-body p-3">
        <form action="" method="GET" class="row g-3 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by Product Name or SKU..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="category_id" class="form-select" onchange="this.form.submit()">
                    <option value="0">-- All Categories --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-gg-gold btn-sm w-100">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                <?php if (!empty($search) || $category_id > 0): ?>
                    <a href="<?php echo ADMIN_URL; ?>products.php" class="btn btn-gg-outline btn-sm" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="gg-card">
    <div class="gg-card-header">
        <h5 class="gg-card-title"><i class="fa-solid fa-gem"></i> Catalog Items (<?php echo count($products); ?>)</h5>
    </div>
    <div class="gg-card-body p-0">
        <div class="table-responsive">
            <table class="table table-gg align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">Image</th>
                        <th>Product Info</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $prod): ?>
                            <tr>
                                <td>
                                   <?php
                                    $img_src = '';

                                    if (!empty($prod['image'])) {

                                        // Product image from uploads folder
                                        if (file_exists(PRODUCT_UPLOAD_DIR . $prod['image'])) {
                                            $img_src = BASE_URL . 'uploads/products/' . $prod['image'];
                                        }

                                        // Product image from shop images folder
                                        elseif (file_exists(__DIR__ . '/../assets/images/shops/' . $prod['image'])) {
                                            $img_src = BASE_URL . 'assets/images/shops/' . $prod['image'];
                                        }
                                    }

                                    // If image is not found
                                    if (empty($img_src)) {
                                        $img_src = 'https://placehold.co/100x100/1e2430/d4af37?text=Jewelry';
                                    }
                                    ?>
                                    <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="table-img-thumb">
                                </td>
                                <td>
                                    <div class="fw-semibold text-light"><?php echo htmlspecialchars($prod['name']); ?></div>
                                   
                                </td>
                                <td><code class="text-gold"><?php echo htmlspecialchars($prod['sku']); ?></code></td>
                                <td><span class="badge bg-dark border border-secondary text-secondary"><?php echo htmlspecialchars($prod['category_name'] ?? 'Uncategorized'); ?></span></td>
                                <td>
                                    <?php if ($prod['sale_price'] && $prod['sale_price'] < $prod['price']): ?>
                                        <span class="fw-bold text-success"><?php echo format_price($prod['sale_price']); ?></span>
                                        <div class="text-muted fs-8 text-decoration-line-through"><?php echo format_price($prod['price']); ?></div>
                                    <?php else: ?>
                                        <span class="fw-bold"><?php echo format_price($prod['price']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($prod['stock'] > 5): ?>
                                        <span class="badge-gg badge-gg-success"><?php echo $prod['stock']; ?> In Stock</span>
                                    <?php elseif ($prod['stock'] > 0): ?>
                                        <span class="badge-gg badge-gg-warning"><?php echo $prod['stock']; ?> Low Stock</span>
                                    <?php else: ?>
                                        <span class="badge-gg badge-gg-danger">Out of Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($prod['status'] === 'active'): ?>
                                        <span class="badge-gg badge-gg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge-gg badge-gg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?php echo ADMIN_URL; ?>edit-product.php?id=<?php echo $prod['id']; ?>" class="btn-action" title="Edit Product">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="<?php echo ADMIN_URL; ?>delete-product.php?id=<?php echo $prod['id']; ?>" class="btn-action btn-action-danger" title="Delete Product" onclick="return confirm('Are you sure you want to delete this product?');">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-box-open fa-2x mb-2 d-block"></i>
                                No matching products found in the catalog.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include(__DIR__ . '/includes/footer.php'); ?>
