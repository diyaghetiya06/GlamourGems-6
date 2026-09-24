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
            GET ORDER ID
======================================*/

$order_id = isset($_GET['order_id'])
    ? (int) $_GET['order_id']
    : 0;

if ($order_id <= 0) {

    header("Location: shop.php");
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
        total_amount,
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

    header("Location: shop.php");
    exit;

}


/*======================================
        HANDLE DEMO PAYMENT
======================================*/

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $payment_method = $_POST['payment_method'] ?? '';

    if ($payment_method === "") {

        $error = "Please select a payment method.";

    } else {

        /*======================================
                DEMO PAYMENT VALIDATION
        ======================================*/

        if ($payment_method === "card") {

            $card_number = trim($_POST['card_number'] ?? '');
            $expiry = trim($_POST['expiry'] ?? '');
            $cvv = trim($_POST['cvv'] ?? '');

            if (
                $card_number === "" ||
                $expiry === "" ||
                $cvv === ""
            ) {

                $error = "Please enter all card details.";

            }

        } elseif ($payment_method === "upi") {

            $upi_id = trim($_POST['upi_id'] ?? '');

            if ($upi_id === "") {

                $error = "Please enter your UPI ID.";

            }

        } elseif ($payment_method === "netbanking") {

            $bank = trim($_POST['bank'] ?? '');

            if ($bank === "") {

                $error = "Please select your bank.";

            }

        }


        /*======================================
                UPDATE PAYMENT
        ======================================*/

        if ($error === "") {

            if ($payment_method === "card") {
                $method_name = "Credit / Debit Card";
            } elseif ($payment_method === "upi") {
                $method_name = "UPI";
            } elseif ($payment_method === "netbanking") {
                $method_name = "Net Banking";
            } else {
                $method_name = "Cash on Delivery";
            }


            $update_stmt = mysqli_prepare(
                $conn,
                "UPDATE orders
                 SET payment_method = ?,
                     payment_status = 'Paid'
                 WHERE id = ?
                 AND customer_email = ?"
            );

            mysqli_stmt_bind_param(
                $update_stmt,
                "sis",
                $method_name,
                $order_id,
                $_SESSION['user_email']
            );

            mysqli_stmt_execute($update_stmt);

            mysqli_stmt_close($update_stmt);


            /*======================================
                    PAYMENT SUCCESS REDIRECT
            ======================================*/

            header(
                "Location: payment-success.php?order_id=" . $order_id
            );

            exit;

        }

    }

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

    <title>Make Payment | Glamour Gems</title>


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
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
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
                PAYMENT PAGE
        ======================================*/

        .payment-page {
            min-height: 100vh;
            padding: 60px 20px;
        }

        .payment-container {
            max-width: 850px;
            margin: auto;
        }


        /*======================================
                PAYMENT HEADING
        ======================================*/

        .payment-heading {
            text-align: center;
            margin-bottom: 35px;
        }

        .payment-heading span {
            color: #C8A165;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 2px;
        }

        .payment-heading h1 {
            font-family: 'Cinzel', serif;
            color: #234B43;
            font-size: 34px;
            margin: 8px 0;
        }

        .payment-heading p {
            color: #777;
            font-size: 14px;
        }


        /*======================================
                PAYMENT CARD
        ======================================*/

        .payment-card {
            background: #fff;
            padding: 35px;
            border-radius: 14px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.08);
        }


        /*======================================
                ORDER INFORMATION
        ======================================*/

        .order-info {
            background: #f5f3ee;
            padding: 22px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .order-info-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 9px 0;
            font-size: 14px;
        }

        .order-info-row span:first-child {
            color: #777;
        }

        .order-info-row span:last-child {
            color: #234B43;
            font-weight: 600;
        }

        .total-row {
            border-top: 1px solid #ddd;
            margin-top: 10px;
            padding-top: 15px;
        }

        .total-row span:last-child {
            color: #C8A165;
            font-size: 20px;
        }


        /*======================================
                PAYMENT METHODS
        ======================================*/

        .payment-title {
            font-family: 'Cinzel', serif;
            color: #234B43;
            font-size: 22px;
            margin-bottom: 18px;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .payment-method {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .payment-method:hover {
            border-color: #C8A165;
        }

        .payment-method input {
            margin-right: 10px;
            accent-color: #234B43;
        }

        .payment-method i {
            color: #C8A165;
            font-size: 20px;
            margin-right: 8px;
        }

        .payment-method label {
            color: #234B43;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }


        /*======================================
                PAYMENT DETAILS
        ======================================*/

        .payment-details {
            display: none;
            margin-top: 25px;
            padding: 25px;
            background: #f8f6f1;
            border-radius: 8px;
            border: 1px solid #e5e1d8;
        }

        .payment-details.active {
            display: block;
        }

        .payment-details h3 {
            font-family: 'Cinzel', serif;
            color: #234B43;
            margin-bottom: 18px;
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            color: #555;
            font-size: 13px;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #ddd;
            border-radius: 6px;
            outline: none;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            background: #fff;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #C8A165;
        }

        .card-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }


        /*======================================
                ERROR MESSAGE
        ======================================*/

        .error-message {
            background: #fff0f0;
            color: #c0392b;
            border: 1px solid #f0caca;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
        }


        /*======================================
                PAY BUTTON
        ======================================*/

        .pay-btn {
            width: 100%;
            border: none;
            margin-top: 30px;
            padding: 15px;
            background: #234B43;
            color: #fff;
            border-radius: 7px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .pay-btn:hover {
            background: #183832;
        }


        /*======================================
                BACK BUTTON
        ======================================*/

        .back-btn {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #777;
            text-decoration: none;
            font-size: 13px;
        }

        .back-btn:hover {
            color: #234B43;
        }


        /*======================================
                RESPONSIVE
        ======================================*/

        @media (max-width: 650px) {

            .payment-card {
                padding: 25px 20px;
            }

            .payment-heading h1 {
                font-size: 28px;
            }

            .payment-methods {
                grid-template-columns: 1fr;
            }

            .card-row {
                grid-template-columns: 1fr;
            }

            .order-info-row {
                flex-direction: column;
                gap: 3px;
            }

        }

    </style>

</head>


<body>


<!--======================================
            PAYMENT PAGE
======================================-->

<section class="payment-page">

    <div class="payment-container">


        <!--======================================
                PAYMENT HEADING
        ======================================-->

        <div class="payment-heading">

            <span>SECURE CHECKOUT</span>

            <h1>Make Payment</h1>

            <p>
                Complete your payment for your Glamour Gems order.
            </p>

        </div>


        <div class="payment-card">


            <!--======================================
                    ORDER INFORMATION
            ======================================-->

            <div class="order-info">

                <div class="order-info-row">

                    <span>Order Number</span>

                    <span>
                        <?php echo htmlspecialchars($order['order_number']); ?>
                    </span>

                </div>


                <div class="order-info-row">

                    <span>Order Status</span>

                    <span>
                        <?php echo htmlspecialchars($order['order_status']); ?>
                    </span>

                </div>


                <div class="order-info-row total-row">

                    <span>Total Amount</span>

                    <span>
                        ₹<?php echo number_format($order['total_amount'], 2); ?>
                    </span>

                </div>

            </div>


            <!--======================================
                    ERROR MESSAGE
            ======================================-->

            <?php if ($error !== ""): ?>

                <div class="error-message">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?php echo htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>


            <!--======================================
                    PAYMENT FORM
            ======================================-->

            <form method="POST" action="">


                <!--======================================
                    PAYMENT METHODS
                ======================================-->

                <h2 class="payment-title">
                    Select Payment Method
                </h2>


                <div class="payment-methods">


                    <div class="payment-method">

                        <label>

                            <input
                                type="radio"
                                name="payment_method"
                                value="card"
                                onchange="showPaymentDetails('card')"
                            >

                            <i class="fa-solid fa-credit-card"></i>

                            Credit / Debit Card

                        </label>

                    </div>


                    <div class="payment-method">

                        <label>

                            <input
                                type="radio"
                                name="payment_method"
                                value="upi"
                                onchange="showPaymentDetails('upi')"
                            >

                            <i class="fa-solid fa-mobile-screen-button"></i>

                            UPI

                        </label>

                    </div>


                    <div class="payment-method">

                        <label>

                            <input
                                type="radio"
                                name="payment_method"
                                value="netbanking"
                                onchange="showPaymentDetails('netbanking')"
                            >

                            <i class="fa-solid fa-building-columns"></i>

                            Net Banking

                        </label>

                    </div>


                    <div class="payment-method">

                        <label>

                            <input
                                type="radio"
                                name="payment_method"
                                value="cod"
                                onchange="showPaymentDetails('cod')"
                            >

                            <i class="fa-solid fa-truck"></i>

                            Cash on Delivery

                        </label>

                    </div>


                </div>


                <!--======================================
                    CARD DETAILS
                ======================================-->

                <div
                    class="payment-details"
                    id="card-details"
                >

                    <h3>Card Details</h3>

                    <div class="form-group">

                        <label>Card Number</label>

                        <input
                            type="text"
                            name="card_number"
                            placeholder="1234 5678 9012 3456"
                            maxlength="19"
                        >

                    </div>


                    <div class="card-row">

                        <div class="form-group">

                            <label>Expiry Date</label>

                            <input
                                type="text"
                                name="expiry"
                                placeholder="MM/YY"
                                maxlength="5"
                            >

                        </div>


                        <div class="form-group">

                            <label>CVV</label>

                            <input
                                type="password"
                                name="cvv"
                                placeholder="123"
                                maxlength="3"
                            >

                        </div>

                    </div>

                </div>


                <!--======================================
                    UPI DETAILS
                ======================================-->

                <div
                    class="payment-details"
                    id="upi-details"
                >

                    <h3>UPI Details</h3>

                    <div class="form-group">

                        <label>UPI ID</label>

                        <input
                            type="text"
                            name="upi_id"
                            placeholder="example@upi"
                        >

                    </div>

                </div>


                <!--======================================
                    NET BANKING DETAILS
                ======================================-->

                <div
                    class="payment-details"
                    id="netbanking-details"
                >

                    <h3>Net Banking</h3>

                    <div class="form-group">

                        <label>Select Bank</label>

                        <select name="bank">

                            <option value="">
                                Select your bank
                            </option>

                            <option value="SBI">
                                State Bank of India
                            </option>

                            <option value="HDFC">
                                HDFC Bank
                            </option>

                            <option value="ICICI">
                                ICICI Bank
                            </option>

                            <option value="Axis">
                                Axis Bank
                            </option>

                        </select>

                    </div>

                </div>


                <!--======================================
                    COD DETAILS
                ======================================-->

                <div
                    class="payment-details"
                    id="cod-details"
                >

                    <h3>Cash on Delivery</h3>

                    <p style="font-size:13px; color:#777; line-height:1.7;">

                        Your order will be delivered to your shipping
                        address. Payment will be collected at the time
                        of delivery.

                    </p>

                </div>


                <!--======================================
                    PROCEED BUTTON
                ======================================-->

                <button
                    type="submit"
                    class="pay-btn"
                >

                    <i class="fa-solid fa-lock"></i>

                    Proceed to Pay

                </button>


            </form>


            <a href="index.php" class="back-btn">

                <i class="fa-solid fa-arrow-left"></i>

                Back to Home

            </a>

        </div>

    </div>

</section>


<!--======================================
            PAYMENT JAVASCRIPT
======================================-->

<script>

function showPaymentDetails(method) {

    const details = document.querySelectorAll(".payment-details");

    details.forEach(function(detail) {

        detail.classList.remove("active");

    });


    const selectedDetails =
        document.getElementById(method + "-details");

    if (selectedDetails) {

        selectedDetails.classList.add("active");

    }

}

</script>


</body>

</html>