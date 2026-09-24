<?php
require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Fetch product to unlink image
    $stmt = mysqli_prepare($conn, "SELECT image FROM products WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($prod = mysqli_fetch_assoc($res)) {
        if (!empty($prod['image']) && $prod['image'] !== 'product_default.jpg' && file_exists(PRODUCT_UPLOAD_DIR . $prod['image'])) {
            @unlink(PRODUCT_UPLOAD_DIR . $prod['image']);
        }
    }
    mysqli_stmt_close($stmt);

    // Delete record
    $del_stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
    mysqli_stmt_bind_param($del_stmt, "i", $id);
    if (mysqli_stmt_execute($del_stmt)) {
        set_flash('success', 'Product has been permanently deleted.');
    } else {
        set_flash('danger', 'Failed to delete product: ' . mysqli_error($conn));
    }
    mysqli_stmt_close($del_stmt);
} else {
    set_flash('danger', 'Invalid product ID.');
}

header('Location: ' . ADMIN_URL . 'products.php');
exit;
?>
