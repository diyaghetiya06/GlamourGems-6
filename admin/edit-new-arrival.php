<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/includes/functions.php');

$page_title = "Edit New Arrival";

$upload_dir = __DIR__ . '/../assets/images/arrivals/';


/*======================================
        VALIDATE ID
======================================*/

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {

    set_flash('danger', 'Invalid New Arrival ID.');

    header("Location: " . ADMIN_URL . "new-arrivals.php");

    exit;
}


/*======================================
        FETCH NEW ARRIVAL
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM new_arrivals WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$arrival = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$arrival) {

    set_flash('danger', 'New Arrival not found.');

    header("Location: " . ADMIN_URL . "new-arrivals.php");

    exit;
}


/*======================================
        UPDATE NEW ARRIVAL
======================================*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $old_price = trim($_POST['old_price'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';

    $errors = [];


    /*======================================
            VALIDATION
    ======================================*/

    if ($title === '') {
        $errors[] = "Title is required.";
    }

    if ($price === '' || !is_numeric($price) || $price < 0) {
        $errors[] = "Please enter a valid New Price.";
    }

    if ($old_price === '' || !is_numeric($old_price) || $old_price < 0) {
        $errors[] = "Please enter a valid Old Price.";
    }

    if ($display_order < 0) {
        $display_order = 0;
    }

    if (!in_array($status, ['active', 'inactive'])) {
        $status = 'active';
    }


    /*======================================
            IMAGE HANDLING
    ======================================*/

    $new_image_name = $arrival['image'];
    $uploaded_new_image = false;
    $new_image_path = '';


    if (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

            $errors[] =
                "There was an error uploading the image.";

        } else {

            $allowed_types = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            $file_type = mime_content_type(
                $_FILES['image']['tmp_name']
            );

            $file_size = $_FILES['image']['size'];


            if (!in_array($file_type, $allowed_types)) {

                $errors[] =
                    "Only JPG, PNG and WEBP images are allowed.";
            }


            if ($file_size > 5 * 1024 * 1024) {

                $errors[] =
                    "Image size must be less than 5MB.";
            }


            if (empty($errors)) {

                $extension = strtolower(
                    pathinfo(
                        $_FILES['image']['name'],
                        PATHINFO_EXTENSION
                    )
                );

                $new_image_name =
                    'arrival_' .
                    time() .
                    '_' .
                    uniqid() .
                    '.' .
                    $extension;

                $new_image_path =
                    $upload_dir . $new_image_name;


                if (
                    move_uploaded_file(
                        $_FILES['image']['tmp_name'],
                        $new_image_path
                    )
                ) {

                    $uploaded_new_image = true;

                } else {

                    $errors[] =
                        "Failed to upload the new image.";

                    $new_image_name = $arrival['image'];
                }
            }
        }
    }


    /*======================================
            UPDATE DATABASE
    ======================================*/

    if (empty($errors)) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE new_arrivals
             SET title = ?,
                 price = ?,
                 old_price = ?,
                 image = ?,
                 display_order = ?,
                 status = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sddsisi",
            $title,
            $price,
            $old_price,
            $new_image_name,
            $display_order,
            $status,
            $id
        );


        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);


            /*======================================
                    DELETE OLD IMAGE
            ======================================*/

            if (
                $uploaded_new_image &&
                !empty($arrival['image'])
            ) {

                $old_image_path =
                    $upload_dir . $arrival['image'];

                if (
                    file_exists($old_image_path) &&
                    is_file($old_image_path)
                ) {

                    unlink($old_image_path);
                }
            }


            set_flash(
                'success',
                'New Arrival updated successfully.'
            );

            header(
                "Location: " .
                ADMIN_URL .
                "new-arrivals.php"
            );

            exit;

        } else {

            mysqli_stmt_close($stmt);


            /*======================================
                    REMOVE NEW IMAGE ON FAILURE
            ======================================*/

            if (
                $uploaded_new_image &&
                file_exists($new_image_path)
            ) {

                unlink($new_image_path);
            }

            $errors[] =
                "Failed to update New Arrival.";
        }
    }
}


