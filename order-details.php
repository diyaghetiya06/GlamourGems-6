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


/*======================================
            GET ORDER ID
======================================*/

$order_id = isset($_GET['order_id'])
    ? (int) $_GET['order_id']
    : 0;

if ($order_id <= 0) {

    header("Location: orders.php");
    exit;

}


/*======================================
            FETCH ORDER
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        order_number,
        customer_name,
        customer_email,
        customer_phone,
        shipping_address,
        total_amount,
        payment_method,
        payment_status,
        order_status,
        created_at
     FROM orders
     WHERE id = ?
     AND customer_email = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "is",
    $order_id,
    $_SESSION['user_email']
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$order = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$order) {

    header("Location: orders.php");
    exit;

}


/*======================================
            FETCH ORDER ITEMS
======================================*/

$item_stmt = mysqli_prepare(
    $conn,
    "SELECT
        oi.product_id,
        oi.product_name,
        oi.quantity,
        oi.price
     FROM order_items oi
     WHERE oi.order_id = ?
     ORDER BY oi.id ASC"
);

mysqli_stmt_bind_param(
    $item_stmt,
    "i",
    $order_id
);

mysqli_stmt_execute($item_stmt);

$items_result = mysqli_stmt_get_result($item_stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Order Details | Glamour Gems</title>


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
                ORDER DETAILS PAGE
        ======================================*/

        .order-details-page {

            min-height: 100vh;

            padding: 60px 20px;

        }

        .order-details-container {

            max-width: 1050px;

            margin: auto;

        }


        /*======================================
                    PAGE HEADING
        ======================================*/

        .page-heading {

            text-align: center;

            margin-bottom: 35px;

        }

        .page-heading span {

            color: #C8A165;

            font-size: 13px;

            font-weight: 600;

            letter-spacing: 2px;

        }

        .page-heading h1 {

            font-family: 'Cinzel', serif;

            color: #234B43;

            font-size: 34px;

            margin: 8px 0;

        }

        .page-heading p {

            color: #777;

            font-size: 14px;

        }


        /*======================================
                    ORDER CARD
        ======================================*/

        .order-card {

            background: #ffffff;

            border-radius: 14px;

            padding: 30px;

            margin-bottom: 25px;

            box-shadow:
                0 10px 35px rgba(0,0,0,0.07);

        }


        /*======================================
                ORDER HEADER
        ======================================*/

        .order-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            padding-bottom: 20px;

            margin-bottom: 25px;

            border-bottom: 1px solid #eee;

        }

        .order-header h2 {

            font-family: 'Cinzel', serif;

            color: #234B43;

            font-size: 22px;

        }

        .order-header p {

            color: #777;

            font-size: 13px;

            margin-top: 5px;

        }


        /*======================================
                    STATUS
        ======================================*/

        .status {

            display: inline-block;

            padding: 7px 13px;

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
                ORDER INFORMATION
        ======================================*/

        .info-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 18px;

            margin-bottom: 30px;

        }

        .info-box {

            background: #f7f5f0;

            padding: 18px;

            border-radius: 8px;

        }

        .info-box span {

            display: block;

            color: #888;

            font-size: 12px;

            margin-bottom: 6px;

        }

        .info-box strong {

            color: #234B43;

            font-size: 14px;

        }


        /*======================================
                    SECTION TITLE
        ======================================*/

        .section-title {

            font-family: 'Cinzel', serif;

            color: #234B43;

            font-size: 21px;

            margin-bottom: 18px;

        }


        /*======================================
                    PRODUCT TABLE
        ======================================*/

        .table-wrapper {

            overflow-x: auto;

        }

        .items-table {

            width: 100%;

            border-collapse: collapse;

            min-width: 600px;

        }

        .items-table th {

            background: #234B43;

            color: #ffffff;

            padding: 13px;

            text-align: left;

            font-size: 13px;

        }

        .items-table td {

            padding: 15px 13px;

            border-bottom: 1px solid #eee;

            font-size: 13px;

            color: #555;

        }

        .items-table tr:hover {

            background: #faf9f5;

        }

        .product-name {

            color: #234B43;

            font-weight: 600;

        }

        .product-price {

            color: #C8A165;

            font-weight: 600;

        }


        /*======================================
                    TOTAL
        ======================================*/

        .total-box {

            display: flex;

            justify-content: flex-end;

            margin-top: 20px;

        }

        .total-content {

            min-width: 280px;

            background: #f5f3ee;

            padding: 18px;

            border-radius: 8px;

        }

        .total-row {

            display: flex;

            justify-content: space-between;

            gap: 30px;

            font-size: 14px;

            color: #777;

        }

        .total-row strong {

            color: #C8A165;

            font-size: 20px;

        }


        /*======================================
                SHIPPING ADDRESS
        ======================================*/

        .address-box {

            background: #f7f5f0;

            padding: 20px;

            border-radius: 8px;

            color: #555;

            font-size: 13px;

            line-height: 1.8;

        }


        /*======================================
                    BUTTONS
        ======================================*/

        .action-buttons {

            display: flex;

            justify-content: center;

            gap: 12px;

            flex-wrap: wrap;

            margin-top: 25px;

        }

        .action-btn {

            display: inline-block;

            padding: 12px 24px;

            background: #234B43;

            color: #ffffff;

            text-decoration: none;

            border-radius: 6px;

            font-size: 13px;

            font-weight: 600;

            transition: 0.3s ease;

        }

        .action-btn:hover {

            background: #183832;

        }

        .shop-btn {

            background: #C8A165;

        }

        .shop-btn:hover {

            background: #b28d55;

        }


        /*======================================
                    RESPONSIVE
        ======================================*/

        @media (max-width: 650px) {

            .order-details-page {

                padding: 40px 15px;

            }

            .order-card {

                padding: 20px;

            }

            .page-heading h1 {

                font-size: 28px;

            }

            .order-header {

                flex-direction: column;

                align-items: flex-start;

            }

            .info-grid {

                grid-template-columns: 1fr;

            }

            .total-box {

                justify-content: stretch;

            }

            .total-content {

                width: 100%;

            }

        }

    </style>

