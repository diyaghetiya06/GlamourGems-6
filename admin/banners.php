<?php
require_once "includes/auth_check.php";
require_once "includes/header.php";
require_once "../config/database.php";
require_once "includes/functions.php";

/*======================================
        TOGGLE BANNER STATUS
======================================*/
if (isset($_GET['toggle'])) {

    $id = (int) $_GET['toggle'];

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE banners SET status = IF(status = 1, 0, 1) WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        set_flash("Banner status updated.", "success");
    } else {
        set_flash("Unable to update banner status.", "danger");
    }

    mysqli_stmt_close($stmt);

    header("Location: banners.php");
    exit;
}


/*======================================
        FETCH ALL BANNERS
======================================*/
$result = mysqli_query(
    $conn,
    "SELECT * FROM banners ORDER BY display_order ASC, id DESC"
);

?>

<!--======================================
            BANNERS PAGE
======================================-->

<div class="container-fluid banners-page">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold mb-1">Banners</h2>
            <p class="text-muted mb-0">
                Manage homepage slider banners
            </p>
        </div>

        <a href="add-banner.php" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i>
            Add New Banner
        </a>

    </div>


    <!--======================================
                BANNERS TABLE
    ======================================-->

    <div class="card shadow-sm border-0">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead>
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Banner</th>
                            <th>Heading</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (mysqli_num_rows($result) > 0): ?>

                        <?php while ($banner = mysqli_fetch_assoc($result)): ?>

                            <tr>

                                <!-- ID -->
                                <td class="ps-4">
                                    <?php echo $banner['id']; ?>
                                </td>


                                <!-- Banner Image -->
                                <td>

                                   <img
                                    src="../uploads/banners/<?php echo htmlspecialchars($banner['image']); ?>"
                                    alt="<?php echo htmlspecialchars($banner['title']); ?>"
                                    class="banner-image"
                                >

                                </td>


                                <!-- Heading -->
                                <td>

                                    <div class="fw-semibold">
                                        <?php echo htmlspecialchars($banner['heading']); ?>
                                    </div>

                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($banner['title']); ?>
                                    </small>

                                </td>


                                <!-- Display Order -->
                                <td>
                                    <span class="order-badge">
                                        <?php echo $banner['display_order']; ?>
                                    </span>
                                </td>


                                <!-- Status -->
                                <td>

                                    <?php if ($banner['status'] == 1): ?>

                                        <a
                                            href="banners.php?toggle=<?php echo $banner['id']; ?>"
                                            class="badge bg-success text-decoration-none"
                                        >
                                            Active
                                        </a>

                                    <?php else: ?>

                                        <a
                                            href="banners.php?toggle=<?php echo $banner['id']; ?>"
                                            class="badge bg-secondary text-decoration-none"
                                        >
                                            Inactive
                                        </a>

                                    <?php endif; ?>

                                </td>

                                     <!-- Actions -->
                                        <td class="text-end pe-4">

                                            <div class="d-inline-flex gap-1">

                                                <!-- EDIT BANNER -->
                                                <a
                                                    href="edit-banner.php?id=<?php echo $banner['id']; ?>"
                                                    class="btn-action"
                                                    title="Edit Banner"
                                                >
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>

                                                <!-- DELETE BANNER -->
                                                <a
                                                    href="delete-banner.php?id=<?php echo $banner['id']; ?>"
                                                    class="btn-action btn-action-danger"
                                                    title="Delete Banner"
                                                    onclick="return confirm('Are you sure you want to delete this banner?');"
                                                >
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>

                                            </div>

                                        </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="6" class="text-center py-5">

                                <div class="text-muted">

                                    <i
                                        class="fa-solid fa-images fa-3x mb-3"
                                    ></i>

                                    <h5>No banners found</h5>

                                    <p class="mb-3">
                                        Add your first homepage banner.
                                    </p>

                                    <a
                                        href="add-banner.php"
                                        class="btn btn-primary"
                                    >
                                        <i class="fa-solid fa-plus me-2"></i>
                                        Add Banner
                                    </a>

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

<?php require_once "includes/footer.php"; ?>

<style>

/*======================================
        BANNERS PAGE DARK THEME
======================================*/

.banners-page {
    color: #f5f5f5;
}

/*======================================
        PAGE HEADER
======================================*/

.banners-page h2 {
    color: #C8A165;
    font-family: "Cinzel", serif;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.banners-page .text-muted {
    color: #9b9b9b !important;
}

/*======================================
        ADD NEW BANNER BUTTON
======================================*/

.banners-page .btn-primary {
    background: #C8A165;
    border-color: #C8A165;
    color: #111;
    font-weight: 600;
}

.banners-page .btn-primary:hover {
    background: #b38c50;
    border-color: #b38c50;
    color: #111;
}

/*======================================
        BANNERS CARD
======================================*/

.banners-page .card {
    background: #151a19;
    border: 1px solid #2d3533 !important;
    border-radius: 10px;
    overflow: hidden;
}

/*======================================
        TABLE
======================================*/

.banners-page .table {
    --bs-table-bg: #151a19;
    --bs-table-color: #f1f1f1;
    --bs-table-hover-bg: #1d2523;
    --bs-table-hover-color: #ffffff;

    margin-bottom: 0;
}

.banners-page .table thead {
    background: #101514;
}

.banners-page .table thead th {
    color: #ffffff;
    border-bottom: 1px solid #303937;
    font-weight: 600;
    padding-top: 16px;
    padding-bottom: 16px;
}

.banners-page .table tbody td {
    color: #eeeeee;
    border-color: #303937;
    background: #151a19;
}

.banners-page .table tbody tr:hover td {
    background: #1d2523;
}

/*======================================
        BANNER IMAGE
======================================*/

.banners-page .banner-image {
    width: 140px;
    height: 70px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #3a403e;
}

/*======================================
        ORDER BADGE
======================================*/

.banners-page .order-badge {
    background: #1b2321;
    color: #f1f1f1;
    border: 1px solid #3c4542;
    padding: 5px 9px;
    border-radius: 6px;
}

/*======================================
        EDIT BUTTON
======================================*/

.banners-page .btn-outline-primary {
    color: #4da3ff;
    border-color: #4da3ff;
}

.banners-page .btn-outline-primary:hover {
    background: #4da3ff;
    color: #111;
}

/*======================================
        DELETE BUTTON
======================================*/

.banners-page .btn-outline-danger {
    color: #ff4d5a;
    border-color: #ff4d5a;
}

.banners-page .btn-outline-danger:hover {
    background: #ff4d5a;
    color: #fff;
}

/*======================================
        EMPTY STATE
======================================*/

.banners-page .empty-state {
    color: #999;
}

/*======================================
        ACTION BUTTONS
======================================*/

.banners-page .btn-action {
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;

    background: #1b2321;
    border: 1px solid #3c4542;
    border-radius: 6px;

    color: #C8A165;
    text-decoration: none;

    transition: 0.3s ease;
}

.banners-page .btn-action:hover {
    background: #C8A165;
    border-color: #C8A165;
    color: #111;
}

.banners-page .btn-action-danger {
    color: #ff4d5a;
}

.banners-page .btn-action-danger:hover {
    background: #ff4d5a;
    border-color: #ff4d5a;
    color: #fff;
}


</style>