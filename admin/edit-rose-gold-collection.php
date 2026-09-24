<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Edit Rose Gold Collection';

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

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: rose-gold-collection.php');
    exit;
}


/*======================================
        FETCH PRODUCT
======================================*/

$product_query = mysqli_query(
    $conn,
    "SELECT *
     FROM collection_rose_gold_products
     WHERE id = $id
     LIMIT 1"
);

$product = mysqli_fetch_assoc($product_query);

if (!$product) {
    header('Location: rose-gold-collection.php');
    exit;
}


/*======================================
        UPDATE PRODUCT
======================================*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $display_order = (int) ($_POST['display_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';

    if ($name === '' || $price <= 0) {
        $error = 'Please enter valid product details.';
    } else {

        $image_name = $product['image'];

        /*======================================
                IMAGE UPLOAD
        ======================================*/

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] === UPLOAD_ERR_OK
        ) {

            $upload_dir = __DIR__ . '/../assets/images/rose-gold/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
                'webp'
            ];

            $original_name = $_FILES['image']['name'];

            $extension = strtolower(
                pathinfo($original_name, PATHINFO_EXTENSION)
            );

            if (!in_array($extension, $allowed_extensions, true)) {

                $error = 'Only JPG, JPEG, PNG and WEBP images are allowed.';

            } else {

                $new_image_name =
                    'rose_gold_' .
                    time() .
                    '_' .
                    uniqid() .
                    '.' .
                    $extension;

                $target_path =
                    $upload_dir .
                    $new_image_name;

                if (move_uploaded_file(
                    $_FILES['image']['tmp_name'],
                    $target_path
                )) {

                    /* Delete old image */

                    $old_image_path =
                        $upload_dir .
                        $product['image'];

                    if (
                        file_exists($old_image_path) &&
                        $product['image'] !== ''
                    ) {
                        unlink($old_image_path);
                    }

                    $image_name = $new_image_name;

                } else {

                    $error = 'Image upload failed.';
                }
            }
        }


        /*======================================
                UPDATE DATABASE
        ======================================*/

        if (!isset($error)) {

            $name_safe = mysqli_real_escape_string(
                $conn,
                $name
            );

            $image_safe = mysqli_real_escape_string(
                $conn,
                $image_name
            );

            $status_safe = mysqli_real_escape_string(
                $conn,
                $status
            );

            $update_query = mysqli_query(
                $conn,
                "UPDATE collection_rose_gold_products
                 SET
                    name = '$name_safe',
                    price = $price,
                    image = '$image_safe',
                    display_order = $display_order,
                    status = '$status_safe'
                 WHERE id = $id"
            );

            if ($update_query) {

                header(
                    'Location: rose-gold-collection.php?success=updated'
                );

                exit;

            } else {

                $error = 'Product update failed.';
            }
        }
    }
}

include(__DIR__ . '/includes/header.php');

?>

<div class="admin-page gold-collection-page">

    <!--======================================
                PAGE HEADER
    ======================================-->

    <div class="gg-page-header">

        <div>

            <h1 class="gg-page-title">
                Edit Rose Gold Product
            </h1>

            <p class="gg-page-subtitle">
                Update Rose Gold Collection product details.
            </p>

        </div>

        <a
            href="rose-gold-collection.php"
            class="btn-gg-secondary"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Back
        </a>

    </div>


    <!--======================================
                ERROR MESSAGE
    ======================================-->

    <?php if (isset($error)): ?>

        <div
            style="
                background:#3a1f1f;
                color:#ffb3b3;
                padding:14px 18px;
                border-radius:8px;
                margin-bottom:20px;
            "
        >
            <?php echo htmlspecialchars($error); ?>
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

                <!-- Product Name -->

                <div style="margin-bottom:20px;">

                    <label>
                        Product Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        value="<?php echo htmlspecialchars($product['name']); ?>"
                        required
                    >

                </div>


                <!-- Price -->

                <div style="margin-bottom:20px;">

                    <label>
                        Price
                    </label>

                    <input
                        type="number"
                        name="price"
                        class="form-control"
                        min="1"
                        step="0.01"
                        value="<?php echo htmlspecialchars($product['price']); ?>"
                        required
                    >

                </div>


                <!-- Display Order -->

                <div style="margin-bottom:20px;">

                    <label>
                        Display Order
                    </label>

                    <input
                        type="number"
                        name="display_order"
                        class="form-control"
                        min="0"
                        value="<?php echo (int) $product['display_order']; ?>"
                        required
                    >

                </div>


                <!-- Status -->

                <div style="margin-bottom:20px;">

                    <label>
                        Status
                    </label>

                    <select
                        name="status"
                        class="form-select"
                    >

                        <option
                            value="active"
                            <?php
                            echo ($product['status'] === 'active')
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            <?php
                            echo ($product['status'] === 'inactive')
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Inactive
                        </option>

                    </select>

                </div>


                <!-- Current Image -->

                <div style="margin-bottom:20px;">

                    <label>
                        Current Image
                    </label>

                    <div style="margin-top:10px;">

                        <img
                            src="../assets/images/rose-gold/<?php echo htmlspecialchars($product['image']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            style="
                                width:120px;
                                height:120px;
                                object-fit:cover;
                                border-radius:10px;
                                border:1px solid rgba(200,161,101,0.25);
                            "
                        >

                    </div>

                </div>


                <!-- New Image -->

                <div style="margin-bottom:25px;">

                    <label>
                        Change Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small>
                        Leave empty if you don't want to change the image.
                    </small>

                </div>


                <!-- Buttons -->

                <div
                    style="
                        display:flex;
                        gap:10px;
                        flex-wrap:wrap;
                    "
                >

                    <button
                        type="submit"
                        class="btn-gg-gold"
                    >
                        <i class="fa-solid fa-save"></i>
                        Update Product
                    </button>

                    <a
                        href="rose-gold-collection.php"
                        class="btn-gg-secondary"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

<?php

include(__DIR__ . '/includes/footer.php');

?>