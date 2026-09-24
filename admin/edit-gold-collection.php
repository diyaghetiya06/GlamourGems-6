<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Edit Gold Collection';

$message = '';
$message_type = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;


/*======================================
        CHECK PRODUCT
======================================*/

if ($id <= 0) {
    header('Location: gold-collection.php');
    exit;
}

$product_query = mysqli_query(
    $conn,
    "SELECT *
     FROM collection_gold_products
     WHERE id = $id
     LIMIT 1"
);

$product = mysqli_fetch_assoc($product_query);

if (!$product) {
    header('Location: gold-collection.php');
    exit;
}


/*======================================
        UPDATE GOLD PRODUCT
======================================*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';

    if ($name === '' || $price === '') {

        $message = 'Please fill all required fields.';
        $message_type = 'error';

    } elseif (!is_numeric($price) || $price < 0) {

        $message = 'Please enter a valid price.';
        $message_type = 'error';

    } elseif (!in_array($status, ['active', 'inactive'], true)) {

        $message = 'Invalid status selected.';
        $message_type = 'error';

    } else {

        $old_image = $product['image'];
        $new_image = $old_image;
        $new_image_uploaded = false;

        /*======================================
                NEW IMAGE UPLOAD
        ======================================*/

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

                $message = 'There was an error uploading the image.';
                $message_type = 'error';

            } else {

                $image = $_FILES['image'];

                $allowed_extensions = [
                    'jpg',
                    'jpeg',
                    'png',
                    'webp'
                ];

                $extension = strtolower(
                    pathinfo($image['name'], PATHINFO_EXTENSION)
                );

                if (!in_array($extension, $allowed_extensions, true)) {

                    $message = 'Only JPG, JPEG, PNG and WEBP images are allowed.';
                    $message_type = 'error';

                } elseif ($image['size'] > 5 * 1024 * 1024) {

                    $message = 'Image size must be less than 5 MB.';
                    $message_type = 'error';

                } else {

                    $upload_dir = __DIR__ . '/../assets/images/gold/';

                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    $safe_name = preg_replace(
                        '/[^A-Za-z0-9_-]/',
                        '-',
                        pathinfo($image['name'], PATHINFO_FILENAME)
                    );

                    $new_image = $safe_name . '-' . time() . '.' . $extension;

                    $target_file = $upload_dir . $new_image;

                    if (move_uploaded_file($image['tmp_name'], $target_file)) {

                        $new_image_uploaded = true;

                    } else {

                        $message = 'Unable to upload the new image.';
                        $message_type = 'error';
                    }
                }
            }
        }


        /*======================================
                UPDATE DATABASE
        ======================================*/

        if ($message === '') {

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE collection_gold_products
                 SET name = ?,
                     price = ?,
                     image = ?,
                     display_order = ?,
                     status = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sdsisi",
                $name,
                $price,
                $new_image,
                $display_order,
                $status,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);


                /*======================================
                        DELETE OLD IMAGE
                ======================================*/

                if (
                    $new_image_uploaded &&
                    $old_image !== '' &&
                    $old_image !== $new_image
                ) {

                    $old_image_path =
                        __DIR__ .
                        '/../assets/images/gold/' .
                        $old_image;

                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }


                header('Location: gold-collection.php?success=updated');
                exit;

            } else {

                mysqli_stmt_close($stmt);

                /* Delete newly uploaded image if DB update fails */

                if (
                    $new_image_uploaded &&
                    isset($target_file) &&
                    file_exists($target_file)
                ) {
                    unlink($target_file);
                }

                $message = 'Unable to update Gold Collection product.';
                $message_type = 'error';
            }
        }
    }
}


/*======================================
        HEADER
======================================*/

include(__DIR__ . '/includes/header.php');

?>

<div class="admin-page">

    <!--======================================
            PAGE HEADER
    ======================================-->

    <div class="gg-page-header">

        <div>

            <h1 class="gg-page-title">
                Edit Gold Collection
            </h1>

            <p class="gg-page-subtitle">
                Update Gold Collection product details.
            </p>

        </div>


        <a
            href="gold-collection.php"
            class="btn-gg-gold"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Back to Gold Collection
        </a>

    </div>


    <!--======================================
            MESSAGE
    ======================================-->

    <?php if ($message !== ''): ?>

        <div
            class="gg-alert <?php echo ($message_type === 'error') ? 'gg-alert-error' : 'gg-alert-success'; ?>"
        >
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <!--======================================
            EDIT FORM
    ======================================-->

    <div class="gg-card">

        <div class="gg-card-header">

            <h2 class="gg-card-title">
                Product Details
            </h2>

        </div>


        <div class="gg-card-body">

            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <!--======================================
                        PRODUCT NAME
                ======================================-->

                <div class="form-group">

                    <label for="name">
                        Product Name
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        value="<?php echo htmlspecialchars($product['name']); ?>"
                        required
                    >

                </div>


                <!--======================================
                        PRICE
                ======================================-->

                <div class="form-group">

                    <label for="price">
                        Price
                        <span class="required">*</span>
                    </label>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        class="form-control"
                        min="0"
                        step="0.01"
                        value="<?php echo htmlspecialchars($product['price']); ?>"
                        required
                    >

                </div>


                <!--======================================
                        CURRENT IMAGE
                ======================================-->

                <div class="form-group">

                    <label>
                        Current Image
                    </label>

                    <div style="margin-bottom:15px;">

                        <img
                            src="../assets/images/gold/<?php echo htmlspecialchars($product['image']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            style="
                                width:120px;
                                height:120px;
                                object-fit:cover;
                                border-radius:12px;
                                border:1px solid rgba(200,161,101,0.35);
                            "
                        >

                    </div>

                </div>


                <!--======================================
                        CHANGE IMAGE
                ======================================-->

                <div class="form-group">

                    <label for="image">
                        Change Image
                    </label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small>
                        Leave empty to keep the current image.
                        Allowed: JPG, JPEG, PNG, WEBP | Maximum 5 MB
                    </small>

                </div>


                <!--======================================
                        DISPLAY ORDER
                ======================================-->

                <div class="form-group">

                    <label for="display_order">
                        Display Order
                    </label>

                    <input
                        type="number"
                        id="display_order"
                        name="display_order"
                        class="form-control"
                        min="1"
                        value="<?php echo (int)$product['display_order']; ?>"
                    >

                </div>


                <!--======================================
                        STATUS
                ======================================-->

                <div class="form-group">

                    <label for="status">
                        Status
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="form-control"
                    >

                        <option
                            value="active"
                            <?php echo ($product['status'] === 'active') ? 'selected' : ''; ?>
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            <?php echo ($product['status'] === 'inactive') ? 'selected' : ''; ?>
                        >
                            Inactive
                        </option>

                    </select>

                </div>


                <!--======================================
                        BUTTONS
                ======================================-->

                <div class="form-actions">

                    <a
                        href="gold-collection.php"
                        class="btn-gg-secondary"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn-gg-gold"
                    >
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Changes
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<?php

include(__DIR__ . '/includes/footer.php');

?>