</head>


<body>


<!--======================================
            ORDER DETAILS PAGE
======================================-->

<section class="order-details-page">

    <div class="order-details-container">


        <!--======================================
                    PAGE HEADING
        ======================================-->

        <div class="page-heading">

            <span>GLAMOUR GEMS</span>

            <h1>Order Details</h1>

            <p>
                View complete information about your order.
            </p>

        </div>


        <!--======================================
                    ORDER SUMMARY
        ======================================-->

        <div class="order-card">

            <div class="order-header">

                <div>

                    <h2>
                        <?php
                        echo htmlspecialchars(
                            $order['order_number']
                        );
                        ?>
                    </h2>

                    <p>

                        Ordered on
                        <?php
                        echo date(
                            "d M Y, h:i A",
                            strtotime(
                                $order['created_at']
                            )
                        );
                        ?>

                    </p>

                </div>


                <?php

                $order_status =
                    strtolower(
                        $order['order_status']
                    );

                if (
                    $order_status === 'delivered' ||
                    $order_status === 'completed'
                ) {

                    $status_class = 'status-paid';

                } elseif (
                    $order_status === 'cancelled'
                ) {

                    $status_class = 'status-cancelled';

                } else {

                    $status_class = 'status-pending';

                }

                ?>

                <span
                    class="status <?php echo $status_class; ?>"
                >

                    <?php
                    echo htmlspecialchars(
                        $order['order_status']
                    );
                    ?>

                </span>

            </div>


            <!--======================================
                    CUSTOMER INFORMATION
            ======================================-->

            <div class="info-grid">


                <div class="info-box">

                    <span>
                        Customer Name
                    </span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $order['customer_name']
                        );
                        ?>
                    </strong>

                </div>


                <div class="info-box">

                    <span>
                        Email
                    </span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $order['customer_email']
                        );
                        ?>
                    </strong>

                </div>


                <div class="info-box">

                    <span>
                        Phone
                    </span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $order['customer_phone']
                        );
                        ?>
                    </strong>

                </div>


                <div class="info-box">

                    <span>
                        Payment Method
                    </span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $order['payment_method']
                        );
                        ?>
                    </strong>

                </div>


                <div class="info-box">

                    <span>
                        Payment Status
                    </span>

                    <strong>

                        <?php
                        echo htmlspecialchars(
                            $order['payment_status']
                        );
                        ?>

                    </strong>

                </div>


            </div>


            <!--======================================
                    ORDER ITEMS
            ======================================-->

            <h2 class="section-title">
                Ordered Products
            </h2>


            <div class="table-wrapper">

                <table class="items-table">

                    <thead>

                        <tr>

                            <th>
                                Product
                            </th>

                            <th>
                                Quantity
                            </th>

                            <th>
                                Price
                            </th>

                            <th>
                                Total
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php

                        $items_total = 0;

                        if (
                            mysqli_num_rows($items_result) > 0
                        ):

                            while (
                                $item =
                                mysqli_fetch_assoc(
                                    $items_result
                                )
                            ):

                                $item_total =
                                    $item['price']
                                    * $item['quantity'];

                                $items_total +=
                                    $item_total;

                        ?>

                            <tr>

                                <td>

                                    <span class="product-name">

                                        <?php
                                        echo htmlspecialchars(
                                            $item['product_name']
                                        );
                                        ?>

                                    </span>

                                </td>


                                <td>

                                    <?php
                                    echo (int)
                                        $item['quantity'];
                                    ?>

                                </td>


                                <td>

                                    <span class="product-price">

                                        ₹<?php
                                        echo number_format(
                                            $item['price'],
                                            2
                                        );
                                        ?>

                                    </span>

                                </td>


                                <td>

                                    <span class="product-price">

                                        ₹<?php
                                        echo number_format(
                                            $item_total,
                                            2
                                        );
                                        ?>

                                    </span>

                                </td>

                            </tr>


                        <?php

                            endwhile;

                        else:

                        ?>

                            <tr>

                                <td
                                    colspan="4"
                                    style="
                                        text-align:center;
                                        color:#777;
                                    "
                                >

                                    No products found
                                    for this order.

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>


            <!--======================================
                        ORDER TOTAL
            ======================================-->

            <div class="total-box">

                <div class="total-content">

                    <div class="total-row">

                        <span>
                            Order Total
                        </span>

                        <strong>

                            ₹<?php
                            echo number_format(
                                $order['total_amount'],
                                2
                            );
                            ?>

                        </strong>

                    </div>

                </div>

            </div>

        </div>


        <!--======================================
                    SHIPPING ADDRESS
        ======================================-->

        <div class="order-card">

            <h2 class="section-title">

                <i class="fa-solid fa-location-dot"></i>

                Delivery Address

            </h2>


            <div class="address-box">

                <?php

                echo nl2br(
                    htmlspecialchars(
                        $order['shipping_address']
                    )
                );

                ?>

            </div>

        </div>


        <!--======================================
                    ACTION BUTTONS
        ======================================-->

        <div class="action-buttons">

            <a
                href="orders.php"
                class="action-btn"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Back to My Orders

            </a>


            <a
                href="shop.php"
                class="action-btn shop-btn"
            >

                <i class="fa-solid fa-bag-shopping"></i>

                Continue Shopping

            </a>

        </div>


    </div>

</section>


</body>

</html>

<?php

mysqli_stmt_close($item_stmt);

?>