<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Edit Silver Collection';

/*======================================
        INDIAN PRICE FORMAT
======================================*/

function indianPrice($price)
{
    $price = (float) $price;

    $number = number_format($price, 0, '.', '');

    $lastThree = substr($number, -3);

    $remaining = substr($number, 0, -3);

    if ($remaining !== '') {
        $remaining = preg_replace(
            '/\B(?=(\d{2})+(?!\d))/',
            ',',
            $remaining
        );

        return $remaining . ',' . $lastThree;
    }

    return $lastThree;
}

/*======================================
        GET PRODUCT ID
======================================*/

$product_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($product_id <= 0) {
    header("Location: silver-collection.php");
    exit;
}

/*======================================
        FETCH PRODUCT
======================================*/

$product_query = mysqli_query(
    $conn,
    "SELECT *
     FROM collection_silver_products
     WHERE id = $product_id
     LIMIT 1"
);

if (!$product_query || mysqli_num_rows($product_query) === 0) {
    header("Location: silver-collection.php");
    exit;
}

$product = mysqli_fetch_assoc($product_query);

/*======================================
        UPDATE PRODUCT
======================================*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $display_order = (int) ($_POST['display_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';

    $errors = [];

    /* Validate Name */

    if ($name === '') {
        $errors[] = 'Product name is required.';
    }

    /* Validate Price */

    if ($price === '' || !is_numeric($price) || (float) $price < 0) {
        $errors[] = 'Please enter a valid price.';
    }

    /* Validate Status */

    if (!in_array($status, ['active', 'inactive'], true)) {
        $status = 'active';
    }

    /*======================================
            IMAGE VARIABLES
    ======================================*/

    $new_image_name = $product['image'];
    $old_image_name = $product['image'];

    /*======================================
            NEW IMAGE UPLOAD
    ======================================*/

    if (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

            $errors[] = 'There was an error uploading the image.';

        } else {

            $image = $_FILES['image'];

            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
                'webp'
            ];

            $file_extension = strtolower(
                pathinfo($image['name'], PATHINFO_EXTENSION)
            );

            $max_size = 5 * 1024 * 1024;

            if (!in_array($file_extension, $allowed_extensions, true)) {

                $errors[] =
                    'Only JPG, JPEG, PNG and WEBP images are allowed.';

            } elseif ($image['size'] > $max_size) {

                $errors[] =
                    'Image size must be less than 5MB.';

            } else {

                $upload_directory =
                    __DIR__ . '/../assets/images/silver/';

                if (!is_dir($upload_directory)) {
                    mkdir($upload_directory, 0777, true);
                }

                $new_image_name =
                    'silver_' .
                    time() .
                    '_' .
                    uniqid() .
                    '.' .
                    $file_extension;

                $upload_path =
                    $upload_directory . $new_image_name;

                if (
                    !move_uploaded_file(
                        $image['tmp_name'],
                        $upload_path
                    )
                ) {

                    $errors[] =
                        'Failed to upload new image.';

                    $new_image_name = $old_image_name;
                }
            }
        }
    }

    /*======================================
            UPDATE DATABASE
    ======================================*/

    if (empty($errors)) {

        $price = (float) $price;

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE collection_silver_products
             SET
                name = ?,
                price = ?,
                image = ?,
                display_order = ?,
                status = ?
             WHERE id = ?"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "sdsisi",
                $name,
                $price,
                $new_image_name,
                $display_order,
                $status,
                $product_id
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                /*======================================
                        DELETE OLD IMAGE
                ======================================*/

                if (
                    $new_image_name !== $old_image_name &&
                    $old_image_name !== ''
                ) {

                    $old_image_path =
                        __DIR__ .
                        '/../assets/images/silver/' .
                        $old_image_name;

                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }

                header("Location: silver-collection.php");
                exit;

            } else {

                mysqli_stmt_close($stmt);

                /* Delete newly uploaded image if DB update fails */

                if (
                    $new_image_name !== $old_image_name
                ) {

                    $new_image_path =
                        __DIR__ .
                        '/../assets/images/silver/' .
                        $new_image_name;

                    if (file_exists($new_image_path)) {
                        unlink($new_image_path);
                    }
                }

                $errors[] =
                    'Failed to update Silver Collection product.';
            }

        } else {

            if (
                $new_image_name !== $old_image_name
            ) {

                $new_image_path =
                    __DIR__ .
                    '/../assets/images/silver/' .
                    $new_image_name;

                if (file_exists($new_image_path)) {
                    unlink($new_image_path);
                }
            }

            $errors[] =
                'Database error. Please try again.';
        }
    }

    /*======================================
            UPDATE FORM VALUES
    ======================================*/

    if (!empty($errors)) {

        $product['name'] = $name;
        $product['price'] = $price;
        $product['image'] = $new_image_name;
        $product['display_order'] = $display_order;
        $product['status'] = $status;
    }
}

include(__DIR__ . '/includes/header.php');

?>

<div class="admin-page gold-collection-page">

    <!--======================================
                PAGE HEADER
    ======================================-->

    <div class="admin-page-header">

        <div>

            <h1>Edit Silver Product</h1>

            <p>
                Update Silver Collection product details.
            </p>

        </div>

        <a
            href="silver-collection.php"
            class="btn-gg-outline"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Back to Silver Collection
        </a>

    </div>


    <!--======================================
                ERROR MESSAGES
    ======================================-->

    <?php if (!empty($errors)): ?>

        <div class="gg-alert gg-alert-danger">

            <i class="fa-solid fa-circle-exclamation"></i>

            <div>

                <?php foreach ($errors as $error): ?>

                    <div>
                        <?php echo htmlspecialchars($error); ?>
                    </div>

                <?php endforeach; ?>

            </div>

        </div>

    <?php endif; ?>


    <!--======================================
                EDIT PRODUCT FORM
    ======================================-->

    <div class="gg-card">

        <div class="gg-card-header">

            <h2 class="gg-card-title">

                <i class="fa-solid fa-pen-to-square"></i>

                Silver Product Details

            </h2>

        </div>


        <div class="gg-card-body">

            <form
                method="POST"
                enctype="multipart/form-data"
                class="admin-form"
            >

                <!--======================================
                        PRODUCT NAME
                ======================================-->

                <div class="form-group">

                    <label for="name">
                        Product Name
                        <span>*</span>
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
                        <span>*</span>
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

                    <div style="margin-top: 10px;">

                        <img
                            src="../assets/images/silver/<?php echo htmlspecialchars($product['image']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            style="
                                width: 140px;
                                height: 140px;
                                object-fit: cover;
                                border-radius: 12px;
                                border: 1px solid rgba(255,255,255,0.1);
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
                        Allowed: JPG, JPEG, PNG, WEBP | Maximum 5MB
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
                        min="0"
                        value="<?php echo htmlspecialchars($product['display_order']); ?>"
                    >

                    <small>
                        Lower number will appear first.
                    </small>

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
                        FORM ACTIONS
                ======================================-->

                <div class="form-actions">

                    <a
                        href="silver-collection.php"
                        class="btn-gg-outline"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn-gg-gold"
                    >
                        <i class="fa-solid fa-floppy-disk"></i>
                        Update Silver Product
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php include(__DIR__ . '/includes/footer.php'); ?>