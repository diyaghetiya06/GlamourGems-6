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

    header("Location: index.php");
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
        total_amount,
        payment_method,
        payment_status,
        order_status
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

    header("Location: index.php");
    exit;

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Payment Successful | Glamour Gems</title>


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
                SUCCESS PAGE
        ======================================*/

        .success-page {

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 30px 20px;

        }


        /*======================================
                SUCCESS CARD
        ======================================*/

        .success-card {

            width: 100%;

            max-width: 650px;

            background: #ffffff;

            padding: 45px 35px;

            border-radius: 15px;

            text-align: center;

            box-shadow:
                0 10px 35px rgba(0,0,0,0.08);

        }


        /*======================================
                SUCCESS ICON
        ======================================*/

        .success-icon {

            width: 75px;

            height: 75px;

            margin: 0 auto 20px;

            border-radius: 50%;

            background: #234B43;

            color: #ffffff;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 34px;

        }


        /*======================================
                SUCCESS HEADING
        ======================================*/

        .success-card span {

            color: #C8A165;

            font-size: 13px;

            font-weight: 600;

            letter-spacing: 2px;

        }

        .success-card h1 {

            font-family: 'Cinzel', serif;

            color: #234B43;

            font-size: 32px;

            margin: 10px 0;

        }

        .success-card > p {

            color: #777;

            font-size: 14px;

            line-height: 1.7;

            margin-bottom: 30px;

        }


        /*======================================
                ORDER DETAILS
        ======================================*/

        .order-details {

            background: #f5f3ee;

            border-radius: 8px;

            padding: 20px;

            margin-bottom: 30px;

            text-align: left;

        }

        .order-row {

            display: flex;

            justify-content: space-between;

            gap: 20px;

            padding: 9px 0;

            font-size: 14px;

        }

        .order-row span:first-child {

            color: #777;

        }

        .order-row span:last-child {

            color: #234B43;

            font-weight: 600;

            text-align: right;

        }

        .total-row {

            border-top: 1px solid #ddd;

            margin-top: 8px;

            padding-top: 15px;

        }

        .total-row span:last-child {

            color: #C8A165;

            font-size: 19px;

        }


        /*======================================
                SUCCESS MESSAGE
        ======================================*/

        .success-message {

            background: #f1f8f5;

            border: 1px solid #d5e9df;

            color: #234B43;

            padding: 13px;

            border-radius: 6px;

            font-size: 13px;

            margin-bottom: 25px;

        }


        /*======================================
                BUTTONS
        ======================================*/

        .success-buttons {

            display: flex;

            justify-content: center;

            gap: 12px;

            flex-wrap: wrap;

        }

        .success-btn {

            display: inline-block;

            padding: 12px 25px;

            background: #234B43;

            color: #ffffff;

            text-decoration: none;

            border-radius: 6px;

            font-size: 14px;

            font-weight: 600;

            transition: 0.3s ease;

        }

        .success-btn:hover {

            background: #183832;

        }

        .home-btn {

            background: #C8A165;

        }

        .home-btn:hover {

            background: #b28d55;

        }

        /*======================================
            REVIEW BUTTON
        ======================================*/

        .review-btn {
            background: #C8A165;
        }

        .review-btn:hover {
            background: #b28d55;
        }

        /*======================================
                RESPONSIVE
        ======================================*/

        @media (max-width: 600px) {

            .success-card {

                padding: 35px 20px;

            }

            .success-card h1 {

                font-size: 27px;

            }

            .order-row {

                flex-direction: column;

                gap: 3px;

            }

            .order-row span:last-child {

                text-align: left;

            }

        }

    </style>

</head>


<body>


<!--======================================
            PAYMENT SUCCESS
======================================-->

<section class="success-page">

    <div class="success-card">


        <!--======================================
                SUCCESS ICON
        ======================================-->

        <div class="success-icon">

            <i class="fa-solid fa-check"></i>

        </div>


        <span>PAYMENT COMPLETED</span>


        <h1>
            Payment Successful!
        </h1>


        <p>

            Thank you for your payment.
            Your Glamour Gems order has been successfully confirmed.

        </p>


        <!--======================================
                ORDER DETAILS
        ======================================-->

        <div class="order-details">


            <div class="order-row">

                <span>
                    Order Number
                </span>

                <span>
                    <?php
                    echo htmlspecialchars(
                        $order['order_number']
                    );
                    ?>
                </span>

            </div>


            <div class="order-row">

                <span>
                    Payment Method
                </span>

                <span>
                    <?php
                    echo htmlspecialchars(
                        $order['payment_method']
                    );
                    ?>
                </span>

            </div>


            <div class="order-row">

                <span>
                    Payment Status
                </span>

                <span>
                    <?php
                    echo htmlspecialchars(
                        $order['payment_status']
                    );
                    ?>
                </span>

            </div>


            <div class="order-row total-row">

                <span>
                    Amount Paid
                </span>

                <span>

                    ₹<?php
                    echo number_format(
                        $order['total_amount'],
                        2
                    );
                    ?>

                </span>

            </div>


        </div>


        <!--======================================
                SUCCESS MESSAGE
        ======================================-->

        <div class="success-message">

            <i class="fa-solid fa-circle-check"></i>

            Your payment has been recorded successfully.

        </div>

<!--======================================
            ACTION BUTTONS
======================================-->

<div class="success-buttons">

    <a
        href="index.php"
        class="success-btn home-btn"
    >
        <i class="fa-solid fa-house"></i>
        Back to Home
    </a>


    <a
        href="shop.php"
        class="success-btn"
    >
        <i class="fa-solid fa-bag-shopping"></i>
        Continue Shopping
    </a>


    <a
        href="orders.php"
        class="success-btn"
    >
        <i class="fa-solid fa-box"></i>
        My Orders
    </a>


    <a
        href="review.php?order_id=<?php echo $order_id; ?>"
        class="success-btn review-btn"
    >
        <i class="fa-solid fa-star"></i>
        Write a Review
    </a>

</div>        


    </div>

</section>


</body>

</html>