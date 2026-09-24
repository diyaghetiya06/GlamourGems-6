<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/includes/functions.php');

$page_title = "Add New Arrival";

$upload_dir = __DIR__ . '/../assets/images/arrivals/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}


/*======================================
        ADD NEW ARRIVAL
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

    if (!in_array($status, ['active', 'inactive'])) {
        $status = 'active';
    }


    /*======================================
            IMAGE VALIDATION
    ======================================*/

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {

        $errors[] = "Please select an image.";

    } else {

        $allowed_types = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        $file_type = mime_content_type($_FILES['image']['tmp_name']);
        $file_size = $_FILES['image']['size'];

        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Only JPG, PNG and WEBP images are allowed.";
        }

        if ($file_size > 5 * 1024 * 1024) {
            $errors[] = "Image size must be less than 5MB.";
        }
    }


    /*======================================
            INSERT NEW ARRIVAL
    ======================================*/

    if (empty($errors)) {

        $original_name = $_FILES['image']['name'];

        $extension = strtolower(
            pathinfo($original_name, PATHINFO_EXTENSION)
        );

        $image_name =
            'arrival_' .
            time() .
            '_' .
            uniqid() .
            '.' .
            $extension;

        $image_path = $upload_dir . $image_name;


        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO new_arrivals
                (title, price, old_price, image, display_order, status)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sddsis",
                $title,
                $price,
                $old_price,
                $image_name,
                $display_order,
                $status
            );


            if (mysqli_stmt_execute($stmt)) {

                set_flash(
                    'success',
                    'New Arrival added successfully.'
                );

                mysqli_stmt_close($stmt);

                header(
                    "Location: " .
                    ADMIN_URL .
                    "new-arrivals.php"
                );

                exit;

            } else {

                unlink($image_path);

                $errors[] =
                    "Failed to add New Arrival. Please try again.";

                mysqli_stmt_close($stmt);
            }

        } else {

            $errors[] = "Failed to upload image.";
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

                <h1>Add New Arrival</h1>

                <p class="text-muted">
                    Add a new product to the New Arrivals section.
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
                ADD FORM
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
                            placeholder="Enter New Arrival title"
                            value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
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
                            placeholder="Enter new price"
                            value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>"
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
                            placeholder="Enter old price"
                            value="<?php echo htmlspecialchars($_POST['old_price'] ?? ''); ?>"
                            required
                        >

                    </div>


                    <!-- Image -->

                    <div class="mb-3">

                        <label class="form-label">
                            Image
                        </label>

                        <input
                            type="file"
                            name="image"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp"
                            required
                        >

                        <small class="text-muted">
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
                            value="<?php echo htmlspecialchars($_POST['display_order'] ?? '0'); ?>"
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

                            <option value="active">
                                Active
                            </option>

                            <option value="inactive">
                                Inactive
                            </option>

                        </select>

                    </div>


                    <!-- Submit -->

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Add New Arrival
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

<style>

/*======================================
        ADD NEW ARRIVAL THEME
======================================*/

.main-content {
    background: #0b1210;
    color: #ffffff;
    min-height: 100vh;
}

/*======================================
        PAGE HEADER
======================================*/

.main-content h1 {
    color: #C8A165;
    font-family: Cinzel, Georgia, serif;
    font-weight: 600;
}

.main-content p.text-muted {
    color: #bdbdbd !important;
}

/*======================================
        BACK BUTTON
======================================*/

.main-content .btn-secondary {
    background: #18231f;
    border: 1px solid #3d4a43;
    color: #ffffff;
}

.main-content .btn-secondary:hover {
    background: #C8A165;
    border-color: #C8A165;
    color: #111;
}

/*======================================
        FORM CARD
======================================*/

.main-content .card {
    background: #111916;
    border: 1px solid #3d4a43;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
}

.main-content .card-body {
    padding: 30px;
}

/*======================================
        FORM LABELS
======================================*/

.main-content .form-label {
    color: #ffffff;
    font-weight: 500;
    margin-bottom: 8px;
}

.main-content .form-label::after {
    content: "";
}

/*======================================
        FORM INPUTS
======================================*/

.main-content .form-control,
.main-content .form-select {
    background: #0b1210 !important;
    border: 1px solid #3d4a43 !important;
    color: #ffffff !important;
    border-radius: 8px;
    padding: 12px 14px;
}

.main-content .form-control::placeholder {
    color: #777f7b;
}

.main-content .form-control:focus,
.main-content .form-select:focus {
    background: #0b1210 !important;
    color: #ffffff !important;
    border-color: #C8A165 !important;
    box-shadow: 0 0 0 0.15rem rgba(200, 161, 101, 0.15) !important;
}

/*======================================
        FILE INPUT
======================================*/

.main-content input[type="file"] {
    color: #ffffff !important;
}

.main-content input[type="file"]::file-selector-button {
    background: #C8A165;
    color: #111;
    border: none;
    padding: 9px 14px;
    margin-right: 12px;
    font-weight: 600;
    cursor: pointer;
}

.main-content input[type="file"]::file-selector-button:hover {
    background: #d8b678;
}

/*======================================
        SELECT OPTION
======================================*/

.main-content .form-select option {
    background: #111916;
    color: #ffffff;
}

/*======================================
        HELP TEXT
======================================*/

.main-content small.text-muted {
    color: #929b96 !important;
}

/*======================================
        ADD BUTTON
======================================*/

.main-content .btn-primary {
    background: #C8A165;
    border: 1px solid #C8A165;
    color: #111;
    font-weight: 600;
    border-radius: 7px;
    padding: 11px 18px;
}

.main-content .btn-primary:hover {
    background: #d8b678;
    border-color: #d8b678;
    color: #111;
}

/*======================================
        ERROR MESSAGE
======================================*/

.main-content .alert-danger {
    background: #351818;
    border: 1px solid #7d3030;
    color: #ffb4b4;
    border-radius: 8px;
}

/*======================================
        RESPONSIVE
======================================*/

@media (max-width: 768px) {

    .main-content .card-body {
        padding: 20px;
    }

    .main-content h1 {
        font-size: 26px;
    }

}

</style>

<?php require_once(__DIR__ . '/includes/footer.php'); ?>