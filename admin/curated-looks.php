<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Curated Looks';

$error = '';

/*======================================
        ADD CURATED LOOK
======================================*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = sanitize($_POST['title'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $display_order = intval($_POST['display_order'] ?? 0);
    $status = sanitize($_POST['status'] ?? 'active');

    if (empty($title) || $price <= 0) {

        $error = 'Title and valid price are required.';

    } elseif (!in_array($status, ['active', 'inactive'])) {

        $error = 'Invalid status selected.';

    } elseif (
        !isset($_FILES['image']) ||
        $_FILES['image']['error'] !== UPLOAD_ERR_OK
    ) {

        $error = 'Please select a Curated Look image.';

    } else {

        $extension = strtolower(
            pathinfo(
                $_FILES['image']['name'],
                PATHINFO_EXTENSION
            )
        );

        $allowed_extensions = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];

        if (!in_array($extension, $allowed_extensions)) {

            $error = 'Only JPG, JPEG, PNG and WEBP images are allowed.';

        } else {

            /*======================================
                    CREATE UPLOAD FOLDER
            ======================================*/

            $upload_dir = __DIR__ . '/../uploads/curated-looks/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            /*======================================
                    IMAGE NAME
            ======================================*/

            $image_name =
                'curated_' .
                time() .
                '_' .
                uniqid() .
                '.' .
                $extension;

            $target_file = $upload_dir . $image_name;

            /*======================================
                    MOVE IMAGE
            ======================================*/

            if (
                move_uploaded_file(
                    $_FILES['image']['tmp_name'],
                    $target_file
                )
            ) {

                /*======================================
                        INSERT DATABASE
                ======================================*/

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO curated_looks
                    (title, price, image, display_order, status)
                    VALUES (?, ?, ?, ?, ?)"
                );

                if ($stmt) {

                    mysqli_stmt_bind_param(
                        $stmt,
                        "sdsis",
                        $title,
                        $price,
                        $image_name,
                        $display_order,
                        $status
                    );

                    if (mysqli_stmt_execute($stmt)) {

                        set_flash(
                            'success',
                            'Curated Look added successfully.'
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

                        unlink($target_file);
                    }

                } else {

                    $error =
                        'Failed to prepare database statement.';

                    unlink($target_file);
                }

            } else {

                $error = 'Failed to upload image.';
            }
        }
    }
}


/*======================================
        FETCH CURATED LOOKS
======================================*/

$curated_looks = [];

$result = mysqli_query(
    $conn,
    "SELECT *
     FROM curated_looks
     ORDER BY display_order ASC, id DESC"
);

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {

        $curated_looks[] = $row;
    }
}


include(__DIR__ . '/includes/header.php');

?>


<!--======================================
        PAGE HEADER
======================================-->

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">

    <div>

        <h2 class="page-header-title mb-1">
            Curated Looks
        </h2>

        <p class="page-header-sub mb-0">
            Manage the jewellery displayed in the Home Page Curated Looks section.
        </p>

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


