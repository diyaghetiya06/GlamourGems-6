<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/includes/functions.php');

$page_title = "Journey Numbers";

$edit_number = null;
$errors = [];


/*======================================
        DELETE JOURNEY NUMBER
======================================*/

if (isset($_GET['delete'])) {

    $delete_id = (int)$_GET['delete'];

    if ($delete_id > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM journey_numbers WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        set_flash(
            'success',
            'Journey number deleted successfully.'
        );

        header("Location: " . ADMIN_URL . "journey-numbers.php");
        exit;
    }
}


/*======================================
        GET JOURNEY NUMBER FOR EDIT
======================================*/

if (isset($_GET['edit'])) {

    $edit_id = (int)$_GET['edit'];

    if ($edit_id > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT *
             FROM journey_numbers
             WHERE id = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "i", $edit_id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $edit_number = mysqli_fetch_assoc($result);
        }

        mysqli_stmt_close($stmt);
    }
}


/*======================================
        ADD / UPDATE JOURNEY NUMBER
======================================*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = (int)($_POST['id'] ?? 0);

    $icon = trim($_POST['icon'] ?? '');
    $number_value = trim($_POST['number_value'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';


    /*======================================
            VALIDATION
    ======================================*/

    if ($icon === '') {
        $errors[] = "Icon is required.";
    }

    if ($number_value === '') {
        $errors[] = "Number is required.";
    }

    if ($title === '') {
        $errors[] = "Title is required.";
    }

    if (!in_array($status, ['active', 'inactive'])) {
        $status = 'active';
    }


    /*======================================
            UPDATE
    ======================================*/

    if (empty($errors) && $id > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE journey_numbers
             SET icon = ?,
                 number_value = ?,
                 title = ?,
                 display_order = ?,
                 status = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssisi",
            $icon,
            $number_value,
            $title,
            $display_order,
            $status,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {

            set_flash(
                'success',
                'Journey number updated successfully.'
            );

            mysqli_stmt_close($stmt);

            header(
                "Location: " .
                ADMIN_URL .
                "journey-numbers.php"
            );

            exit;

        } else {

            $errors[] = "Failed to update journey number.";
        }

        mysqli_stmt_close($stmt);
    }


    /*======================================
            INSERT
    ======================================*/

    if (empty($errors) && $id === 0) {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO journey_numbers
            (icon, number_value, title, display_order, status)
            VALUES (?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssis",
            $icon,
            $number_value,
            $title,
            $display_order,
            $status
        );

        if (mysqli_stmt_execute($stmt)) {

            set_flash(
                'success',
                'Journey number added successfully.'
            );

            mysqli_stmt_close($stmt);

            header(
                "Location: " .
                ADMIN_URL .
                "journey-numbers.php"
            );

            exit;

        } else {

            $errors[] = "Failed to add journey number.";
        }

        mysqli_stmt_close($stmt);
    }
}


/*======================================
        FETCH JOURNEY NUMBERS
======================================*/

$journey_query = mysqli_query(
    $conn,
    "SELECT *
     FROM journey_numbers
     ORDER BY display_order ASC, id ASC"
);


/*======================================
        HEADER
======================================*/

require_once(__DIR__ . '/includes/header.php');
require_once(__DIR__ . '/includes/sidebar.php');

?>

