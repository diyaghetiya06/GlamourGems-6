<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Add Silver Collection';

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
        ADD SILVER PRODUCT
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

    /* Validate Image */
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Product image is required.';
    }

    /*======================================
            IMAGE UPLOAD
    ======================================*/

    $image_name = '';

    if (empty($errors)) {

        $image = $_FILES['image'];

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

        $file_extension = strtolower(
            pathinfo($image['name'], PATHINFO_EXTENSION)
        );

        $max_size = 5 * 1024 * 1024;

        if (!in_array($file_extension, $allowed_extensions, true)) {

            $errors[] = 'Only JPG, JPEG, PNG and WEBP images are allowed.';

        } elseif ($image['size'] > $max_size) {

            $errors[] = 'Image size must be less than 5MB.';

        } else {

            $upload_directory = __DIR__ . '/../assets/images/silver/';

            if (!is_dir($upload_directory)) {
                mkdir($upload_directory, 0777, true);
            }

            $image_name =
                'silver_' .
                time() .
                '_' .
                uniqid() .
                '.' .
                $file_extension;

            $upload_path = $upload_directory . $image_name;

            if (!move_uploaded_file($image['tmp_name'], $upload_path)) {
                $errors[] = 'Failed to upload image.';
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
            "INSERT INTO collection_silver_products
            (name, price, image, display_order, status)
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

                header("Location: silver-collection.php");
                exit;

            } else {

                mysqli_stmt_close($stmt);

                if ($image_name !== '') {
                    $uploaded_file = __DIR__ . '/../assets/images/silver/' . $image_name;

                    if (file_exists($uploaded_file)) {
                        unlink($uploaded_file);
                    }
                }

                $errors[] = 'Failed to add Silver Collection product.';
            }

        } else {

            if ($image_name !== '') {
                $uploaded_file = __DIR__ . '/../assets/images/silver/' . $image_name;

                if (file_exists($uploaded_file)) {
                    unlink($uploaded_file);
                }
            }

            $errors[] = 'Database error. Please try again.';
        }
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
            <h1>Add Silver Product</h1>
            <p>Add a new product to the Silver Collection.</p>
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
                ADD PRODUCT FORM
    ======================================-->

    <div class="gg-card">

        <div class="gg-card-header">

            <h2 class="gg-card-title">
                <i class="fa-solid fa-plus"></i>
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
                        placeholder="Enter Silver product name"
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
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
                        placeholder="Enter price"
                        min="0"
                        step="0.01"
                        value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>"
                        required
                    >

                    <small>
                        Example: 40000
                    </small>

                </div>


                <!--======================================
                        IMAGE
                ======================================-->

                <div class="form-group">

                    <label for="image">
                        Product Image
                        <span>*</span>
                    </label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    >

                    <small>
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
                        value="<?php echo htmlspecialchars($_POST['display_order'] ?? '1'); ?>"
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
                        <i class="fa-solid fa-plus"></i>
                        Add Silver Product
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php include(__DIR__ . '/includes/footer.php'); ?>