<div class="row g-4">


    <!--======================================
            ADD CURATED LOOK
    ======================================-->

    <div class="col-12 col-lg-4">

        <div class="gg-card">

            <div class="gg-card-header">

                <h5 class="gg-card-title">

                    <i class="fa-solid fa-gem"></i>

                    Add Curated Look

                </h5>

            </div>


            <div class="gg-card-body">

                <form
                    action=""
                    method="POST"
                    enctype="multipart/form-data"
                >


                    <!-- TITLE -->

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
                            placeholder="e.g. Diamond Necklace"
                            required
                        >

                    </div>


                    <!-- PRICE -->

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
                            placeholder="500000"
                            required
                        >

                    </div>


                    <!-- IMAGE -->

                    <div class="mb-3">

                        <label
                            for="image"
                            class="form-label"
                        >

                            Image
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="file"
                            class="form-control"
                            id="image"
                            name="image"
                            accept=".jpg,.jpeg,.png,.webp"
                            required
                        >

                        <small class="text-muted">
                            JPG, JPEG, PNG or WEBP
                        </small>

                    </div>


                    <!-- DISPLAY ORDER -->

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
                            value="0"
                            min="0"
                        >

                    </div>


                    <!-- STATUS -->

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

                            <option value="active">
                                Active
                            </option>

                            <option value="inactive">
                                Inactive
                            </option>

                        </select>

                    </div>


                    <!-- SAVE -->

                    <button
                        type="submit"
                        class="btn btn-gg-gold w-100"
                    >

                        <i class="fa-solid fa-plus me-2"></i>

                        Add Curated Look

                    </button>


                </form>

            </div>

        </div>

    </div>


    <!--======================================
            CURATED LOOKS LIST
    ======================================-->

    <div class="col-12 col-lg-8">

        <div class="gg-card">

            <div class="gg-card-header">

                <h5 class="gg-card-title">

                    <i class="fa-solid fa-images"></i>

                    Curated Looks List
                    (<?php echo count($curated_looks); ?>)

                </h5>

            </div>


            <div class="gg-card-body p-0">

                <div class="table-responsive">

                    <table class="table table-gg align-middle mb-0">

                        <thead>

                            <tr>

                                <th style="width:80px;">
                                    Image
                                </th>

                                <th>
                                    Title
                                </th>

                                <th>
                                    Price
                                </th>

                                <th>
                                    Order
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (!empty($curated_looks)): ?>

                                <?php foreach ($curated_looks as $look): ?>

                                    <tr>

                                        <!-- IMAGE -->

                                        <td>

                                            <img
                                                src="<?php echo BASE_URL; ?>assets/images/products/<?php echo htmlspecialchars($look['image']); ?>"
                                                alt="<?php echo htmlspecialchars($look['title']); ?>"
                                                class="table-img-thumb"
                                            >

                                        </td>


                                        <!-- TITLE -->

                                        <td>

                                            <div class="fw-semibold text-light">

                                                <?php
                                                echo htmlspecialchars(
                                                    $look['title']
                                                );
                                                ?>

                                            </div>

                                        </td>


                                        <!-- PRICE -->

                                        <td class="text-gold">

                                            ₹<?php
                                            echo number_format(
                                                $look['price'],
                                                0
                                            );
                                            ?>

                                        </td>


                                        <!-- ORDER -->

                                        <td>

                                            <?php
                                            echo $look['display_order'];
                                            ?>

                                        </td>


                                        <!-- STATUS -->

                                        <td>

                                            <?php if ($look['status'] === 'active'): ?>

                                                <span class="badge-gg badge-gg-success">
                                                    Active
                                                </span>

                                            <?php else: ?>

                                                <span class="badge-gg badge-gg-secondary">
                                                    Inactive
                                                </span>

                                            <?php endif; ?>

                                        </td>
                                        
                                        <!-- ACTIONS -->

                                        <td class="text-end">

                                            <div class="d-inline-flex gap-1">

                                                <a
                                                    href="<?php echo ADMIN_URL; ?>edit-curated-look.php?id=<?php echo $look['id']; ?>"
                                                    class="btn-action"
                                                    title="Edit Curated Look"
                                                >
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>

                                                <a
                                                    href="<?php echo ADMIN_URL; ?>delete-curated-look.php?id=<?php echo $look['id']; ?>"
                                                    class="btn-action btn-action-danger"
                                                    title="Delete Curated Look"
                                                    onclick="return confirm('Are you sure you want to delete this Curated Look?');"
                                                >
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="5"
                                        class="text-center py-5 text-muted"
                                    >

                                        <i class="fa-solid fa-images fa-2x mb-3"></i>

                                        <div>
                                            No Curated Looks added yet.
                                        </div>

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>


<?php include(__DIR__ . '/includes/footer.php'); ?>