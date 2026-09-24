<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Add Gold Collection';

$message = '';
$message_type = '';

/*======================================
        ADD GOLD COLLECTION
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

    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {

        $message = 'Please select a Gold Collection image.';
        $message_type = 'error';

    } else {

        $image = $_FILES['image'];

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

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

            $file_name = $safe_name . '-' . time() . '.' . $extension;

            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($image['tmp_name'], $target_file)) {

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO collection_gold_products
                    (name, price, image, display_order, status)
                    VALUES (?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "sdsis",
                    $name,
                    $price,
                    $file_name,
                    $display_order,
                    $status
                );

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    header('Location: gold-collection.php?success=added');
                    exit;

                } else {

                    mysqli_stmt_close($stmt);

                    if (file_exists($target_file)) {
                        unlink($target_file);
                    }

                    $message = 'Unable to add Gold Collection product.';
                    $message_type = 'error';
                }

            } else {

                $message = 'Unable to upload image.';
                $message_type = 'error';
            }
        }
    }
}

include(__DIR__ . '/includes/header.php');

?>

<div class="admin-page">

    <!--======================================
            PAGE HEADER
    ======================================-->
    <div class="gg-page-header">

        <div>
            <h1 class="gg-page-title">
                Add Gold Collection
            </h1>

            <p class="gg-page-subtitle">
                Add a new product to the Gold Collection.
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
            ADD FORM
    ======================================-->
    <div class="gg-card">

        <div class="gg-card-header">

            <h2 class="gg-card-title">
                Gold Collection Details
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
                        placeholder="Enter product name"
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
                        <span class="required">*</span>
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
                <div class="form-group">

                    <label for="image">
                        Gold Collection Image
                        <span class="required">*</span>
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
                        value="<?php echo htmlspecialchars($_POST['display_order'] ?? '1'); ?>"
                        min="1"
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
                        <i class="fa-solid fa-plus"></i>
                        Add Gold Product
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php

include(__DIR__ . '/includes/footer.php');

?>