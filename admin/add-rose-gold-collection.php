<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Add Rose Gold Collection';

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
        ADD ROSE GOLD PRODUCT
======================================*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $display_order = (int) ($_POST['display_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';

    $errors = [];


    /*======================================
            VALIDATE NAME
    ======================================*/

    if ($name === '') {
        $errors[] = 'Product name is required.';
    }


    /*======================================
            VALIDATE PRICE
    ======================================*/

    if (
        $price === '' ||
        !is_numeric($price) ||
        (float) $price < 0
    ) {
        $errors[] = 'Please enter a valid price.';
    }


    /*======================================
            VALIDATE STATUS
    ======================================*/

    if (!in_array($status, ['active', 'inactive'], true)) {
        $status = 'active';
    }


    /*======================================
            VALIDATE IMAGE
    ======================================*/

    if (
        !isset($_FILES['image']) ||
        $_FILES['image']['error'] !== UPLOAD_ERR_OK
    ) {
        $errors[] = 'Product image is required.';
    }


    /*======================================
            IMAGE UPLOAD
    ======================================*/

    $image_name = '';

    if (empty($errors)) {

        $image = $_FILES['image'];

        $allowed_extensions = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];

        $file_extension = strtolower(
            pathinfo(
                $image['name'],
                PATHINFO_EXTENSION
            )
        );

        $max_size = 5 * 1024 * 1024;


        if (
            !in_array(
                $file_extension,
                $allowed_extensions,
                true
            )
        ) {

            $errors[] =
                'Only JPG, JPEG, PNG and WEBP images are allowed.';

        } elseif ($image['size'] > $max_size) {

            $errors[] =
                'Image size must be less than 5MB.';

        } else {

            $upload_directory =
                __DIR__ .
                '/../assets/images/rose-gold/';


            if (!is_dir($upload_directory)) {

                mkdir(
                    $upload_directory,
                    0777,
                    true
                );
            }


            $image_name =
                'rose_gold_' .
                time() .
                '_' .
                uniqid() .
                '.' .
                $file_extension;


            $upload_path =
                $upload_directory .
                $image_name;


            if (
                !move_uploaded_file(
                    $image['tmp_name'],
                    $upload_path
                )
            ) {

                $errors[] =
                    'Failed to upload image.';

                $image_name = '';
            }
        }
    }


    /*======================================
            INSERT PRODUCT
    ======================================*/

    if (empty($errors)) {

        $price = (float) $price;


        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO collection_rose_gold_products
            (
                name,
                price,
                image,
                display_order,
                status
            )
            VALUES (?, ?, ?, ?, ?)"
        );


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "sdsis",
                $name,
                $price,
                $image_name,
                $display_order,
                $status
            );


            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header(
                    "Location: rose-gold-collection.php?success=added"
                );

                exit;

            } else {

                mysqli_stmt_close($stmt);


                /* Delete uploaded image if insert fails */

                if ($image_name !== '') {

                    $uploaded_file =
                        __DIR__ .
                        '/../assets/images/rose-gold/' .
                        $image_name;


                    if (file_exists($uploaded_file)) {
                        unlink($uploaded_file);
                    }
                }


                $errors[] =
                    'Failed to add Rose Gold Collection product.';
            }

        } else {

            if ($image_name !== '') {

                $uploaded_file =
                    __DIR__ .
                    '/../assets/images/rose-gold/' .
                    $image_name;


                if (file_exists($uploaded_file)) {
                    unlink($uploaded_file);
                }
            }


            $errors[] =
                'Database error. Please try again.';
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
                Add Rose Gold Product
            </h1>

            <p class="gg-page-subtitle">
                Add a new product to the Rose Gold Collection.
            </p>

        </div>


        <a
            href="rose-gold-collection.php"
            class="btn-gg-outline"
        >
            <i class="fa-solid fa-arrow-left"></i>
            Back to Rose Gold
        </a>

    </div>


    <!--======================================
                ERROR MESSAGE
    ======================================-->

    <?php if (!empty($errors)): ?>

        <div
            class="gg-alert"
            style="
                margin-bottom:20px;
                color:#ff8d8d;
                background:rgba(239,98,98,0.08);
                border:1px solid rgba(239,98,98,0.25);
            "
        >

            <i class="fa-solid fa-circle-exclamation"></i>

            <?php foreach ($errors as $error): ?>

                <div>
                    <?php echo htmlspecialchars($error); ?>
                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>


    <!--======================================
                PRODUCT FORM
    ======================================-->

    <div class="gg-card">

        <div class="gg-card-header">

            <h2 class="gg-card-title">

                <i class="fa-solid fa-ring"></i>

                Rose Gold Product Details

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

                <div style="margin-bottom:20px;">

                    <label
                        class="form-label"
                        for="name"
                    >
                        Product Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        placeholder="Enter Rose Gold product name"
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                        required
                    >

                </div>


                <!--======================================
                        PRICE
                ======================================-->

                <div style="margin-bottom:20px;">

                    <label
                        class="form-label"
                        for="price"
                    >
                        Price
                    </label>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        class="form-control"
                        placeholder="Enter price"
                        min="0"
                        step="0.01"
                        value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>"
                        required
                    >

                </div>


                <!--======================================
                        IMAGE
                ======================================-->

                <div style="margin-bottom:20px;">

                    <label
                        class="form-label"
                        for="image"
                    >
                        Product Image
                    </label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    >

                    <small
                        style="
                            display:block;
                            margin-top:7px;
                            color:#8f9b96;
                        "
                    >
                        JPG, JPEG, PNG or WEBP | Maximum 5MB
                    </small>

                </div>


                <!--======================================
                        DISPLAY ORDER
                ======================================-->

                <div style="margin-bottom:20px;">

                    <label
                        class="form-label"
                        for="display_order"
                    >
                        Display Order
                    </label>

                    <input
                        type="number"
                        id="display_order"
                        name="display_order"
                        class="form-control"
                        min="0"
                        value="<?php echo htmlspecialchars($_POST['display_order'] ?? '1'); ?>"
                    >

                </div>


                <!--======================================
                        STATUS
                ======================================-->

                <div style="margin-bottom:25px;">

                    <label
                        class="form-label"
                        for="status"
                    >
                        Status
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="form-select"
                    >

                        <option
                            value="active"
                            <?php echo (($_POST['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            <?php echo (($_POST['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>
                        >
                            Inactive
                        </option>

                    </select>

                </div>


                <!--======================================
                        FORM ACTIONS
                ======================================-->

                <div
                    style="
                        display:flex;
                        gap:12px;
                        align-items:center;
                    "
                >

                    <a
                        href="rose-gold-collection.php"
                        class="btn-gg-outline"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="btn-gg-gold"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Add Rose Gold Product
                    </button>

                </div>


            </form>

        </div>

    </div>

</div>


<?php

include(__DIR__ . '/includes/footer.php');

?>