<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Edit Diamond Collection';


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
            ERROR VARIABLE
======================================*/

$error = '';


/*======================================
            GET PRODUCT ID
======================================*/

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {

    header('Location: diamond-collection.php');
    exit;
}


/*======================================
            FETCH PRODUCT
======================================*/

$product_query = mysqli_query(
    $conn,
    "SELECT *
     FROM collection_diamond_products
     WHERE id = $id
     LIMIT 1"
);

$product = mysqli_fetch_assoc($product_query);

if (!$product) {

    header('Location: diamond-collection.php');
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


    /*======================================
            VALIDATION
    ======================================*/

    if ($name === '') {

        $error = 'Please enter product name.';

    } elseif ($price <= 0) {

        $error = 'Please enter a valid price.';

    } elseif (!in_array($status, ['active', 'inactive'], true)) {

        $error = 'Invalid status selected.';

    }


    /*======================================
            CURRENT IMAGE
    ======================================*/

    $image_name = $product['image'];


    /*======================================
            NEW IMAGE UPLOAD
    ======================================*/

    if (
        $error === '' &&
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

            $error = 'Image upload failed.';

        } else {

            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
                'webp'
            ];

            $extension = strtolower(
                pathinfo(
                    $_FILES['image']['name'],
                    PATHINFO_EXTENSION
                )
            );


            if (!in_array($extension, $allowed_extensions, true)) {

                $error =
                    'Only JPG, JPEG, PNG and WEBP images are allowed.';

            } else {

                $upload_dir =
                    __DIR__ .
                    '/../assets/images/diamond/';


                if (!is_dir($upload_dir)) {

                    mkdir(
                        $upload_dir,
                        0777,
                        true
                    );
                }


                $new_image_name =
                    'diamond_' .
                    time() .
                    '_' .
                    uniqid() .
                    '.' .
                    $extension;


                $target_path =
                    $upload_dir .
                    $new_image_name;


                if (
                    move_uploaded_file(
                        $_FILES['image']['tmp_name'],
                        $target_path
                    )
                ) {

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

                    $error =
                        'Image upload failed. Please try again.';
                }
            }
        }
    }


    /*======================================
            UPDATE DATABASE
    ======================================*/

    if ($error === '') {

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
            "UPDATE collection_diamond_products
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
                'Location: diamond-collection.php?success=updated'
            );

            exit;

        } else {

            $error =
                'Product update failed. Please try again.';
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
                Edit Diamond Product
            </h1>

            <p class="gg-page-subtitle">
                Update Diamond Collection product details.
            </p>

        </div>


        <a
            href="diamond-collection.php"
            class="btn-gg-secondary"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Back
        </a>

    </div>


    <!--======================================
                ERROR MESSAGE
    ======================================-->

    <?php if ($error !== ''): ?>

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
                Diamond Product Details
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
                            echo (
                                $product['status'] === 'active'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            <?php
                            echo (
                                $product['status'] === 'inactive'
                            )
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
                            src="../assets/images/diamond/<?php echo htmlspecialchars($product['image']); ?>"
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


                <!-- Change Image -->

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
                        href="diamond-collection.php"
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