require_once(__DIR__ . '/includes/header.php');
require_once(__DIR__ . '/includes/sidebar.php');

?>

<main class="main-content">

    <div class="container-fluid">


        <!--======================================
                PAGE HEADER
        ======================================-->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1>Edit New Arrival</h1>

                <p class="text-muted">
                    Update New Arrival details.
                </p>

            </div>

            <a
                href="<?php echo ADMIN_URL; ?>new-arrivals.php"
                class="btn btn-secondary"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Back

            </a>

        </div>


        <!--======================================
                ERROR MESSAGES
        ======================================-->

        <?php if (!empty($errors)): ?>

            <div class="alert alert-danger">

                <ul class="mb-0">

                    <?php foreach ($errors as $error): ?>

                        <li>
                            <?php echo htmlspecialchars($error); ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>


        <!--======================================
                EDIT FORM
        ======================================-->

        <div class="card">

            <div class="card-body">

                <form
                    method="POST"
                    enctype="multipart/form-data"
                >


                    <!-- Title -->

                    <div class="mb-3">

                        <label class="form-label">
                            Title
                        </label>

                        <input
                            type="text"
                            name="title"
                            class="form-control"
                            value="<?php echo htmlspecialchars($_POST['title'] ?? $arrival['title']); ?>"
                            required
                        >

                    </div>


                    <!-- New Price -->

                    <div class="mb-3">

                        <label class="form-label">
                            New Price
                        </label>

                        <input
                            type="number"
                            name="price"
                            class="form-control"
                            min="0"
                            step="0.01"
                            value="<?php echo htmlspecialchars($_POST['price'] ?? $arrival['price']); ?>"
                            required
                        >

                    </div>


                    <!-- Old Price -->

                    <div class="mb-3">

                        <label class="form-label">
                            Old Price
                        </label>

                        <input
                            type="number"
                            name="old_price"
                            class="form-control"
                            min="0"
                            step="0.01"
                            value="<?php echo htmlspecialchars($_POST['old_price'] ?? $arrival['old_price']); ?>"
                            required
                        >

                    </div>


                    <!-- Current Image -->

                    <div class="mb-3">

                        <label class="form-label">
                            Current Image
                        </label>

                        <div>

                            <img
                                src="<?php echo BASE_URL; ?>assets/images/arrivals/<?php echo htmlspecialchars($arrival['image']); ?>"
                                alt="<?php echo htmlspecialchars($arrival['title']); ?>"
                                style="width:140px; height:140px; object-fit:cover; border-radius:10px;"
                            >

                        </div>

                    </div>


                    <!-- Replace Image -->

                    <div class="mb-3">

                        <label class="form-label">
                            Replace Image
                        </label>

                        <input
                            type="file"
                            name="image"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <small class="text-muted">
                            Leave empty to keep the current image.
                            JPG, PNG or WEBP | Maximum 5MB
                        </small>

                    </div>


                    <!-- Display Order -->

                    <div class="mb-3">

                        <label class="form-label">
                            Display Order
                        </label>

                        <input
                            type="number"
                            name="display_order"
                            class="form-control"
                            min="0"
                            value="<?php echo htmlspecialchars($_POST['display_order'] ?? $arrival['display_order']); ?>"
                        >

                    </div>


                    <!-- Status -->

                    <div class="mb-4">

                        <label class="form-label">
                            Status
                        </label>

                        <select
                            name="status"
                            class="form-select"
                        >

                            <option
                                value="active"
                                <?php echo (($arrival['status'] === 'active') ? 'selected' : ''); ?>
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                <?php echo (($arrival['status'] === 'inactive') ? 'selected' : ''); ?>
                            >
                                Inactive
                            </option>

                        </select>

                    </div>


                    <!-- Buttons -->

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="fa-solid fa-floppy-disk"></i>

                        Update New Arrival

                    </button>


                    <a
                        href="<?php echo ADMIN_URL; ?>new-arrivals.php"
                        class="btn btn-secondary"
                    >

                        Cancel

                    </a>

                </form>

            </div>

        </div>

    </div>

</main>

<?php require_once(__DIR__ . '/includes/footer.php'); ?>