<div class="admin-page-content">

    <!--======================================
            PAGE HEADER
    ======================================-->

    <div class="page-header">

        <div>

            <h1 class="page-header-title">
                Journey Numbers
            </h1>

            <p class="page-header-subtitle">
                Manage the statistics displayed in "Our Journey in Numbers".
            </p>

        </div>

    </div>


    <!--======================================
            ERROR MESSAGE
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


    <div class="row g-4">


        <!--======================================
                ADD / EDIT FORM
        ======================================-->

        <div class="col-lg-4">

            <div class="gg-card">

                <div class="gg-card-header">

                    <h3>
                        <?php
                        echo $edit_number
                            ? 'Edit Journey Number'
                            : 'Add Journey Number';
                        ?>
                    </h3>

                </div>


                <div class="gg-card-body">

                    <form method="POST">

                        <input
                            type="hidden"
                            name="id"
                            value="<?php echo $edit_number['id'] ?? 0; ?>"
                        >


                        <!-- Icon -->

                        <div class="mb-3">

                            <label class="form-label">
                                Font Awesome Icon
                            </label>

                            <input
                                type="text"
                                name="icon"
                                class="form-control"
                                placeholder="fa-solid fa-gem"
                                value="<?php echo htmlspecialchars($edit_number['icon'] ?? ''); ?>"
                                required
                            >

                            <small>
                                Example: fa-solid fa-gem
                            </small>

                        </div>


                        <!-- Number -->

                        <div class="mb-3">

                            <label class="form-label">
                                Number
                            </label>

                            <input
                                type="text"
                                name="number_value"
                                class="form-control"
                                placeholder="25+"
                                value="<?php echo htmlspecialchars($edit_number['number_value'] ?? ''); ?>"
                                required
                            >

                        </div>


                        <!-- Title -->

                        <div class="mb-3">

                            <label class="form-label">
                                Title
                            </label>

                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                placeholder="Years Experience"
                                value="<?php echo htmlspecialchars($edit_number['title'] ?? ''); ?>"
                                required
                            >

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
                                value="<?php echo htmlspecialchars($edit_number['display_order'] ?? 0); ?>"
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
                                    <?php echo (($edit_number['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>
                                >
                                    Active
                                </option>

                                <option
                                    value="inactive"
                                    <?php echo (($edit_number['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>
                                >
                                    Inactive
                                </option>

                            </select>

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="fa-solid fa-save"></i>

                            <?php
                            echo $edit_number
                                ? 'Update Number'
                                : 'Add Number';
                            ?>

                        </button>


                        <?php if ($edit_number): ?>

                            <a
                                href="<?php echo ADMIN_URL; ?>journey-numbers.php"
                                class="btn btn-secondary"
                            >
                                Cancel
                            </a>

                        <?php endif; ?>

                    </form>

                </div>

            </div>

        </div>


        <!--======================================
                EXISTING NUMBERS
        ======================================-->

        <div class="col-lg-8">

            <div class="gg-card">

                <div class="gg-card-header">

                    <h3>
                        Existing Journey Numbers
                    </h3>

                </div>


                <div class="gg-card-body">

                    <div class="table-responsive">

                        <table class="table gg-table align-middle">

                            <thead>

                                <tr>

                                    <th>#</th>
                                    <th>Icon</th>
                                    <th>Number</th>
                                    <th>Title</th>
                                    <th>Order</th>
                                    <th>Status</th>

                                    <th class="text-end">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php if ($journey_query && mysqli_num_rows($journey_query) > 0): ?>

                                    <?php while ($journey = mysqli_fetch_assoc($journey_query)): ?>

                                        <tr>

                                            <td>
                                                <?php echo (int)$journey['id']; ?>
                                            </td>


                                            <td>

                                                <i
                                                    class="<?php echo htmlspecialchars($journey['icon']); ?>"
                                                ></i>

                                            </td>


                                            <td>

                                                <strong>
                                                    <?php echo htmlspecialchars($journey['number_value']); ?>
                                                </strong>

                                            </td>


                                            <td>
                                                <?php echo htmlspecialchars($journey['title']); ?>
                                            </td>


                                            <td>
                                                <?php echo (int)$journey['display_order']; ?>
                                            </td>


                                            <td>

                                                <?php if ($journey['status'] === 'active'): ?>

                                                    <span class="badge bg-success">
                                                        Active
                                                    </span>

                                                <?php else: ?>

                                                    <span class="badge bg-secondary">
                                                        Inactive
                                                    </span>

                                                <?php endif; ?>

                                            </td>


                                            <!--======================================
                                                    ACTIONS
                                            ======================================-->

                                            <td class="text-end">

                                                <div class="d-inline-flex gap-1">

                                                    <a
                                                        href="<?php echo ADMIN_URL; ?>journey-numbers.php?edit=<?php echo $journey['id']; ?>"
                                                        class="btn-action"
                                                        title="Edit"
                                                    >

                                                        <i class="fa-solid fa-pen-to-square"></i>

                                                    </a>


                                                    <a
                                                        href="<?php echo ADMIN_URL; ?>journey-numbers.php?delete=<?php echo $journey['id']; ?>"
                                                        class="btn-action btn-action-danger"
                                                        title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete this journey number?');"
                                                    >

                                                        <i class="fa-solid fa-trash"></i>

                                                    </a>

                                                </div>

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <tr>

                                        <td
                                            colspan="7"
                                            class="text-center"
                                        >
                                            No journey numbers found.
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

</div>


<style>

/*======================================
        JOURNEY NUMBERS PAGE
======================================*/

.admin-page-content {
    padding: 30px;
    min-height: calc(100vh - 70px);
}


/*======================================
        PAGE HEADER
======================================*/

.page-header {
    margin-bottom: 30px;
}

.page-header-title {
    color: #C8A165 !important;
    font-family: Cinzel, Georgia, serif;
    font-size: 30px;
    font-weight: 600;
    margin-bottom: 8px;
}

.page-header-subtitle {
    color: #9da6a1 !important;
    margin: 0;
}


/*======================================
        CARD
======================================*/

.gg-card {
    background: #111916 !important;
    border: 1px solid #29352f !important;
    border-radius: 14px !important;
    overflow: hidden;
}

.gg-card-header {
    padding: 20px 22px;
    border-bottom: 1px solid #29352f;
    background: #111916 !important;
}

.gg-card-header h3 {
    margin: 0;
    color: #C8A165 !important;
    font-family: Cinzel, Georgia, serif;
    font-size: 20px;
}

.gg-card-body {
    padding: 22px;
    background: #111916 !important;
}


/*======================================
        FORM
======================================*/

.gg-card .form-label {
    color: #ffffff !important;
    font-weight: 500;
}

.gg-card .form-control,
.gg-card .form-select {
    background: #0b1210 !important;
    color: #ffffff !important;
    border: 1px solid #29352f !important;
    border-radius: 8px !important;
}

.gg-card .form-control:focus,
.gg-card .form-select:focus {
    background: #0b1210 !important;
    color: #ffffff !important;
    border-color: #C8A165 !important;
    box-shadow: 0 0 0 0.15rem rgba(200, 161, 101, 0.15) !important;
}

.gg-card .form-control::placeholder {
    color: #78817c !important;
}

.gg-card small {
    color: #8b9690 !important;
}


/*======================================
        ADD BUTTON
======================================*/

.gg-card .btn-primary {
    background: #C8A165 !important;
    border: 1px solid #C8A165 !important;
    color: #111 !important;
    font-weight: 600;
    border-radius: 7px !important;
}

.gg-card .btn-primary:hover {
    background: #d6b477 !important;
    border-color: #d6b477 !important;
}


/*======================================
        CANCEL BUTTON
======================================*/

.gg-card .btn-secondary {
    background: #0b1210 !important;
    border: 1px solid #29352f !important;
    color: #ffffff !important;
    border-radius: 7px !important;
}


/*======================================
        TABLE
======================================*/

.gg-table {
    margin: 0 !important;
    background: #111916 !important;
    color: #ffffff !important;
}

.gg-table thead th {
    background: #0b1210 !important;
    color: #C8A165 !important;
    border-color: #29352f !important;
    white-space: nowrap;
}

.gg-table tbody tr,
.gg-table tbody td {
    background: #111916 !important;
    color: #ffffff !important;
    border-color: #29352f !important;
}

.gg-table tbody tr:hover,
.gg-table tbody tr:hover td {
    background: #18231f !important;
}

.gg-table tbody td i {
    color: #C8A165 !important;
    font-size: 20px;
}


/*======================================
        EDIT / DELETE BUTTONS
======================================*/

.gg-table .btn-action {
    width: 36px !important;
    height: 36px !important;

    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;

    background: #111916 !important;
    border: 1px solid #29352f !important;
    border-radius: 8px !important;

    color: #ffffff !important;
    text-decoration: none !important;

    transition: all 0.25s ease;
}

.gg-table .btn-action i {
    color: #ffffff !important;
    font-size: 14px !important;
}

.gg-table .btn-action:hover {
    background: #18231f !important;
    border-color: #52615a !important;
    color: #ffffff !important;
    transform: translateY(-2px);
}

.gg-table .btn-action-danger:hover {
    background: #351818 !important;
    border-color: #7d3030 !important;
}

.gg-table .btn-action-danger i {
    color: #ffffff !important;
}


/*======================================
        STATUS
======================================*/

.gg-table .badge.bg-success {
    background: #123f2c !important;
    color: #61d49a !important;
    border: 1px solid #267653 !important;
}

.gg-table .badge.bg-secondary {
    background: #343a40 !important;
    color: #d0d0d0 !important;
}


/*======================================
        RESPONSIVE
======================================*/

@media (max-width: 768px) {

    .admin-page-content {
        padding: 20px 15px;
    }

    .page-header-title {
        font-size: 25px;
    }

}

</style>

<?php require_once(__DIR__ . '/includes/footer.php'); ?>