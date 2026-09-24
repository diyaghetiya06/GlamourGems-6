<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Payment Management';


//======================================
//          PAYMENT FILTER
//======================================

$payment_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

//======================================
//          BUILD PAYMENT QUERY
//======================================

$where_sql = "";

$conditions = [];


//======================================
//          PAYMENT STATUS FILTER
//======================================

if (
    $payment_status === 'Paid' ||
    $payment_status === 'Pending' ||
    $payment_status === 'Failed' ||
    $payment_status === 'Refunded'
) {

    $safe_status = mysqli_real_escape_string($conn, $payment_status);

    $conditions[] = "payment_status = '$safe_status'";
}


//======================================
//          SEARCH FILTER
//======================================

if ($search !== '') {

    $safe_search = mysqli_real_escape_string($conn, $search);

    $conditions[] = "(
        order_number LIKE '%$safe_search%'
        OR customer_name LIKE '%$safe_search%'
        OR customer_email LIKE '%$safe_search%'
        OR payment_method LIKE '%$safe_search%'
    )";
}


//======================================
//          FINAL WHERE
//======================================

if (!empty($conditions)) {

    $where_sql = "WHERE " . implode(" AND ", $conditions);

}



//======================================
//          FETCH PAYMENTS
//======================================

$payment_query = mysqli_query(
    $conn,
    "SELECT
        id,
        order_number,
        customer_name,
        customer_email,
        total_amount,
        payment_method,
        payment_status,
        created_at
     FROM orders
     $where_sql
     ORDER BY id DESC"
);


//======================================
//          PAYMENT COUNTS
//======================================

$total_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM orders"
);

$total_row = mysqli_fetch_assoc($total_result);
$total_payments = (int)$total_row['total'];


$paid_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM orders WHERE payment_status = 'Paid'"
);

$paid_row = mysqli_fetch_assoc($paid_result);
$paid_payments = (int)$paid_row['total'];


$pending_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM orders WHERE payment_status = 'Pending'"
);

$pending_row = mysqli_fetch_assoc($pending_result);
$pending_payments = (int)$pending_row['total'];


$failed_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM orders WHERE payment_status = 'Failed'"
);

$failed_row = mysqli_fetch_assoc($failed_result);
$failed_payments = (int)$failed_row['total'];


include(__DIR__ . '/includes/header.php');

?>

<!--======================================
            PAYMENT MANAGEMENT
======================================-->

