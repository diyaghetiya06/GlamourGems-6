<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/includes/functions.php');

$page_title = "New Arrivals";

/*======================================
        FETCH NEW ARRIVALS
======================================*/

$new_arrivals_query = mysqli_query(
    $conn,
    "SELECT *
     FROM new_arrivals
     ORDER BY display_order ASC, id ASC"
);

require_once(__DIR__ . '/includes/header.php');
require_once(__DIR__ . '/includes/sidebar.php');

?>

<main class="main-content">

    <div class="container-fluid">

        <h1>New Arrivals</h1>

        <?php if ($new_arrivals_query && mysqli_num_rows($new_arrivals_query) > 0): ?>

            <p>
                <?php echo mysqli_num_rows($new_arrivals_query); ?>
                New Arrivals found.
            </p>

        <?php else: ?>

            <p>No New Arrivals found.</p>

        <?php endif; ?>
        
        <!--======================================
                NEW ARRIVALS TABLE
        ======================================-->

        <div class="card mt-4">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <h3 class="mb-0">All New Arrivals</h3>

                    <a
                        href="<?php echo ADMIN_URL; ?>add-new-arrival.php"
                        class="btn btn-primary"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Add New Arrival
                    </a>

                </div>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>New Price</th>
                                <th>Old Price</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php while ($arrival = mysqli_fetch_assoc($new_arrivals_query)): ?>

                                <tr>

                                    <td>
                                        <img
                                            src="<?php echo BASE_URL; ?>assets/images/arrivals/<?php echo htmlspecialchars($arrival['image']); ?>"
                                            alt="<?php echo htmlspecialchars($arrival['title']); ?>"
                                            style="width:70px; height:70px; object-fit:cover; border-radius:8px;"
                                        >
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($arrival['title']); ?>
                                    </td>

                                    <td>
                                        ₹<?php echo formatIndianPrice($arrival['price']); ?>
                                    </td>

                                    <td>
                                        ₹<?php echo formatIndianPrice($arrival['old_price']); ?>
                                    </td>

                                    <td>
                                        <?php echo (int)$arrival['display_order']; ?>
                                    </td>

                                    <td>
                                        <?php if ($arrival['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-end">

                                        <div class="d-inline-flex gap-1">

                                            <a
                                                href="<?php echo ADMIN_URL; ?>edit-new-arrival.php?id=<?php echo $arrival['id']; ?>"
                                                class="btn-action"
                                                title="Edit New Arrival"
                                            >
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>

                                            <a
                                                href="<?php echo ADMIN_URL; ?>delete-new-arrival.php?id=<?php echo $arrival['id']; ?>"
                                                class="btn-action btn-action-danger"
                                                title="Delete New Arrival"
                                                onclick="return confirm('Are you sure you want to delete this New Arrival?');"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


    </div>

</main>

<style>

/*======================================
        NEW ARRIVALS ADMIN THEME
======================================*/

.main-content {
    background: #0b1210;
    color: #ffffff;
    min-height: 100vh;
}

/*======================================
        PAGE HEADING
======================================*/

.main-content h1 {
    color: #C8A165;
    font-family: Cinzel, Georgia, serif;
    font-weight: 600;
}

.main-content > .container-fluid > p {
    color: #bdbdbd;
}

/*======================================
        NEW ARRIVALS CARD
======================================*/

.main-content .card {
    background: #111916;
    border: 1px solid #3d4a43;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
}

.main-content .card-body {
    padding: 25px;
}

/*======================================
        CARD HEADING
======================================*/

.main-content .card h3 {
    color: #C8A165;
    font-family: Cinzel, Georgia, serif;
    font-weight: 600;
}

/*======================================
        ADD NEW ARRIVAL BUTTON
======================================*/

.main-content .btn-primary {
    background: #C8A165;
    border: 1px solid #C8A165;
    color: #111;
    font-weight: 600;
    border-radius: 7px;
    padding: 10px 16px;
}

.main-content .btn-primary:hover {
    background: #d8b678;
    border-color: #d8b678;
    color: #111;
}

/*======================================
        TABLE
======================================*/

.main-content .table {
    color: #ffffff;
    margin-bottom: 0;
}

.main-content .table thead {
    background: #0a100e;
}

.main-content .table thead th {
    color: #ffffff;
    border-bottom: 1px solid #3d4a43;
    padding: 15px 12px;
    font-size: 13px;
    font-weight: 600;
}

.main-content .table tbody tr {
    background: #111916;
    border-bottom: 1px solid #29352f;
}

.main-content .table tbody td {
    color: #eeeeee;
    border-color: #29352f;
    padding: 14px 12px;
}

.main-content .table-hover tbody tr:hover {
    background: #18231f;
}

/*======================================
        PRODUCT IMAGE
======================================*/

.main-content .table tbody img {
    border: 1px solid #3d4a43;
}

/*======================================
        STATUS BADGE
======================================*/

.main-content .badge.bg-success {
    background: #123f2c !important;
    color: #61d49a;
    border: 1px solid #267653;
    padding: 7px 12px;
    border-radius: 20px;
}

.main-content .badge.bg-secondary {
    background: #343a40 !important;
    color: #d0d0d0;
    padding: 7px 12px;
    border-radius: 20px;
}

/*======================================
        ACTION BUTTONS
======================================*/

.main-content .btn-action {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #3d4a43;
    border-radius: 7px;
    color: #C8A165;
    background: #151e1a;
    text-decoration: none;
}

.main-content .btn-action:hover {
    background: #C8A165;
    color: #111;
    border-color: #C8A165;
}

.main-content .btn-action-danger {
    color: #e57373;
}

.main-content .btn-action-danger:hover {
    background: #b42318;
    color: #ffffff;
    border-color: #b42318;
}

/*======================================
        RESPONSIVE
======================================*/

@media (max-width: 768px) {

    .main-content .card-body {
        padding: 15px;
    }

    .main-content h1 {
        font-size: 25px;
    }

    .main-content .card h3 {
        font-size: 20px;
    }

}

/*======================================
        NEW ARRIVALS TABLE FIX
======================================*/

.main-content .card,
.main-content .card-body,
.main-content .table-responsive {
    background: #111916 !important;
}

.main-content .table {
    --bs-table-bg: #111916 !important;
    --bs-table-color: #ffffff !important;
    --bs-table-hover-bg: #18231f !important;
    --bs-table-hover-color: #ffffff !important;
    background: #111916 !important;
    color: #ffffff !important;
}

.main-content .table thead,
.main-content .table thead tr,
.main-content .table thead th {
    background: #0a100e !important;
    color: #C8A165 !important;
}

.main-content .table tbody,
.main-content .table tbody tr,
.main-content .table tbody td {
    background: #111916 !important;
    color: #ffffff !important;
}

.main-content .table tbody tr:hover,
.main-content .table tbody tr:hover td {
    background: #18231f !important;
    color: #ffffff !important;
}

/* Product titles */
.main-content .table tbody td:nth-child(2) {
    color: #ffffff !important;
    font-weight: 500;
}

/* Prices */
.main-content .table tbody td:nth-child(3),
.main-content .table tbody td:nth-child(4) {
    color: #C8A165 !important;
    font-weight: 500;
}

/* Order number */
.main-content .table tbody td:nth-child(5) {
    color: #dddddd !important;
}

/* Table borders */
.main-content .table > :not(caption) > * > * {
    border-bottom-color: #35423b !important;
}

</style>

<?php require_once(__DIR__ . '/includes/footer.php'); ?>