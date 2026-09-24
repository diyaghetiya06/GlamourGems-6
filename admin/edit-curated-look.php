<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Edit Curated Look';

$error = '';

/*======================================
        GET CURATED LOOK ID
======================================*/

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {

    header('Location: ' . ADMIN_URL . 'curated-looks.php');
    exit;

}


/*======================================
        FETCH CURATED LOOK
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM curated_looks
     WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$look = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$look) {

    header('Location: ' . ADMIN_URL . 'curated-looks.php');
    exit;

}


/*======================================
        FETCH PRODUCT IMAGES
======================================*/

$product_image_dir = __DIR__ . '/../assets/images/products/';

$product_images = [];

if (is_dir($product_image_dir)) {

    foreach (scandir($product_image_dir) as $file) {

        if (
            in_array(
                strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                ['jpg', 'jpeg', 'png', 'webp']
            )
        ) {

            $product_images[] = $file;

        }

    }

}

sort($product_images, SORT_NATURAL | SORT_FLAG_CASE);


/*======================================
        UPDATE CURATED LOOK
======================================*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = sanitize($_POST['title'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $image = sanitize($_POST['image'] ?? '');
    $display_order = intval($_POST['display_order'] ?? 0);
    $status = sanitize($_POST['status'] ?? 'active');


    if (empty($title) || $price <= 0 || empty($image)) {

        $error = 'Title, price and image are required.';

    } elseif (!in_array($status, ['active', 'inactive'])) {

        $error = 'Invalid status selected.';

    } elseif (!in_array($image, $product_images)) {

        $error = 'Invalid image selected.';

    } else {

        /*======================================
                UPDATE DATABASE
        ======================================*/

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE curated_looks
             SET title = ?,
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
                $title,
                $price,
                $image,
                $display_order,
                $status,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {

                set_flash(
                    'success',
                    'Curated Look updated successfully.'
                );

                mysqli_stmt_close($stmt);

                header(
                    'Location: ' .
                    ADMIN_URL .
                    'curated-looks.php'
                );

                exit;

            } else {

                $error =
                    'Database error: ' .
                    mysqli_error($conn);

                mysqli_stmt_close($stmt);

            }

        } else {

            $error = 'Failed to prepare database statement.';

        }

    }

}


/*======================================
        HEADER
======================================*/

include(__DIR__ . '/includes/header.php');

?>


<!--======================================
        PAGE HEADER
======================================-->

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">

    <div>

        <h2 class="page-header-title mb-1">
            Edit Curated Look
        </h2>

        <p class="page-header-sub mb-0">
            Update the selected Curated Look.
        </p>

    </div>

    <div>

        <a
            href="curated-looks.php"
            class="btn btn-outline-light"
        >
            <i class="fa-solid fa-arrow-left me-2"></i>
            Back to Curated Looks
        </a>

    </div>

</div>


<!--======================================
        ERROR MESSAGE
======================================-->

<?php if (!empty($error)): ?>

    <div class="alert alert-danger alert-dismissible fade show">

        <i class="fa-solid fa-circle-exclamation me-2"></i>

        <?php echo htmlspecialchars($error); ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

<?php endif; ?>


<div class="row justify-content-center">

    <div class="col-12 col-lg-8">

        <div class="gg-card">

            <div class="gg-card-header">

                <h5 class="gg-card-title">

                    <i class="fa-solid fa-pen-to-square"></i>

                    Edit Curated Look

                </h5>

            </div>


            <div class="gg-card-body">

                <form
                    action=""
                    method="POST"
                >


                    <!--======================================
                            TITLE
                    ======================================-->

                    <div class="mb-3">

                        <label
                            for="title"
                            class="form-label"
                        >

                            Title
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="title"
                            name="title"
                            value="<?php echo htmlspecialchars($look['title']); ?>"
                            required
                        >

                    </div>


                    <!--======================================
                            PRICE
                    ======================================-->

                    <div class="mb-3">

                        <label
                            for="price"
                            class="form-label"
                        >

                            Price
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="number"
                            class="form-control"
                            id="price"
                            name="price"
                            min="1"
                            step="0.01"
                            value="<?php echo htmlspecialchars($look['price']); ?>"
                            required
                        >

                    </div>


                    <!--======================================
                            IMAGE
                    ======================================-->

                    <div class="mb-3">

                        <label
                            for="image"
                            class="form-label"
                        >

                            Image
                            <span class="text-danger">*</span>

                        </label>

                        <select
                            class="form-select"
                            id="image"
                            name="image"
                            required
                        >

                            <?php foreach ($product_images as $image): ?>

                                <option
                                    value="<?php echo htmlspecialchars($image); ?>"
                                    <?php
                                    if ($image === $look['image']) {
                                        echo 'selected';
                                    }
                                    ?>
                                >

                                    <?php echo htmlspecialchars($image); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                        <small class="text-muted">
                            Select an image from assets/images/products/
                        </small>

                    </div>


                    <!--======================================
                            CURRENT IMAGE
                    ======================================-->

                    <div class="mb-4">

                        <label class="form-label">
                            Current Image
                        </label>

                        <div>

                            <img
                                src="<?php echo BASE_URL; ?>assets/images/products/<?php echo htmlspecialchars($look['image']); ?>"
                                alt="<?php echo htmlspecialchars($look['title']); ?>"
                                style="width:160px;height:160px;object-fit:cover;border-radius:10px;"
                            >

                        </div>

                    </div>


                    <!--======================================
                            DISPLAY ORDER
                    ======================================-->

                    <div class="mb-3">

                        <label
                            for="display_order"
                            class="form-label"
                        >

                            Display Order

                        </label>

                        <input
                            type="number"
                            class="form-control"
                            id="display_order"
                            name="display_order"
                            value="<?php echo htmlspecialchars($look['display_order']); ?>"
                            min="0"
                        >

                    </div>


                    <!--======================================
                            STATUS
                    ======================================-->

                    <div class="mb-4">

                        <label
                            for="status"
                            class="form-label"
                        >

                            Status

                        </label>

                        <select
                            class="form-select"
                            id="status"
                            name="status"
                        >

                            <option
                                value="active"
                                <?php
                                if ($look['status'] === 'active') {
                                    echo 'selected';
                                }
                                ?>
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                <?php
                                if ($look['status'] === 'inactive') {
                                    echo 'selected';
                                }
                                ?>
                            >
                                Inactive
                            </option>

                        </select>

                    </div>


                    <!--======================================
                            UPDATE BUTTON
                    ======================================-->

                    <button
                        type="submit"
                        class="btn btn-gg-gold"
                    >

                        <i class="fa-solid fa-save me-2"></i>

                        Update Curated Look

                    </button>

                    <a
                        href="curated-looks.php"
                        class="btn btn-outline-secondary ms-2"
                    >
                        Cancel
                    </a>


                </form>

            </div>

        </div>

    </div>

</div>


<?php include(__DIR__ . '/includes/footer.php'); ?>