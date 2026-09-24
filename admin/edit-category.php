<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/includes/functions.php');


/*======================================
        GET CATEGORY ID
======================================*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    set_flash(
        'danger',
        'Invalid category ID.'
    );

    header('Location: ' . ADMIN_URL . 'categories.php');
    exit;
}

$category_id = (int) $_GET['id'];


/*======================================
        IMAGE UPLOAD SETTINGS
======================================*/

$upload_dir = __DIR__ . '/../assets/images/recipient/';

$allowed_extensions = [
    'jpg',
    'jpeg',
    'png',
    'webp'
];

$max_file_size = 5 * 1024 * 1024;

$error = '';


/*======================================
        FETCH CATEGORY
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM categories WHERE id = ? LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $category_id
);

mysqli_stmt_execute($stmt);

$res = mysqli_stmt_get_result($stmt);

$category = mysqli_fetch_assoc($res);

mysqli_stmt_close($stmt);


if (!$category) {

    set_flash(
        'danger',
        'Category not found.'
    );

    header('Location: ' . ADMIN_URL . 'categories.php');
    exit;
}


/*======================================
        UPDATE CATEGORY
======================================*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $status = sanitize($_POST['status'] ?? 'active');

    $slug = create_slug($name);


    /*======================================
            VALIDATION
    ======================================*/

    if (empty($name)) {

        $error = 'Category name is required.';

    } elseif (!in_array($status, ['active', 'inactive'])) {

        $error = 'Invalid category status.';

    }


    /*======================================
            IMAGE UPLOAD
    ======================================*/

    $new_image_name = '';

    if (
        empty($error) &&
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

            $error = 'There was an error uploading the image.';

        } elseif ($_FILES['image']['size'] > $max_file_size) {

            $error = 'Image size must be less than 5MB.';

        } else {

            $extension = strtolower(
                pathinfo(
                    $_FILES['image']['name'],
                    PATHINFO_EXTENSION
                )
            );


            if (!in_array($extension, $allowed_extensions)) {

                $error =
                    'Only JPG, JPEG, PNG and WEBP images are allowed.';

            } else {

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }


                $new_image_name =
                    'category_' .
                    time() .
                    '_' .
                    uniqid() .
                    '.' .
                    $extension;


                $target_file =
                    $upload_dir . $new_image_name;


                if (
                    !move_uploaded_file(
                        $_FILES['image']['tmp_name'],
                        $target_file
                    )
                ) {

                    $error =
                        'Failed to save category image.';
                }
            }
        }
    }


    /*======================================
            UPDATE DATABASE
    ======================================*/

    if (empty($error)) {

        if (!empty($new_image_name)) {

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE categories
                 SET name = ?,
                     slug = ?,
                     description = ?,
                     image = ?,
                     status = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssssi",
                $name,
                $slug,
                $description,
                $new_image_name,
                $status,
                $category_id
            );

        } else {

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE categories
                 SET name = ?,
                     slug = ?,
                     description = ?,
                     status = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssssi",
                $name,
                $slug,
                $description,
                $status,
                $category_id
            );
        }


        /*======================================
                EXECUTE UPDATE
        ======================================*/

        if (mysqli_stmt_execute($stmt)) {

            /* Delete old image only when new image is uploaded */

            if (
                !empty($new_image_name) &&
                !empty($category['image']) &&
                $category['image'] !== 'category_default.jpg'
            ) {

                $old_image =
                    $upload_dir .
                    basename($category['image']);

                if (file_exists($old_image)) {
                    unlink($old_image);
                }
            }


            set_flash(
                'success',
                'Category "' .
                htmlspecialchars($name) .
                '" updated successfully.'
            );


            mysqli_stmt_close($stmt);


            header(
                'Location: ' .
                ADMIN_URL .
                'categories.php'
            );

            exit;

        } else {

            /* Remove new image if database update fails */

            if (!empty($new_image_name)) {

                $new_image =
                    $upload_dir .
                    basename($new_image_name);

                if (file_exists($new_image)) {
                    unlink($new_image);
                }
            }


            $error =
                'Failed to update category: ' .
                mysqli_error($conn);

            mysqli_stmt_close($stmt);
        }
    }
}

?>


<?php include(__DIR__ . '/includes/header.php'); ?>


<!--======================================
        EDIT CATEGORY PAGE
======================================-->

