<?php

require_once "includes/auth_check.php";
require_once "../config/database.php";
require_once "includes/functions.php";

$error = "";

/*======================================
            ADD NEW BANNER
======================================*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = sanitize($_POST['title'] ?? '');
    $heading = sanitize($_POST['heading'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $display_order = (int) ($_POST['display_order'] ?? 0);
    $status = isset($_POST['status']) ? 1 : 0;

    /*======================================
            VALIDATION
    ======================================*/

    if ($title === '' || $heading === '') {

        $error = "Title and heading are required.";

    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {

        $error = "Please select a banner image.";

    } else {

        $file = $_FILES['image'];

        $allowed_types = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp'
        ];

        if (!in_array($file['type'], $allowed_types)) {

            $error = "Only JPG, JPEG, PNG and WEBP images are allowed.";

        } elseif ($file['size'] > 5 * 1024 * 1024) {

            $error = "Image size must be less than 5MB.";

        } else {

            /*======================================
                CREATE UPLOAD DIRECTORY
            ======================================*/

            $upload_dir = "../uploads/banners/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            /*======================================
                    CREATE UNIQUE FILE NAME
            ======================================*/

            $extension = strtolower(
                pathinfo($file['name'], PATHINFO_EXTENSION)
            );

            $image_name = "banner_" . time() . "_" . uniqid() . "." . $extension;

            $image_path = $upload_dir . $image_name;


            /*======================================
                    MOVE IMAGE
            ======================================*/

            if (move_uploaded_file($file['tmp_name'], $image_path)) {

                /*======================================
                        INSERT INTO DATABASE
                ======================================*/

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO banners
                    (title, heading, description, image, display_order, status)
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssii",
                    $title,
                    $heading,
                    $description,
                    $image_name,
                    $display_order,
                    $status
                );

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    set_flash(
                        "Banner added successfully.",
                        "success"
                    );

                    header("Location: banners.php");
                    exit;

                } else {

                    mysqli_stmt_close($stmt);

                    // Remove uploaded image if DB insert fails
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }

                    $error = "Unable to save banner.";

                }

            } else {

                $error = "Failed to upload image.";
            }
        }
    }
}

require_once "includes/header.php";

?>

<!--======================================
            ADD BANNER PAGE
======================================-->

<div class="container-fluid add-banner-page">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold mb-1">Add New Banner</h2>

            <p class="text-muted mb-0">
                Add a banner to the homepage slider
            </p>
        </div>

        <a href="banners.php" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i>
            Back to Banners
        </a>

    </div>


    <!--======================================
                ERROR MESSAGE
    ======================================-->

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <!--======================================
                BANNER FORM
    ======================================-->

    <div class="card shadow-sm border-0">

        <div class="card-body p-4">

            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <div class="row g-4">

                    <!-- Title -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Banner Title
                        </label>

                        <input
                            type="text"
                            name="title"
                            class="form-control"
                            placeholder="Example: Luxury Collection"
                            value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                            required
                        >

                        <small class="text-muted">
                            Internal name for identifying the banner.
                        </small>

                    </div>


                    <!-- Heading -->
                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Banner Heading
                        </label>

                        <input
                            type="text"
                            name="heading"
                            class="form-control"
                            placeholder="Example: Timeless Elegance"
                            value="<?php echo htmlspecialchars($_POST['heading'] ?? ''); ?>"
                            required
                        >

                    </div>


                    <!-- Description -->
                    <div class="col-12">

                        <label class="form-label fw-semibold">
                            Description
                        </label>

                        <textarea
                            name="description"
                            class="form-control"
                            rows="4"
                            placeholder="Enter a short description for the banner..."
                        ><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>

                    </div>


                    <!-- Image -->
                    <div class="col-md-8">

                        <label class="form-label fw-semibold">
                            Banner Image
                        </label>

                        <input
                            type="file"
                            name="image"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp"
                            required
                        >

                        <small class="text-muted">
                            JPG, JPEG, PNG or WEBP • Maximum 5MB
                        </small>

                    </div>


                    <!-- Display Order -->
                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Display Order
                        </label>

                        <input
                            type="number"
                            name="display_order"
                            class="form-control"
                            value="<?php echo htmlspecialchars($_POST['display_order'] ?? '0'); ?>"
                            min="0"
                        >

                        <small class="text-muted">
                            Lower number appears first.
                        </small>

                    </div>


                    <!-- Status -->
                    <div class="col-12">

                        <div class="form-check form-switch">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="status"
                                id="status"
                                checked
                            >

                            <label
                                class="form-check-label fw-semibold"
                                for="status"
                            >
                                Active Banner
                            </label>

                        </div>

                    </div>


                    <!-- Submit -->
                    <div class="col-12 pt-2">

                        <button
                            type="submit"
                            class="btn btn-primary px-4"
                        >
                            <i class="fa-solid fa-plus me-2"></i>
                            Add Banner
                        </button>

                        <a
                            href="banners.php"
                            class="btn btn-light border px-4 ms-2"
                        >
                            Cancel
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

