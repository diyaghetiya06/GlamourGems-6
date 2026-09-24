<?php

require_once 'config/config.php';
require_once 'config/database.php';


/*======================================
            CHECK LOGIN
======================================*/

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit;

}

$user_id = (int) $_SESSION['user_id'];


/*======================================
            FETCH ORDERS
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        order_number,
        total_amount,
        payment_method,
        payment_status,
        order_status,
        created_at
     FROM orders
     WHERE customer_email = ?
     ORDER BY id DESC"
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $_SESSION['user_email']
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Orders | Glamour Gems</title>


    <!--======================================
                GOOGLE FONTS
    ======================================-->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet"
    >


    <!--======================================
                FONT AWESOME
    ======================================-->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >


    <style>

        /*======================================
                    GLOBAL
        ======================================*/

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f9f7f2;
            color: #333;
        }


        /*======================================
                    ORDERS PAGE
        ======================================*/

        .orders-page {

            min-height: 100vh;

            padding: 60px 20px;

        }

        .orders-container {

            max-width: 1100px;

            margin: auto;

        }


        /*======================================
                    PAGE HEADING
        ======================================*/

        .orders-heading {

            text-align: center;

            margin-bottom: 35px;

        }

        .orders-heading span {

            color: #C8A165;

            font-size: 13px;

            font-weight: 600;

            letter-spacing: 2px;

        }

        .orders-heading h1 {

            font-family: 'Cinzel', serif;

            color: #234B43;

            font-size: 34px;

            margin: 8px 0;

        }

        .orders-heading p {

            color: #777;

            font-size: 14px;

        }


        /*======================================
                    ORDERS CARD
        ======================================*/

        .orders-card {

            background: #ffffff;

            border-radius: 14px;

            padding: 25px;

            box-shadow:
                0 10px 35px rgba(0,0,0,0.07);

        }


        /*======================================
                    TABLE
        ======================================*/

        .orders-table-wrapper {

            width: 100%;

            overflow-x: auto;

        }

        .orders-table {

            width: 100%;

            border-collapse: collapse;

            min-width: 850px;

        }

        .orders-table th {

            background: #234B43;

            color: #ffffff;

            padding: 14px 12px;

            text-align: left;

            font-size: 13px;

            font-weight: 600;

        }

        .orders-table td {

            padding: 15px 12px;

            border-bottom: 1px solid #eee;

            font-size: 13px;

            color: #555;

        }

        .orders-table tbody tr:hover {

            background: #faf9f5;

        }


        /*======================================
                    ORDER NUMBER
        ======================================*/

        .order-number {

            color: #234B43;

            font-weight: 600;

        }


        /*======================================
                    AMOUNT
        ======================================*/

        .order-amount {

            color: #C8A165;

            font-weight: 600;

        }


        /*======================================
                    STATUS
        ======================================*/

        .status {

            display: inline-block;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 11px;

            font-weight: 600;

        }

        .status-paid {

            background: #e8f5ee;

            color: #24734d;

        }

        .status-pending {

            background: #fff4df;

            color: #9a6814;

        }

        .status-cancelled {

            background: #fdeaea;

            color: #b63b3b;

        }


        /*======================================
                    VIEW BUTTON
        ======================================*/

        .view-btn {

            display: inline-block;

            padding: 7px 13px;

            background: #234B43;

            color: #ffffff;

            text-decoration: none;

            border-radius: 5px;

            font-size: 11px;

            font-weight: 600;

            transition: 0.3s ease;

        }

        .view-btn:hover {

            background: #183832;

        }


        /*======================================
                    EMPTY ORDERS
        ======================================*/

        .empty-orders {

            text-align: center;

            padding: 60px 20px;

        }

        .empty-orders i {

            font-size: 45px;

            color: #C8A165;

            margin-bottom: 18px;

        }

        .empty-orders h2 {

            font-family: 'Cinzel', serif;

            color: #234B43;

            font-size: 23px;

            margin-bottom: 8px;

        }

        .empty-orders p {

            color: #777;

            font-size: 13px;

            margin-bottom: 20px;

        }

        .shop-btn {

            display: inline-block;

            padding: 11px 23px;

            background: #234B43;

            color: #ffffff;

            text-decoration: none;

            border-radius: 6px;

            font-size: 13px;

            font-weight: 600;

        }


        /*======================================
                    BACK PROFILE
        ======================================*/

        .back-profile {

            display: block;

            text-align: center;

            margin-top: 20px;

            color: #777;

            text-decoration: none;

            font-size: 13px;

        }

        .back-profile:hover {

            color: #234B43;

        }


        /*======================================
                    RESPONSIVE
        ======================================*/

        @media (max-width: 650px) {

            .orders-page {

                padding: 40px 15px;

            }

            .orders-heading h1 {

                font-size: 28px;

            }

            .orders-card {

                padding: 15px;

            }

        }

    </style>

