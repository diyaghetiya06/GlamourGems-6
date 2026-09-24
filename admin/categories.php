<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Category Management';

$error = '';
$success = "";
$edit_category = null;
/*======================================
        IMAGE UPLOAD SETTINGS
======================================*/

$upload_dir = __DIR__ . '/../assets/images/recipient/';

$allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
$max_file_size = 5 * 1024 * 1024;

/*======================================
        ADD / UPDATE CATEGORY
======================================*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $status = sanitize($_POST['status'] ?? 'active');

    $slug = create_slug($name);

    if (empty($name)) {

        $error = 'Category name is required.';

    } elseif (!in_array($status, ['active', 'inactive'])) {

        $error = 'Invalid category status.';

    } else {

        $image_name = '';


        /*======================================
                HANDLE IMAGE UPLOAD
        ======================================*/

        if (
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

                    $error = 'Only JPG, JPEG, PNG and WEBP images are allowed.';

                } else {

                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    $image_name =
                        'category_' .
                        time() .
                        '_' .
                        uniqid() .
                        '.' .
                        $extension;

                    $target_file = $upload_dir . $image_name;

                    if (
                        !move_uploaded_file(
                            $_FILES['image']['tmp_name'],
                            $target_file
                        )
                    ) {

                        $error = 'Failed to save category image.';
                    }
                }
            }
        }


        /*======================================
                SAVE CATEGORY
        ======================================*/

        if (empty($error)) {

            if ($category_id > 0) {

                /* Get old image */
                $old_stmt = mysqli_prepare(
                    $conn,
                    "SELECT image FROM categories WHERE id = ? LIMIT 1"
                );

                mysqli_stmt_bind_param(
                    $old_stmt,
                    "i",
                    $category_id
                );

                mysqli_stmt_execute($old_stmt);

                $old_res = mysqli_stmt_get_result($old_stmt);
                $old_category = mysqli_fetch_assoc($old_res);

                mysqli_stmt_close($old_stmt);


                /* Update with new image */
                if (!empty($image_name)) {

                    $stmt = mysqli_prepare(
                        $conn,
                        "UPDATE categories
                         SET name = ?, slug = ?, description = ?, image = ?, status = ?
                         WHERE id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "sssssi",
                        $name,
                        $slug,
                        $description,
                        $image_name,
                        $status,
                        $category_id
                    );

                } else {

                    $stmt = mysqli_prepare(
                        $conn,
                        "UPDATE categories
                         SET name = ?, slug = ?, description = ?, status = ?
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


                if (mysqli_stmt_execute($stmt)) {

                    /* Delete old image only after successful update */
                    if (
                        !empty($image_name) &&
                        !empty($old_category['image']) &&
                        $old_category['image'] !== 'category_default.jpg'
                    ) {

                        $old_image =
                            $upload_dir .
                            basename($old_category['image']);

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

                    /* Remove newly uploaded image if DB update failed */
                    if (!empty($image_name)) {

                        $new_image =
                            $upload_dir .
                            basename($image_name);

                        if (file_exists($new_image)) {
                            unlink($new_image);
                        }
                    }

                    $error =
                        'Failed to update category: ' .
                        mysqli_error($conn);

                    mysqli_stmt_close($stmt);
                }


            } else {

                /*======================================
                        INSERT NEW CATEGORY
                ======================================*/

                if (empty($image_name)) {
                    $image_name = 'category_default.jpg';
                }

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO categories
                    (name, slug, description, image, status)
                    VALUES (?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssss",
                    $name,
                    $slug,
                    $description,
                    $image_name,
                    $status
                );


                if (mysqli_stmt_execute($stmt)) {

                    set_flash(
                        'success',
                        'Category "' .
                        htmlspecialchars($name) .
                        '" created successfully.'
                    );

                    mysqli_stmt_close($stmt);

                    header(
                        'Location: ' .
                        ADMIN_URL .
                        'categories.php'
                    );

                    exit;

                } else {

                    /* Remove uploaded image if insert failed */
                    if (!empty($image_name)) {

                        $new_image =
                            $upload_dir .
                            basename($image_name);

                        if (file_exists($new_image)) {
                            unlink($new_image);
                        }
                    }

                    $error =
                        'Failed to create category: ' .
                        mysqli_error($conn);

                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}


/*======================================
        FETCH ALL CATEGORIES
======================================*/

$sql = "SELECT c.*, COUNT(p.id) AS total_products
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id
        GROUP BY c.id
        ORDER BY c.id ASC";

$categories = [];

$res = mysqli_query($conn, $sql);

if ($res) {

    while ($row = mysqli_fetch_assoc($res)) {
        $categories[] = $row;
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
            Categories
        </h2>

        <p class="page-header-sub mb-0">
            Manage jewellery recipient categories and images.
        </p>

    </div>

</div>


<?php if (!empty($error)): ?>

    <div class="alert alert-danger alert-dismissible fade show" role="alert">

        <i class="fa-solid fa-circle-exclamation me-2"></i>

        <?php echo htmlspecialchars($error); ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Close">
        </button>

    </div>

<?php endif; ?>


<div class="row g-4">


    <!--======================================
            CATEGORY FORM
    ======================================-->

    <div class="col-12 col-lg-4">

        <div class="gg-card">

            <div class="gg-card-header">

                <h5 class="gg-card-title">

                    <i class="fa-solid fa-tags"></i>

                    <?php
                    echo $edit_category
                        ? 'Edit Category'
                        : 'Add Category';
                    ?>

                </h5>

            </div>


            <div class="gg-card-body">

                <form
                    action=""
                    method="POST"
                    enctype="multipart/form-data"
                >

                    <input
                        type="hidden"
                        name="category_id"
                        value="<?php echo $edit_category['id'] ?? 0; ?>"
                    >


                    <!-- CATEGORY NAME -->

                    <div class="mb-3">

                        <label
                            for="name"
                            class="form-label"
                        >
                            Category Name
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="name"
                            name="name"
                            placeholder="e.g. Couple"
                            required
                            value="<?php echo htmlspecialchars($edit_category['name'] ?? ''); ?>"
                        >

                    </div>


                    <!-- DESCRIPTION -->

                    <div class="mb-3">

                        <label
                            for="description"
                            class="form-label"
                        >
                            Description
                        </label>

                        <textarea
                            class="form-control"
                            id="description"
                            name="description"
                            rows="3"
                            placeholder="Brief summary of category..."
                        ><?php echo htmlspecialchars($edit_category['description'] ?? ''); ?></textarea>

                    </div>


                    <!-- IMAGE -->

                    <div class="mb-3">

                        <label
                            for="image"
                            class="form-label"
                        >
                            Category Image
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


                    <?php if (!empty($edit_category['image'])): ?>

                        <div class="mb-3">

                            <label class="form-label">
                                Current Image
                            </label>

                            <div>

                                <img
                                    src="../assets/images/recipient/<?php echo htmlspecialchars($edit_category['image']); ?>"
                                    alt="<?php echo htmlspecialchars($edit_category['name']); ?>"
                                    style="
                                        width:100%;
                                        max-width:180px;
                                        height:110px;
                                        object-fit:cover;
                                        border-radius:10px;
                                        border:1px solid rgba(200,161,101,.35);
                                    "
                                >

                            </div>

                        </div>

                    <?php endif; ?>


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

                            <option
                                value="active"
                                <?php echo (($edit_category['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                <?php echo (($edit_category['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>
                            >
                                Inactive
                            </option>

                        </select>

                    </div>


                    <!-- BUTTONS -->

                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-gg-gold w-100 py-2"
                        >

                            <i class="fa-solid fa-save me-1"></i>

                            <?php
                            echo $edit_category
                                ? 'Update Category'
                                : 'Save Category';
                            ?>

                        </button>


                        <?php if ($edit_category): ?>

                            <a
                                href="<?php echo ADMIN_URL; ?>categories.php"
                                class="btn btn-gg-outline"
                            >
                                Cancel
                            </a>

                        <?php endif; ?>

                    </div>

                </form>

            </div>

        </div>

    </div>


    <!--======================================
            CATEGORY LIST
    ======================================-->

    <div class="col-12 col-lg-8">

        <div class="gg-card">

            <div class="gg-card-header">

                <h5 class="gg-card-title">

                    <i class="fa-solid fa-list"></i>

                    Existing Categories
                    (<?php echo count($categories); ?>)

                </h5>

            </div>


            <div class="gg-card-body p-0">

                <div class="table-responsive">

                    <table class="table table-gg align-middle mb-0">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Image</th>

                                <th>Category Name</th>

                                <th>Slug</th>

                                <th>Products</th>

                                <th>Status</th>

                                <th class="text-end">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (!empty($categories)): ?>

                                <?php foreach ($categories as $cat): ?>

                                    <tr>

                                        <td>
                                            #<?php echo $cat['id']; ?>
                                        </td>


                                        <!-- IMAGE -->

                                        <td>

                                            <img
                                                src="../assets/images/recipient/<?php echo htmlspecialchars($cat['image'] ?: 'category_default.jpg'); ?>"
                                                alt="<?php echo htmlspecialchars($cat['name']); ?>"
                                                style="
                                                    width:60px;
                                                    height:50px;
                                                    object-fit:cover;
                                                    border-radius:8px;
                                                "
                                            >

                                        </td>


                                        <!-- NAME -->

                                        <td>

                                            <div class="fw-semibold text-light">

                                                <?php
                                                echo htmlspecialchars(
                                                    $cat['name']
                                                );
                                                ?>

                                            </div>

                                            <small
                                                class="text-muted text-truncate d-block"
                                                style="max-width:220px;"
                                            >

                                                <?php
                                                echo htmlspecialchars(
                                                    $cat['description']
                                                );
                                                ?>

                                            </small>

                                        </td>


                                        <!-- SLUG -->

                                        <td>

                                            <code class="text-gold">

                                                <?php
                                                echo htmlspecialchars(
                                                    $cat['slug']
                                                );
                                                ?>

                                            </code>

                                        </td>


                                        <!-- PRODUCTS -->

                                        <td>

                                            <span class="badge bg-dark border border-secondary text-info">

                                                <i class="fa-solid fa-gem me-1"></i>

                                                <?php
                                                echo $cat['total_products'];
                                                ?>

                                                Items

                                            </span>

                                        </td>


                                        <!-- STATUS -->

                                        <td>

                                            <?php if ($cat['status'] === 'active'): ?>

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
                                                    href="<?php echo ADMIN_URL; ?>edit-category.php?id=<?php echo $cat['id']; ?>"
                                                    class="btn-action"
                                                    title="Edit Category"
                                                >
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>


                                                <a
                                                    href="<?php echo ADMIN_URL; ?>delete-category.php?id=<?php echo $cat['id']; ?>"
                                                    class="btn-action btn-action-danger"
                                                    title="Delete Category"
                                                    onclick="return confirm('Delete category <?php echo htmlspecialchars($cat['name']); ?>?');"
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
                                        colspan="7"
                                        class="text-center py-4 text-muted"
                                    >
                                        No categories created yet.
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