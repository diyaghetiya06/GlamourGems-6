<?php

require_once "includes/auth_check.php";
require_once "../config/database.php";
require_once "includes/functions.php";

$error = "";

/*======================================
            GET BANNER ID
======================================*/

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    set_flash("Invalid banner.", "danger");
    header("Location: banners.php");
    exit;
}


/*======================================
            FETCH BANNER
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM banners WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$banner = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$banner) {
    set_flash("Banner not found.", "danger");
    header("Location: banners.php");
    exit;
}


/*======================================
            UPDATE BANNER
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

    } else {

        $new_image_name = $banner['image'];
        $new_image_path = "";


        /*======================================
                CHECK NEW IMAGE
        ======================================*/

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

                $error = "Unable to upload image.";

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

                    $new_image_name =
                        "banner_" . time() . "_" . uniqid() . "." . $extension;

                    $new_image_path =
                        $upload_dir . $new_image_name;


                    /*======================================
                        MOVE NEW IMAGE
                    ======================================*/

                    if (!move_uploaded_file(
                        $file['tmp_name'],
                        $new_image_path
                    )) {

                        $error = "Failed to upload image.";

                    }

                }

            }

        }


        /*======================================
            UPDATE DATABASE
        ======================================*/

        if ($error === '') {

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE banners
                 SET title = ?,
                     heading = ?,
                     description = ?,
                     image = ?,
                     display_order = ?,
                     status = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssssiii",
                $title,
                $heading,
                $description,
                $new_image_name,
                $display_order,
                $status,
                $id
            );


            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);


                /*======================================
                    DELETE OLD IMAGE
                    ONLY AFTER SUCCESSFUL UPDATE
                ======================================*/

                if (
                    $new_image_name !== $banner['image'] &&
                    !empty($banner['image'])
                ) {

                    $old_image_path =
                        "../uploads/banners/" . $banner['image'];

                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }

                }


                set_flash(
                    "Banner updated successfully.",
                    "success"
                );

                header("Location: banners.php");
                exit;


            } else {

                mysqli_stmt_close($stmt);


                /*======================================
                    REMOVE NEW IMAGE IF UPDATE FAILS
                ======================================*/

                if (
                    $new_image_path !== "" &&
                    file_exists($new_image_path)
                ) {

                    unlink($new_image_path);

                }

                $error = "Unable to update banner.";

            }

        }

    }

}


/*======================================
        REFRESH DATA AFTER ERROR
======================================*/

if ($error !== '') {

    $banner['title'] = $_POST['title'] ?? $banner['title'];
    $banner['heading'] = $_POST['heading'] ?? $banner['heading'];
    $banner['description'] = $_POST['description'] ?? $banner['description'];
    $banner['display_order'] = $_POST['display_order'] ?? $banner['display_order'];
    $banner['status'] = isset($_POST['status']) ? 1 : 0;

}


require_once "includes/header.php";

?>

<!--======================================
            EDIT BANNER PAGE
======================================-->

<div class="container-fluid edit-banner-page">


    <!--======================================
                PAGE HEADER
    ======================================-->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold mb-1">
                Edit Banner
            </h2>

            <p class="text-muted mb-0">
                Update homepage slider banner
            </p>

        </div>


        <a
            href="banners.php"
            class="btn btn-outline-secondary"
        >

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
                EDIT BANNER FORM
    ======================================-->

    <div class="card shadow-sm border-0">

        <div class="card-body p-4">

            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <div class="row g-4">


                    <!--======================================
                            BANNER TITLE
                    ======================================-->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            <i class="fa-solid fa-tag me-2"></i>

                            Banner Title

                        </label>

                        <input
                            type="text"
                            name="title"
                            class="form-control"
                            value="<?php echo htmlspecialchars($banner['title']); ?>"
                            required
                        >

                        <small class="text-muted">

                            Internal name for identifying the banner.

                        </small>

                    </div>


                    <!--======================================
                            BANNER HEADING
                    ======================================-->

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            <i class="fa-solid fa-heading me-2"></i>

                            Banner Heading

                        </label>

                        <input
                            type="text"
                            name="heading"
                            class="form-control"
                            value="<?php echo htmlspecialchars($banner['heading']); ?>"
                            required
                        >

                    </div>


                    <!--======================================
                            DESCRIPTION
                    ======================================-->

                    <div class="col-12">

                        <label class="form-label fw-semibold">

                            <i class="fa-solid fa-align-left me-2"></i>

                            Description

                        </label>

                        <textarea
                            name="description"
                            class="form-control"
                            rows="4"
                            placeholder="Enter a short description for the banner..."
                        ><?php echo htmlspecialchars($banner['description']); ?></textarea>

                    </div>


                    <!--======================================
                            CURRENT IMAGE
                    ======================================-->

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">

                            <i class="fa-solid fa-image me-2"></i>

                            Current Banner

                        </label>

                        <div class="current-banner-box">

                            <img
                                src="../uploads/banners/<?php echo htmlspecialchars($banner['image']); ?>"
                                alt="<?php echo htmlspecialchars($banner['title']); ?>"
                                class="current-banner-image"
                            >

                        </div>

                    </div>


                    <!--======================================
                            NEW IMAGE
                    ======================================-->

                    <div class="col-md-8">

                        <label class="form-label fw-semibold">

                            <i class="fa-solid fa-cloud-arrow-up me-2"></i>

                            Replace Banner Image

                        </label>

                        <input
                            type="file"
                            name="image"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <small class="text-muted">

                            Leave empty to keep the current image.
                            JPG, JPEG, PNG or WEBP • Maximum 5MB

                        </small>

                    </div>


                    <!--======================================
                            DISPLAY ORDER
                    ======================================-->

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">

                            <i class="fa-solid fa-sort me-2"></i>

                            Display Order

                        </label>

                        <input
                            type="number"
                            name="display_order"
                            class="form-control"
                            value="<?php echo htmlspecialchars($banner['display_order']); ?>"
                            min="0"
                        >

                        <small class="text-muted">

                            Lower number appears first.

                        </small>

                    </div>


                    <!--======================================
                            STATUS
                    ======================================-->

                    <div class="col-md-8">

                        <label class="form-label fw-semibold">

                            <i class="fa-solid fa-toggle-on me-2"></i>

                            Banner Status

                        </label>

                        <div class="form-check form-switch mt-2">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="status"
                                id="status"
                                <?php echo ($banner['status'] == 1) ? 'checked' : ''; ?>
                            >

                            <label
                                class="form-check-label fw-semibold"
                                for="status"
                            >

                                Active Banner

                            </label>

                        </div>

                    </div>


                    <!--======================================
                            ACTION BUTTONS
                    ======================================-->

                    <div class="col-12 pt-2">

                        <button
                            type="submit"
                            class="btn btn-primary px-4"
                        >

                            <i class="fa-solid fa-floppy-disk me-2"></i>

                            Update Banner

                        </button>


                        <a
                            href="banners.php"
                            class="btn btn-light border px-4 ms-2"
                        >

                            <i class="fa-solid fa-xmark me-2"></i>

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
        EDIT BANNER DARK THEME