</head>


<body>


<!--======================================
                MY ORDERS
======================================-->

<section class="orders-page">

    <div class="orders-container">


        <!--======================================
                PAGE HEADING
        ======================================-->

        <div class="orders-heading">

            <span>YOUR PURCHASES</span>

            <h1>My Orders</h1>

            <p>
                View and track all your Glamour Gems orders.
            </p>

        </div>


        <div class="orders-card">


            <?php if (mysqli_num_rows($result) > 0): ?>


                <!--======================================
                        ORDERS TABLE
                ======================================-->

                <div class="orders-table-wrapper">

                    <table class="orders-table">

                        <thead>

                            <tr>

                                <th>Order Number</th>

                                <th>Date</th>

                                <th>Total</th>

                                <th>Payment Method</th>

                                <th>Payment Status</th>

                                <th>Order Status</th>

                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php while ($order = mysqli_fetch_assoc($result)): ?>

                                <?php

                                $payment_status =
                                    strtolower(
                                        $order['payment_status']
                                    );

                                $order_status =
                                    strtolower(
                                        $order['order_status']
                                    );

                                ?>


                                <tr>


                                    <!-- Order Number -->

                                    <td>

                                        <span class="order-number">

                                            <?php
                                            echo htmlspecialchars(
                                                $order['order_number']
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <!-- Date -->

                                    <td>

                                        <?php
                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $order['created_at']
                                            )
                                        );
                                        ?>

                                    </td>


                                    <!-- Total -->

                                    <td>

                                        <span class="order-amount">

                                            ₹<?php
                                            echo number_format(
                                                $order['total_amount'],
                                                2
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <!-- Payment Method -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $order['payment_method']
                                        );
                                        ?>

                                    </td>


                                    <!-- Payment Status -->

                                    <td>

                                        <span
                                            class="status
                                            <?php

                                            if (
                                                $payment_status === 'paid'
                                            ) {

                                                echo 'status-paid';

                                            } elseif (
                                                $payment_status === 'cancelled'
                                            ) {

                                                echo 'status-cancelled';

                                            } else {

                                                echo 'status-pending';

                                            }

                                            ?>"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $order['payment_status']
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <!-- Order Status -->

                                    <td>

                                        <span
                                            class="status
                                            <?php

                                            if (
                                                $order_status === 'completed'
                                                || $order_status === 'delivered'
                                            ) {

                                                echo 'status-paid';

                                            } elseif (
                                                $order_status === 'cancelled'
                                            ) {

                                                echo 'status-cancelled';

                                            } else {

                                                echo 'status-pending';

                                            }

                                            ?>"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $order['order_status']
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <!-- View Order -->

                                    <td>

                                        <a
                                            href="order-details.php?order_id=<?php echo (int) $order['id']; ?>"
                                            class="view-btn"
                                        >

                                            <i class="fa-solid fa-eye"></i>

                                            View

                                        </a>

                                    </td>


                                </tr>


                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <!--======================================
                        NO ORDERS
                ======================================-->

                <div class="empty-orders">

                    <i class="fa-solid fa-box-open"></i>

                    <h2>No Orders Yet</h2>

                    <p>
                        You haven't placed any orders yet.
                    </p>

                    <a
                        href="shop.php"
                        class="shop-btn"
                    >
                        Start Shopping
                    </a>

                </div>


            <?php endif; ?>


        </div>


        <!--======================================
                BACK TO PROFILE
        ======================================-->

        <a
            href="profile.php"
            class="back-profile"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back to My Profile

        </a>


    </div>

</section>


</body>

</html>

<?php

mysqli_stmt_close($stmt);

?>