<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Material Management';

$error = '';
$edit_material = null;

/*======================================
        IMAGE UPLOAD SETTINGS
======================================*/

$upload_dir = __DIR__ . '/../assets/images/materials/';

$allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
$max_file_size = 5 * 1024 * 1024;


/*======================================
        CHECK EDIT MATERIAL
======================================*/

if (isset($_GET['edit_id'])) {

    $edit_id = intval($_GET['edit_id']);

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM materials WHERE id = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $edit_id);
    mysqli_stmt_execute($stmt);

    $res = mysqli_stmt_get_result($stmt);
    $edit_material = mysqli_fetch_assoc($res);

    mysqli_stmt_close($stmt);
}


/*======================================
        DELETE MATERIAL
======================================*/

if (isset($_GET['delete_id'])) {

    $delete_id = intval($_GET['delete_id']);

    /* Get image before deleting */
    $stmt = mysqli_prepare(
        $conn,
        "SELECT image FROM materials WHERE id = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $delete_id);
    mysqli_stmt_execute($stmt);

    $res = mysqli_stmt_get_result($stmt);
    $material_to_delete = mysqli_fetch_assoc($res);

    mysqli_stmt_close($stmt);


    /* Delete material */
    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM materials WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $delete_id);

    if (mysqli_stmt_execute($stmt)) {

        /* Delete uploaded image */
        if (
            !empty($material_to_delete['image']) &&
            $material_to_delete['image'] !== 'material_default.jpg'
        ) {

            $old_image =
                $upload_dir .
                basename($material_to_delete['image']);

            if (file_exists($old_image)) {
                unlink($old_image);
            }
        }

        set_flash(
            'success',
            'Material deleted successfully.'
        );

    } else {

        set_flash(
            'danger',
            'Failed to delete material.'
        );
    }

    mysqli_stmt_close($stmt);

    header(
        'Location: ' .
        ADMIN_URL .
        'materials.php'
    );

    exit;
}