<div class="admin-page">

    <!--======================================
                PAGE HEADER
    ======================================-->

    <div class="page-header">

        <div>
            <h1 class="page-title">Payment Management</h1>

            <p class="page-subtitle">
                View and manage customer payment records.
            </p>
        </div>

    </div>


    <!--======================================
                SUMMARY CARDS
    ======================================-->

    <div class="stats-grid">

        <!-- TOTAL PAYMENTS -->

        <div class="stat-card">

            <div class="stat-icon">
                <i class="fa-solid fa-credit-card"></i>
            </div>

            <div class="stat-content">

                <span class="stat-label">
                    Total Payments
                </span>

                <h3>
                    <?php echo $total_payments; ?>
                </h3>

            </div>

        </div>


        <!-- PAID -->

        <div class="stat-card">

            <div class="stat-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <div class="stat-content">

                <span class="stat-label">
                    Paid
                </span>

                <h3>
                    <?php echo $paid_payments; ?>
                </h3>

            </div>

        </div>


        <!-- PENDING -->

        <div class="stat-card">

            <div class="stat-icon">
                <i class="fa-solid fa-clock"></i>
            </div>

            <div class="stat-content">

                <span class="stat-label">
                    Pending
                </span>

                <h3>
                    <?php echo $pending_payments; ?>
                </h3>

            </div>

        </div>


        <!-- FAILED -->

        <div class="stat-card">

            <div class="stat-icon">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>

            <div class="stat-content">

                <span class="stat-label">
                    Failed
                </span>

                <h3>
                    <?php echo $failed_payments; ?>
                </h3>

            </div>

        </div>

    </div>


    <!--======================================
                PAYMENT TABLE
    ======================================-->

    <div class="gg-card">

        <div class="gg-card-header">

            <div>

                <h2 class="gg-card-title">
                    Payment Records
                </h2>

                <p class="gg-card-subtitle">
                    All payment transactions from customer orders.
                </p>

            </div>

        </div>


        <!--======================================
                    FILTERS
        ======================================-->

        <form method="GET" class="filter-form">

            <div class="filter-group">

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search order, customer or payment method..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >

            </div>


            <div class="filter-group">

                <select name="status" class="form-control">

                    <option value="">
                        All Payment Status
                    </option>

                    <option
                        value="Paid"
                        <?php echo ($payment_status === 'Paid') ? 'selected' : ''; ?>
                    >
                        Paid
                    </option>

                    <option
                        value="Pending"
                        <?php echo ($payment_status === 'Pending') ? 'selected' : ''; ?>
                    >
                        Pending
                    </option>

                    <option
                        value="Failed"
                        <?php echo ($payment_status === 'Failed') ? 'selected' : ''; ?>
                    >
                        Failed
                    </option>

                    <option
                        value="Refunded"
                        <?php echo ($payment_status === 'Refunded') ? 'selected' : ''; ?>
                    >
                        Refunded
                    </option>

                </select>

            </div>


            <button type="submit" class="btn-gg-gold">
                <i class="fa-solid fa-filter"></i>
                Filter
            </button>


            <a href="payments.php" class="btn-gg-secondary">
                <i class="fa-solid fa-rotate-left"></i>
                Reset
            </a>

        </form>


        <!--======================================
                    PAYMENT TABLE
        ======================================-->

        <div class="table-responsive">

            <table class="table-gg">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Order</th>

                        <th>Customer</th>

                        <th>Amount</th>

                        <th>Payment Method</th>

                        <th>Payment Status</th>

                        <th>Date</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                    <?php if ($payment_query && mysqli_num_rows($payment_query) > 0): ?>

                        <?php
                        $count = 1;

                        while ($payment = mysqli_fetch_assoc($payment_query)):
                        ?>

                            <tr>

                                <!-- NUMBER -->

                                <td>
                                    <?php echo $count++; ?>
                                </td>


                                <!-- ORDER -->

                                <td>

                                    <strong>
                                        <?php echo htmlspecialchars($payment['order_number']); ?>
                                    </strong>

                                </td>


                                <!-- CUSTOMER -->

                                <td>

                                    <div>

                                        <strong>
                                            <?php echo htmlspecialchars($payment['customer_name']); ?>
                                        </strong>

                                        <br>

                                        <small>
                                            <?php echo htmlspecialchars($payment['customer_email']); ?>
                                        </small>

                                    </div>

                                </td>


                                <!-- AMOUNT -->

                                <td>

                                    <strong>
                                        ₹<?php echo number_format((float)$payment['total_amount'], 2); ?>
                                    </strong>

                                </td>


                                <!-- PAYMENT METHOD -->

                                <td>

                                    <?php
                                    $method = $payment['payment_method']
                                        ? $payment['payment_method']
                                        : 'Not Selected';
                                    ?>

                                    <span>
                                        <?php echo htmlspecialchars($method); ?>
                                    </span>

                                </td>


                                <!-- PAYMENT STATUS -->

                                <td>

                                    <?php

                                    $status = $payment['payment_status'];

                                    $status_class = 'badge-gg';

                                    if ($status === 'Paid') {
                                        $status_class .= ' badge-success';
                                    } elseif ($status === 'Pending') {
                                        $status_class .= ' badge-warning';
                                    } elseif ($status === 'Failed') {
                                        $status_class .= ' badge-danger';
                                    } elseif ($status === 'Refunded') {
                                        $status_class .= ' badge-info';
                                    }

                                    ?>

                                    <span class="<?php echo $status_class; ?>">

                                        <?php echo htmlspecialchars($status); ?>

                                    </span>

                                </td>


                                <!-- DATE -->

                                <td>

                                    <?php
                                    echo date(
                                        'd M Y',
                                        strtotime($payment['created_at'])
                                    );
                                    ?>

                                    <br>

                                    <small>
                                        <?php
                                        echo date(
                                            'h:i A',
                                            strtotime($payment['created_at'])
                                        );
                                        ?>
                                    </small>

                                </td>


                                <!-- ACTION -->

                                <td>

                                    <a
                                        href="order-details.php?id=<?php echo (int)$payment['id']; ?>"
                                        class="btn-gg-small"
                                        title="View Order"
                                    >

                                        <i class="fa-solid fa-eye"></i>

                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="8" class="text-center">

                                <div class="empty-state">

                                    <i class="fa-solid fa-credit-card"></i>

                                    <h3>
                                        No Payment Records Found
                                    </h3>

                                    <p>
                                        No payments match the selected filter.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<?php

include(__DIR__ . '/includes/footer.php');

?>