<div class="container-fluid edit-category-page">

    <!--======================================
            PAGE HEADER
    ======================================-->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="page-header-title mb-1">
                Edit Category
            </h2>

            <p class="page-header-sub mb-0">
                Update category information and image.
            </p>

        </div>


        <a
            href="<?php echo ADMIN_URL; ?>categories.php"
            class="btn btn-gg-outline"
        >
            <i class="fa-solid fa-arrow-left me-2"></i>
            Back to Categories
        </a>

    </div>


    <!--======================================
            ERROR MESSAGE
    ======================================-->

    <?php if (!empty($error)): ?>

        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >

            <i class="fa-solid fa-circle-exclamation me-2"></i>

            <?php echo htmlspecialchars($error); ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>

        </div>

    <?php endif; ?>


    <!--======================================
            CATEGORY FORM
    ======================================-->

    <div class="row justify-content-center">

        <div class="col-12 col-lg-7 col-xl-6">

            <div class="gg-card">

                <div class="gg-card-header">

                    <h5 class="gg-card-title">

                        <i class="fa-solid fa-tags"></i>

                        Category Details

                    </h5>

                </div>


                <div class="gg-card-body">

                    <form
                        method="POST"
                        enctype="multipart/form-data"
                    >


                        <!-- CATEGORY NAME -->

                        <div class="mb-3">

                            <label
                                for="name"
                                class="form-label"
                            >

                                <i class="fa-solid fa-tag me-1"></i>

                                Category Name

                                <span class="text-danger">*</span>

                            </label>


                            <input
                                type="text"
                                class="form-control"
                                id="name"
                                name="name"
                                value="<?php echo htmlspecialchars($category['name']); ?>"
                                placeholder="Category name"
                                required
                            >

                        </div>


                        <!-- DESCRIPTION -->

                        <div class="mb-3">

                            <label
                                for="description"
                                class="form-label"
                            >

                                <i class="fa-solid fa-align-left me-1"></i>

                                Description

                            </label>


                            <textarea
                                class="form-control"
                                id="description"
                                name="description"
                                rows="4"
                                placeholder="Brief summary of category..."
                            ><?php echo htmlspecialchars($category['description']); ?></textarea>

                        </div>


                        <!-- CURRENT IMAGE -->

                        <div class="mb-3">

                            <label class="form-label">

                                <i class="fa-solid fa-image me-1"></i>

                                Current Image

                            </label>


                            <div class="mb-3">

                                <img
                                    src="../assets/images/recipient/<?php echo htmlspecialchars($category['image'] ?: 'category_default.jpg'); ?>"
                                    alt="<?php echo htmlspecialchars($category['name']); ?>"
                                    class="category-current-image"
                                >

                            </div>

                        </div>


                        <!-- NEW IMAGE -->

                        <div class="mb-3">

                            <label
                                for="image"
                                class="form-label"
                            >

                                <i class="fa-solid fa-cloud-arrow-up me-1"></i>

                                Replace Image

                            </label>


                            <input
                                type="file"
                                class="form-control"
                                id="image"
                                name="image"
                                accept=".jpg,.jpeg,.png,.webp"
                            >


                            <small class="text-muted">
                                JPG, JPEG, PNG or WEBP. Maximum 5MB.
                            </small>

                        </div>


                        <!-- STATUS -->

                        <div class="mb-4">

                            <label
                                for="status"
                                class="form-label"
                            >

                                <i class="fa-solid fa-toggle-on me-1"></i>

                                Status

                            </label>


                            <select
                                class="form-select"
                                id="status"
                                name="status"
                            >

                                <option
                                    value="active"
                                    <?php echo $category['status'] === 'active' ? 'selected' : ''; ?>
                                >
                                    Active
                                </option>


                                <option
                                    value="inactive"
                                    <?php echo $category['status'] === 'inactive' ? 'selected' : ''; ?>
                                >
                                    Inactive
                                </option>

                            </select>

                        </div>


                        <!-- BUTTONS -->

                        <div class="d-flex gap-2">

                            <button
                                type="submit"
                                class="btn btn-gg-gold"
                            >

                                <i class="fa-solid fa-floppy-disk me-2"></i>

                                Update Category

                            </button>


                            <a
                                href="<?php echo ADMIN_URL; ?>categories.php"
                                class="btn btn-gg-outline"
                            >

                                <i class="fa-solid fa-xmark me-1"></i>

                                Cancel

                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


<style>

/*======================================
        EDIT CATEGORY PAGE
======================================*/

.edit-category-page {
    color: #f5f5f5;
}


/*======================================
        PAGE HEADER
======================================*/

.edit-category-page .page-header-title {
    color: #C8A165;
    font-family: "Cinzel", serif;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.edit-category-page .page-header-sub {
    color: #9b9b9b;
}


/*======================================
        CARD
======================================*/

.edit-category-page .gg-card {
    background: #151a19;
    border: 1px solid #2d3533;
    border-radius: 10px;
    overflow: hidden;
}

.edit-category-page .gg-card-header {
    background: #101514;
    border-bottom: 1px solid #303937;
    padding: 18px 20px;
}

.edit-category-page .gg-card-title {
    margin: 0;
    color: #C8A165;
    font-weight: 600;
}

.edit-category-page .gg-card-title i {
    margin-right: 8px;
}

.edit-category-page .gg-card-body {
    padding: 25px;
}


/*======================================
        FORM
======================================*/

.edit-category-page .form-label {
    color: #eeeeee;
    font-weight: 500;
}

.edit-category-page .form-control,
.edit-category-page .form-select {
    background: #101514;
    border: 1px solid #39423f;
    color: #ffffff;
    border-radius: 7px;
}

.edit-category-page .form-control:focus,
.edit-category-page .form-select:focus {
    background: #101514;
    color: #ffffff;
    border-color: #C8A165;
    box-shadow: 0 0 0 0.2rem rgba(200, 161, 101, 0.12);
}

.edit-category-page .form-control::placeholder {
    color: #777;
}


/*======================================
        CURRENT IMAGE
======================================*/

.edit-category-page .category-current-image {
    width: 180px;
    height: 110px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid rgba(200, 161, 101, 0.35);
}


/*======================================
        GOLD BUTTON
======================================*/

.edit-category-page .btn-gg-gold {
    background: #C8A165;
    border: 1px solid #C8A165;
    color: #111;
    font-weight: 600;
}

.edit-category-page .btn-gg-gold:hover {
    background: #b38c50;
    border-color: #b38c50;
    color: #111;
}


/*======================================
        OUTLINE BUTTON
======================================*/

.edit-category-page .btn-gg-outline {
    background: transparent;
    border: 1px solid #C8A165;
    color: #C8A165;
}

.edit-category-page .btn-gg-outline:hover {
    background: #C8A165;
    color: #111;
}


/*======================================
        MUTED TEXT
======================================*/

.edit-category-page .text-muted {
    color: #999 !important;
}

</style>


<?php include(__DIR__ . '/includes/footer.php'); ?>