/*======================================
        ADD / UPDATE MATERIAL
======================================*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = sanitize($_POST['name'] ?? '');
    $status = sanitize($_POST['status'] ?? 'active');
    $material_id = intval($_POST['material_id'] ?? 0);

    $slug = create_slug($name);

    if (empty($name)) {

        $error = 'Material name is required.';

    } elseif (!in_array($status, ['active', 'inactive'])) {

        $error = 'Invalid material status.';

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

                $error =
                    'There was an error uploading the image.';

            } elseif (
                $_FILES['image']['size'] > $max_file_size
            ) {

                $error =
                    'Image size must be less than 5MB.';

            } else {

                $extension = strtolower(
                    pathinfo(
                        $_FILES['image']['name'],
                        PATHINFO_EXTENSION
                    )
                );

                if (!in_array(
                    $extension,
                    $allowed_extensions
                )) {

                    $error =
                        'Only JPG, JPEG, PNG and WEBP images are allowed.';

                } else {

                    if (!is_dir($upload_dir)) {

                        mkdir(
                            $upload_dir,
                            0755,
                            true
                        );
                    }

                    $image_name =
                        'material_' .
                        time() .
                        '_' .
                        uniqid() .
                        '.' .
                        $extension;

                    $target_file =
                        $upload_dir .
                        $image_name;

                    if (
                        !move_uploaded_file(
                            $_FILES['image']['tmp_name'],
                            $target_file
                        )
                    ) {

                        $error =
                            'Failed to save material image.';
                    }
                }
            }
        }


        /*======================================
                SAVE MATERIAL
        ======================================*/

        if (empty($error)) {

            /*==================================
                    UPDATE MATERIAL
            ==================================*/

            if ($material_id > 0) {

                /* Get old image */

                $old_stmt = mysqli_prepare(
                    $conn,
                    "SELECT image FROM materials
                     WHERE id = ?
                     LIMIT 1"
                );

                mysqli_stmt_bind_param(
                    $old_stmt,
                    "i",
                    $material_id
                );

                mysqli_stmt_execute($old_stmt);

                $old_res =
                    mysqli_stmt_get_result($old_stmt);

                $old_material =
                    mysqli_fetch_assoc($old_res);

                mysqli_stmt_close($old_stmt);


                /* Update with new image */

                if (!empty($image_name)) {

                    $stmt = mysqli_prepare(
                        $conn,
                        "UPDATE materials
                         SET name = ?,
                             slug = ?,
                             image = ?,
                             status = ?
                         WHERE id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssssi",
                        $name,
                        $slug,
                        $image_name,
                        $status,
                        $material_id
                    );

                } else {

                    $stmt = mysqli_prepare(
                        $conn,
                        "UPDATE materials
                         SET name = ?,
                             slug = ?,
                             status = ?
                         WHERE id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "sssi",
                        $name,
                        $slug,
                        $status,
                        $material_id
                    );
                }


                if (mysqli_stmt_execute($stmt)) {

                    /* Delete old image */

                    if (
                        !empty($image_name) &&
                        !empty($old_material['image']) &&
                        $old_material['image'] !== 'material_default.jpg'
                    ) {

                        $old_image =
                            $upload_dir .
                            basename(
                                $old_material['image']
                            );

                        if (file_exists($old_image)) {
                            unlink($old_image);
                        }
                    }

                    set_flash(
                        'success',
                        'Material "' .
                        htmlspecialchars($name) .
                        '" updated successfully.'
                    );

                    mysqli_stmt_close($stmt);

                    header(
                        'Location: ' .
                        ADMIN_URL .
                        'materials.php'
                    );

                    exit;

                } else {

                    /* Remove new image if update fails */

                    if (!empty($image_name)) {

                        $new_image =
                            $upload_dir .
                            basename($image_name);

                        if (file_exists($new_image)) {
                            unlink($new_image);
                        }
                    }

                    $error =
                        'Failed to update material: ' .
                        mysqli_error($conn);

                    mysqli_stmt_close($stmt);
                }


            } else {

                /*==================================
                        INSERT NEW MATERIAL
                ==================================*/

                if (empty($image_name)) {
                    $image_name =
                        'material_default.jpg';
                }

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO materials
                    (name, slug, image, status)
                    VALUES (?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "ssss",
                    $name,
                    $slug,
                    $image_name,
                    $status
                );


                if (mysqli_stmt_execute($stmt)) {

                    set_flash(
                        'success',
                        'Material "' .
                        htmlspecialchars($name) .
                        '" created successfully.'
                    );

                    mysqli_stmt_close($stmt);

                    header(
                        'Location: ' .
                        ADMIN_URL .
                        'materials.php'
                    );

                    exit;

                } else {

                    /* Remove uploaded image */

                    if (!empty($image_name)) {

                        $new_image =
                            $upload_dir .
                            basename($image_name);

                        if (file_exists($new_image)) {
                            unlink($new_image);
                        }
                    }

                    $error =
                        'Failed to create material: ' .
                        mysqli_error($conn);

                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}


/*======================================
        FETCH ALL MATERIALS
======================================*/

$materials = [];

$res = mysqli_query(
    $conn,
    "SELECT *
     FROM materials
     ORDER BY id ASC"
);

if ($res) {

    while ($row = mysqli_fetch_assoc($res)) {
        $materials[] = $row;
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
            Materials
        </h2>

        <p class="page-header-sub mb-0">
            Manage jewellery materials and images.
        </p>

    </div>

</div>


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


<div class="row g-4">


    <!--======================================
            MATERIAL FORM
    ======================================-->

    <div class="col-12 col-lg-4">

        <div class="gg-card">

            <div class="gg-card-header">

                <h5 class="gg-card-title">

                    <i class="fa-solid fa-gem"></i>

                    <?php
                    echo $edit_material
                        ? 'Edit Material'
                        : 'Add Material';
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
                        name="material_id"
                        value="<?php echo $edit_material['id'] ?? 0; ?>"
                    >


                    <!-- MATERIAL NAME -->

                    <div class="mb-3">

                        <label
                            for="name"
                            class="form-label"
                        >

                            Material Name

                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="name"
                            name="name"
                            placeholder="e.g. Platinum"
                            required
                            value="<?php echo htmlspecialchars($edit_material['name'] ?? ''); ?>"
                        >

                    </div>


                    <!-- IMAGE -->

                    <div class="mb-3">

                        <label
                            for="image"
                            class="form-label"
                        >
                            Material Image
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


                    <!-- CURRENT IMAGE -->

                    <?php if (!empty($edit_material['image'])): ?>

                        <div class="mb-3">

                            <label class="form-label">
                                Current Image
                            </label>

                            <div>

                                <img
                                    src="../assets/images/materials/<?php echo htmlspecialchars($edit_material['image']); ?>"
                                    alt="<?php echo htmlspecialchars($edit_material['name']); ?>"
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
                                <?php
                                echo (
                                    ($edit_material['status'] ?? 'active')
                                    === 'active'
                                )
                                ? 'selected'
                                : '';
                                ?>
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                <?php
                                echo (
                                    ($edit_material['status'] ?? '')
                                    === 'inactive'
                                )
                                ? 'selected'
                                : '';
                                ?>
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
                            echo $edit_material
                                ? 'Update Material'
                                : 'Save Material';
                            ?>

                        </button>


                        <?php if ($edit_material): ?>

                            <a
                                href="<?php echo ADMIN_URL; ?>materials.php"
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
            MATERIAL LIST
    ======================================-->

    <div class="col-12 col-lg-8">

        <div class="gg-card">

            <div class="gg-card-header">

                <h5 class="gg-card-title">

                    <i class="fa-solid fa-list"></i>

                    Existing Materials
                    (<?php echo count($materials); ?>)

                </h5>

            </div>


            <div class="gg-card-body p-0">

                <div class="table-responsive">

                    <table
                        class="table table-gg align-middle mb-0"
                    >

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Image</th>

                                <th>Material Name</th>

                                <th>Slug</th>

                                <th>Status</th>

                                <th class="text-end">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (!empty($materials)): ?>

                                <?php foreach ($materials as $material): ?>

                                    <tr>

                                        <td>
                                            #<?php echo $material['id']; ?>
                                        </td>


                                        <!-- IMAGE -->

                                        <td>

                                            <img
                                                src="../assets/images/materials/<?php echo htmlspecialchars($material['image'] ?: 'material_default.jpg'); ?>"
                                                alt="<?php echo htmlspecialchars($material['name']); ?>"
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
                                                    $material['name']
                                                );
                                                ?>

                                            </div>

                                        </td>


                                        <!-- SLUG -->

                                        <td>

                                            <code class="text-gold">

                                                <?php
                                                echo htmlspecialchars(
                                                    $material['slug']
                                                );
                                                ?>

                                            </code>

                                        </td>


                                        <!-- STATUS -->

                                        <td>

                                            <?php
                                            if (
                                                $material['status']
                                                === 'active'
                                            ):
                                            ?>

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
                                                    href="<?php echo ADMIN_URL; ?>materials.php?edit_id=<?php echo $material['id']; ?>"
                                                    class="btn-action"
                                                    title="Edit Material"
                                                >

                                                    <i class="fa-solid fa-pen-to-square"></i>

                                                </a>


                                                <a
                                                    href="<?php echo ADMIN_URL; ?>materials.php?delete_id=<?php echo $material['id']; ?>"
                                                    class="btn-action btn-action-danger"
                                                    title="Delete Material"
                                                    onclick="return confirm('Delete material <?php echo htmlspecialchars($material['name']); ?>?');"
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
                                        colspan="6"
                                        class="text-center py-4 text-muted"
                                    >
                                        No materials created yet.
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