<style>

/*======================================
        ADD BANNER DARK THEME
======================================*/

.add-banner-page {
    color: #f1f1f1;
}

/*======================================
        PAGE HEADER
======================================*/

.add-banner-page h2 {
    color: #C8A165;
    font-family: "Cinzel", serif;
    letter-spacing: 1px;
}

.add-banner-page .text-muted {
    color: #9b9b9b !important;
}

/*======================================
        BACK BUTTON
======================================*/

.add-banner-page .btn-outline-secondary {
    color: #C8A165;
    border-color: #C8A165;
}

.add-banner-page .btn-outline-secondary:hover {
    background: #C8A165;
    border-color: #C8A165;
    color: #111;
}

/*======================================
        FORM CARD
======================================*/

.add-banner-page .card {
    background: #151a19;
    border: 1px solid #2d3533 !important;
    border-radius: 10px;
}

/*======================================
        FORM LABELS
======================================*/

.add-banner-page .form-label,
.add-banner-page .form-check-label {
    color: #f1f1f1;
}

/*======================================
        INPUTS
======================================*/

.add-banner-page .form-control {
    background: #101514;
    border: 1px solid #3a4441;
    color: #f1f1f1;
    border-radius: 6px;
}

.add-banner-page .form-control:focus {
    background: #101514;
    border-color: #C8A165;
    color: #ffffff;
    box-shadow: 0 0 0 0.15rem rgba(200, 161, 101, 0.15);
}

.add-banner-page .form-control::placeholder {
    color: #777;
}

/*======================================
        FILE INPUT
======================================*/

.add-banner-page input[type="file"] {
    color: #d6d6d6;
}

.add-banner-page input[type="file"]::file-selector-button {
    background: #252d2b;
    color: #C8A165;
    border: 0;
    border-right: 1px solid #3a4441;
    margin-right: 12px;
}

.add-banner-page input[type="file"]::file-selector-button:hover {
    background: #303936;
}

/*======================================
        STATUS SWITCH
======================================*/

.add-banner-page .form-check-input {
    background-color: #303936;
    border-color: #59635f;
}

.add-banner-page .form-check-input:checked {
    background-color: #C8A165;
    border-color: #C8A165;
}

.add-banner-page .form-check-input:focus {
    box-shadow: 0 0 0 0.15rem rgba(200, 161, 101, 0.15);
}

/*======================================
        ADD BANNER BUTTON
======================================*/

.add-banner-page .btn-primary {
    background: #C8A165;
    border-color: #C8A165;
    color: #111;
    font-weight: 600;
}

.add-banner-page .btn-primary:hover {
    background: #b38c50;
    border-color: #b38c50;
    color: #111;
}

/*======================================
        CANCEL BUTTON
======================================*/

.add-banner-page .btn-light {
    background: #252d2b;
    border-color: #3a4441 !important;
    color: #dcdcdc;
}

.add-banner-page .btn-light:hover {
    background: #303936;
    color: #ffffff;
}

/*======================================
        ERROR MESSAGE
======================================*/

.add-banner-page .alert-danger {
    background: #321b1e;
    border: 1px solid #74343a;
    color: #ffb3b8;
}

</style>

<?php require_once "includes/footer.php"; ?>