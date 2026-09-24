<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Add Diamond Collection Product';


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
        ADD DIAMOND PRODUCT
======================================*/
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $display_order = (int) ($_POST['display_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';

    $error = '';


    /*======================================
            BASIC VALIDATION
    ======================================*/

    if ($name === '') {

        $error = 'Please enter product name.';

    } elseif ($price <= 0) {

        $error = 'Please enter a valid price.';

    } elseif (!in_array($status, ['active', 'inactive'], true)) {

        $error = 'Invalid status selected.';

    } elseif (
        !isset($_FILES['image']) ||
        $_FILES['image']['error'] !== UPLOAD_ERR_OK
    ) {

        $error = 'Please select a product image.';
    }


    /*======================================
            IMAGE UPLOAD
    ======================================*/

    if ($error === '') {

        $allowed_extensions = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];

        $original_name = $_FILES['image']['name'];

        $extension = strtolower(
            pathinfo(
                $original_name,
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
                !move_uploaded_file(
                    $_FILES['image']['tmp_name'],
                    $target_path
                )
            ) {

                $error =
                    'Image upload failed. Please try again.';
            }
        }
    }


    /*======================================
            INSERT INTO DATABASE
    ======================================*/

    if ($error === '') {

        $name_safe = mysqli_real_escape_string(
            $conn,
            $name
        );

        $image_safe = mysqli_real_escape_string(
            $conn,
            $new_image_name
        );

        $status_safe = mysqli_real_escape_string(
            $conn,
            $status
        );


        $insert_query = mysqli_query(
            $conn,
            "INSERT INTO collection_diamond_products
            (
                name,
                price,
                image,
                display_order,
                status
            )
            VALUES
            (
                '$name_safe',
                $price,
                '$image_safe',
                $display_order,
                '$status_safe'
            )"
        );


        if ($insert_query) {

            header(
                'Location: diamond-collection.php?success=added'
            );

            exit;

        } else {

            if (
                isset($new_image_name) &&
                file_exists($target_path)
            ) {

                unlink($target_path);
            }

            $error =
                'Product could not be added. Please try again.';
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
                Add Diamond Product
            </h1>

            <p class="gg-page-subtitle">
                Add a new product to the Diamond Collection.
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
                ADD FORM
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
                        placeholder="Enter product name"
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
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
                        placeholder="Enter price"
                        min="1"
                        step="0.01"
                        value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>"
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
                        value="<?php echo htmlspecialchars($_POST['display_order'] ?? '0'); ?>"
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
                                ($_POST['status'] ?? 'active')
                                === 'active'
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
                                ($_POST['status'] ?? '')
                                === 'inactive'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Inactive
                        </option>

                    </select>

                </div>


                <!-- Product Image -->

                <div style="margin-bottom:25px;">

                    <label>
                        Product Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    >

                    <small>
                        Allowed formats: JPG, JPEG, PNG, WEBP
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
                        <i class="fa-solid fa-plus"></i>
                        Add Product
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