======================================*/

.edit-banner-page {
    color: #f1f1f1;
}


/*======================================
        PAGE HEADER
======================================*/

.edit-banner-page h2 {
    color: #C8A165;
    font-family: "Cinzel", serif;
    letter-spacing: 1px;
}

.edit-banner-page .text-muted {
    color: #9b9b9b !important;
}


/*======================================
        BACK BUTTON
======================================*/

.edit-banner-page .btn-outline-secondary {
    color: #C8A165;
    border-color: #C8A165;
}

.edit-banner-page .btn-outline-secondary:hover {
    background: #C8A165;
    border-color: #C8A165;
    color: #111;
}


/*======================================
        FORM CARD
======================================*/

.edit-banner-page .card {
    background: #151a19;
    border: 1px solid #2d3533 !important;
    border-radius: 10px;
}


/*======================================
        FORM LABELS
======================================*/

.edit-banner-page .form-label,
.edit-banner-page .form-check-label {
    color: #f1f1f1;
}

.edit-banner-page .form-label i {
    color: #C8A165;
}


/*======================================
        INPUTS
======================================*/

.edit-banner-page .form-control {
    background: #101514;
    border: 1px solid #3a4441;
    color: #f1f1f1;
    border-radius: 6px;
}

.edit-banner-page .form-control:focus {
    background: #101514;
    border-color: #C8A165;
    color: #ffffff;
    box-shadow: 0 0 0 0.15rem rgba(200, 161, 101, 0.15);
}

.edit-banner-page .form-control::placeholder {
    color: #777;
}


/*======================================
        FILE INPUT
======================================*/

.edit-banner-page input[type="file"] {
    color: #d6d6d6;
}

.edit-banner-page input[type="file"]::file-selector-button {
    background: #252d2b;
    color: #C8A165;
    border: 0;
    border-right: 1px solid #3a4441;
    margin-right: 12px;
}

.edit-banner-page input[type="file"]::file-selector-button:hover {
    background: #303936;
}


/*======================================
        CURRENT IMAGE
======================================*/

.current-banner-box {
    background: #101514;
    border: 1px solid #3a4441;
    border-radius: 8px;
    padding: 8px;
    width: 100%;
}

.current-banner-image {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: 6px;
    display: block;
}


/*======================================
        STATUS SWITCH
======================================*/

.edit-banner-page .form-check-input {
    background-color: #303936;
    border-color: #59635f;
}

.edit-banner-page .form-check-input:checked {
    background-color: #C8A165;
    border-color: #C8A165;
}

.edit-banner-page .form-check-input:focus {
    box-shadow: 0 0 0 0.15rem rgba(200, 161, 101, 0.15);
}


/*======================================
        UPDATE BUTTON
======================================*/

.edit-banner-page .btn-primary {
    background: #C8A165;
    border-color: #C8A165;
    color: #111;
    font-weight: 600;
}

.edit-banner-page .btn-primary:hover {
    background: #b38c50;
    border-color: #b38c50;
    color: #111;
}


/*======================================
        CANCEL BUTTON
======================================*/

.edit-banner-page .btn-light {
    background: #252d2b;
    border-color: #3a4441 !important;
    color: #dcdcdc;
}

.edit-banner-page .btn-light:hover {
    background: #303936;
    color: #ffffff;
}


/*======================================
        ERROR MESSAGE
======================================*/

.edit-banner-page .alert-danger {
    background: #321b1e;
    border: 1px solid #74343a;
    color: #ffb3b8;
}

</style>


<?php require_once "includes/footer.php"; ?>