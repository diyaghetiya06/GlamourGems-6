<?php

require_once 'config/config.php';
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$sql = "
    SELECT 
        cart.quantity,
        products.name,
        products.image,
        products.price,
        products.sale_price
    FROM cart
    INNER JOIN products 
        ON cart.product_id = products.id
    WHERE cart.user_id = ?
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$items = [];
$subtotal = 0;
$total_items = 0;

while ($row = mysqli_fetch_assoc($result)) {

    $price = !empty($row['sale_price'])
        ? $row['sale_price']
        : $row['price'];

    $row['final_price'] = $price;
    $row['item_total'] = $price * $row['quantity'];

    $subtotal += $row['item_total'];
    $total_items += $row['quantity'];

    $items[] = $row;
}

mysqli_stmt_close($stmt);

if (empty($items)) {
    header("Location: cart.php");
    exit;
}

$shipping = 0;
$grand_total = $subtotal + $shipping;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Checkout | Glamour Gems</title>

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

        .checkout-page {
            padding: 60px 6%;
        }

        .checkout-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .checkout-title h1 {
            font-family: 'Cinzel', serif;
            color: #234B43;
            font-size: 38px;
        }

        .checkout-wrapper {
            max-width: 1100px;
            margin: auto;
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
        }

        .checkout-box,
        .order-summary {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.07);
        }

        .checkout-box h2,
        .order-summary h2 {
            font-family: 'Cinzel', serif;
            color: #234B43;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            margin-bottom: 7px;
            color: #555;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            outline: none;
        }

        .form-group textarea {
            height: 100px;
            resize: none;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #C8A165;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 14px 0;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .order-item-name {
            color: #555;
        }

        .order-item-price {
            color: #234B43;
            font-weight: 600;
            white-space: nowrap;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 14px 0;
            font-size: 14px;
        }

        .summary-total {
            border-top: 1px solid #ddd;
            margin-top: 10px;
            padding-top: 18px;
            display: flex;
            justify-content: space-between;
            color: #234B43;
            font-size: 18px;
            font-weight: 600;
        }

        .place-order-btn {
            width: 100%;
            border: none;
            margin-top: 25px;
            padding: 14px;
            background: #C8A165;
            color: #fff;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        .place-order-btn:hover {
            background: #b28d55;
        }

        @media (max-width: 800px) {

            .checkout-wrapper {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="checkout-page">

    <div class="checkout-title">

        <h1>Checkout</h1>

    </div>


    <div class="checkout-wrapper">


        <!--======================================
                CUSTOMER DETAILS
        ======================================-->

        <div class="checkout-box">

            <h2>Delivery Details</h2>

            <form action="place-order.php" method="POST">

                <div class="form-group">

                    <label>Full Name</label>

                    <input
                        type="text"
                        name="name"
                        value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Email</label>

                    <input
                        type="email"
                        name="email"
                        value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Phone</label>

                    <input
                        type="text"
                        name="phone"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Address</label>

                    <textarea
                        name="address"
                        required
                    ></textarea>

                </div>


                <div class="form-group">

                    <label>City</label>

                    <input
                        type="text"
                        name="city"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Pincode</label>

                    <input
                        type="text"
                        name="pincode"
                        required
                    >

                </div>


                <button type="submit" class="place-order-btn">
                    Place Order
                </button>

            </form>

        </div>


        <!--======================================
                ORDER SUMMARY
        ======================================-->

        <div class="order-summary">

            <h2>Your Order</h2>


            <?php foreach ($items as $item): ?>

                <div class="order-item">

                    <span class="order-item-name">

                        <?php echo htmlspecialchars($item['name']); ?>

                        × <?php echo $item['quantity']; ?>

                    </span>

                    <span class="order-item-price">

                        ₹<?php echo number_format($item['item_total'], 2); ?>

                    </span>

                </div>

            <?php endforeach; ?>


            <div class="summary-row">

                <span>Items</span>

                <span><?php echo $total_items; ?></span>

            </div>


            <div class="summary-row">

                <span>Subtotal</span>

                <span>
                    ₹<?php echo number_format($subtotal, 2); ?>
                </span>

            </div>


            <div class="summary-row">

                <span>Shipping</span>

                <span>FREE</span>

            </div>


            <div class="summary-total">

                <span>Total</span>

                <span>
                    ₹<?php echo number_format($grand_total, 2); ?>
                </span>

            </div>

        </div>

    </div>

</div>

</body>

</html>