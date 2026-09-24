<?php

require_once 'config/config.php';
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = isset($_GET['order_id'])
    ? (int) $_GET['order_id']
    : 0;

if ($order_id <= 0) {
    header("Location: shop.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Order Successful | Glamour Gems</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>

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

        .success-page {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .success-box {
            max-width: 600px;
            width: 100%;
            background: #fff;
            padding: 55px 35px;
            text-align: center;
            border-radius: 14px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.08);
        }

        .success-icon {
            width: 75px;
            height: 75px;
            margin: 0 auto 25px;
            border-radius: 50%;
            background: #234B43;
            color: #C8A165;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
        }

        .success-box h1 {
            font-family: 'Cinzel', serif;
            color: #234B43;
            font-size: 32px;
            margin-bottom: 12px;
        }

        .success-box p {
            color: #777;
            font-size: 14px;
            line-height: 1.7;
        }

        .order-number {
            margin: 25px 0;
            padding: 15px;
            background: #f5f3ee;
            border-radius: 7px;
            color: #234B43;
            font-weight: 600;
        }

        .success-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 13px 28px;
            background: #234B43;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }

        .success-btn:hover {
            background: #183832;
        }
        
        /*======================================
            PAYMENT BUTTON
            ======================================*/

            .payment-btn {
                display: inline-block;
                margin-top: 15px;
                padding: 13px 28px;
                background: #C8A165;
                color: #fff;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 600;
                transition: 0.3s ease;
            }

            .payment-btn:hover {
                background: #b28d55;
            }

    </style>

</head>

<body>

<div class="success-page">

    <div class="success-box">

        <div class="success-icon">
            ✓
        </div>

        <h1>Order Placed Successfully!</h1>

        <p>
            Thank you for shopping with Glamour Gems.
            Your order has been successfully placed.
        </p>

        <div class="order-number">

            Order ID:
            #<?php echo $order_id; ?>

        </div>

        <a href="shop.php" class="success-btn">
            Continue Shopping
        </a>

        <a
        href="payment.php?order_id=<?php echo $order_id; ?>"
        class="payment-btn"
    >
        <i class="fa-solid fa-credit-card"></i>
        Make Payment
    </a>
    </div>

</div>

</body>

</html>