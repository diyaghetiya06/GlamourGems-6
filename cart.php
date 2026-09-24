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
          FETCH CART ITEMS
======================================*/

$cart_items = [];


/*======================================
          NORMAL SHOP PRODUCTS
======================================*/

$sql_products = "
    SELECT 
        cart.id AS cart_id,
        cart.quantity,
        products.id AS product_id,
        products.name,
        products.price,
        products.sale_price,
        products.image
    FROM cart
    INNER JOIN products
        ON cart.product_id = products.id
    WHERE cart.user_id = ?
      AND cart.product_id IS NOT NULL
    ORDER BY cart.created_at DESC
";

$stmt = mysqli_prepare($conn, $sql_products);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result(
    $stmt,
    $cart_id,
    $quantity,
    $product_id,
    $name,
    $price,
    $sale_price,
    $image
);

while (mysqli_stmt_fetch($stmt)) {

    $cart_items[] = [

        'cart_id' => $cart_id,

        'quantity' => $quantity,

        'product_id' => $product_id,

        'new_arrival_id' => null,

        'name' => $name,

        'price' => $price,

        'sale_price' => $sale_price,

        'old_price' => 0,

        'image' => $image,

        'item_type' => 'product'

    ];
}

mysqli_stmt_close($stmt);


/*======================================
          NEW ARRIVAL PRODUCTS
======================================*/

$sql_arrivals = "
    SELECT 
        cart.id AS cart_id,
        cart.quantity,
        new_arrivals.id AS new_arrival_id,
        new_arrivals.title,
        new_arrivals.price,
        new_arrivals.old_price,
        new_arrivals.image
    FROM cart
    INNER JOIN new_arrivals
        ON cart.new_arrival_id = new_arrivals.id
    WHERE cart.user_id = ?
      AND cart.new_arrival_id IS NOT NULL
    ORDER BY cart.created_at DESC
";

$stmt = mysqli_prepare($conn, $sql_arrivals);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result(
    $stmt,
    $cart_id,
    $quantity,
    $new_arrival_id,
    $title,
    $arrival_price,
    $old_price,
    $image
);

while (mysqli_stmt_fetch($stmt)) {

    $cart_items[] = [

        'cart_id' => $cart_id,

        'quantity' => $quantity,

        'product_id' => null,

        'new_arrival_id' => $new_arrival_id,

        'name' => $title,

        'price' => $arrival_price,

        'sale_price' => $arrival_price,

        'old_price' => $old_price,

        'image' => $image,

        'item_type' => 'new_arrival'

    ];
}

mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Shopping Cart | Glamour Gems</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Poppins:wght@400;500;600&display=swap"
          rel="stylesheet">


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

        .cart-page {
            padding: 60px 6%;
            min-height: 100vh;
        }

        .cart-title {
            text-align: center;
            margin-bottom: 45px;
        }

        .cart-title h1 {
            font-family: 'Cinzel', serif;
            color: #234B43;
            font-size: 38px;
            margin-bottom: 8px;
        }

        .cart-title p {
            color: #777;
            font-size: 14px;
        }

        .back-home-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 18px;
            padding: 11px 18px;
            border: 1px solid #C8A165;
            border-radius: 8px;
            color: #234B43;
            background: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: .25s ease;
        }

        .back-home-btn:hover {
            color: #fff;
            background: #234B43;
            border-color: #234B43;
            transform: translateY(-2px);
        }

        .cart-wrapper {
            max-width: 1200px;
            margin: auto;
        }

        .cart-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
            align-items: start;
        }

        .cart-items {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.07);
        }

        .cart-item {
            display: grid;
            grid-template-columns: 110px 1fr auto;
            gap: 20px;
            align-items: center;
            padding: 22px;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-image {
            width: 110px;
            height: 110px;
            border-radius: 8px;
            overflow: hidden;
            background: #f5f3ee;
        }

        .cart-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-details h3 {
            font-family: 'Cinzel', serif;
            color: #234B43;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .cart-price {
            color: #C8A165;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .quantity {
            font-size: 14px;
            color: #666;
        }

        .quantity strong {
            color: #234B43;
        }

        /*======================================
        QUANTITY CONTROL
======================================*/

.quantity-control {
    display: flex;
    align-items: center;
    gap: 12px;
}

.quantity-control form {
    margin: 0;
}

.quantity-control button {
    width: 30px;
    height: 30px;
    border: 1px solid #d8d0c3;
    background: #fff;
    color: #234B43;
    border-radius: 5px;
    font-size: 18px;
    cursor: pointer;
}

.quantity-control button:hover {
    background: #234B43;
    color: #fff;
}

.quantity-control span {
    min-width: 20px;
    text-align: center;
    font-weight: 600;
    color: #234B43;
}

        .item-total {
            text-align: right;
            min-width: 110px;
        }

        .item-total p {
            font-size: 16px;
            font-weight: 600;
            color: #234B43;
            margin-bottom: 12px;
        }

        .remove-btn {
            color: #a94442;
            text-decoration: none;
            font-size: 13px;
        }

        .remove-btn:hover {
            color: #7d2624;
        }

        .cart-summary {
            background: #234B43;
            color: #fff;
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.10);
        }

        .cart-summary h2 {
            font-family: 'Cinzel', serif;
            font-size: 24px;
            margin-bottom: 25px;
            color: #C8A165;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            font-size: 14px;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            padding-top: 20px;
            margin-top: 8px;
            font-size: 18px;
            font-weight: 600;
        }

        .checkout-btn {
            display: block;
            width: 100%;
            text-align: center;
            margin-top: 25px;
            padding: 14px;
            background: #C8A165;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }

        .checkout-btn:hover {
            background: #b28d55;
        }

        .continue-shopping {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
        }

        .empty-cart {
            max-width: 600px;
            margin: 80px auto;
            background: #fff;
            padding: 55px 30px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.07);
        }

        .empty-cart i {
            font-size: 45px;
            color: #C8A165;
            margin-bottom: 20px;
        }

        .empty-cart h2 {
            font-family: 'Cinzel', serif;
            color: #234B43;
            margin-bottom: 10px;
        }

        .empty-cart p {
            color: #777;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .empty-cart a {
            display: inline-block;
            padding: 12px 25px;
            background: #234B43;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
        }

        @media (max-width: 800px) {

            .cart-content {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 600px) {

            .cart-page {
                padding: 40px 4%;
            }

            .cart-item {
                grid-template-columns: 80px 1fr;
            }

            .cart-image {
                width: 80px;
                height: 80px;
            }

            .item-total {
                grid-column: 2;
                text-align: left;
            }

        }

    </style>

</head>

<body>


<!--======================================
              CART PAGE
======================================-->

<section class="cart-page">

    <div class="cart-wrapper">

        <div class="cart-title">

            <h1>Shopping Cart</h1>

            <p>Review your selected jewellery before checkout.</p>

            <a href="index.php" class="back-home-btn">
                <i class="fa-solid fa-arrow-left"></i> Back to Home
            </a>

        </div>


        <?php if (empty($cart_items)): ?>

            <div class="empty-cart">

                <i class="fa-solid fa-bag-shopping"></i>

                <h2>Your Cart is Empty</h2>

                <p>
                    You haven't added any products to your cart yet.
                </p>

                <a href="shop.php">
                    Continue Shopping
                </a>

            </div>

        <?php else: ?>


            <?php

            $grand_total = 0;
            $total_items = 0;

            ?>


            <div class="cart-content">


                <!--======================================
                        CART ITEMS
                ======================================-->

                <div class="cart-items">

                    <?php foreach ($cart_items as $item): ?>

                        <?php

                        $unit_price = !empty($item['sale_price'])
                            ? $item['sale_price']
                            : $item['price'];

                        $item_total = $unit_price * $item['quantity'];

                        $grand_total += $item_total;

                        $total_items += $item['quantity'];

                        ?>


                        <div class="cart-item">

                                <div class="cart-image">

                                    <?php
                                    if ($item['item_type'] === 'new_arrival') {
                                        $cart_image_path = 'assets/images/arrivals/' . $item['image'];
                                    } else {
                                        $cart_image_path = 'assets/images/shops/' . $item['image'];
                                    }
                                    ?>

                                    <img
                                        src="<?php echo htmlspecialchars($cart_image_path); ?>"
                                        alt="<?php echo htmlspecialchars($item['name']); ?>"
                                    >

                                </div>


                            <div class="cart-details">

                                <h3>
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </h3>

                                <div class="cart-price">

                                    ₹<?php echo number_format($unit_price, 2); ?>

                                </div>

                                <div class="quantity-control">

                                        <form action="update-cart.php" method="POST">

                                            <input
                                                type="hidden"
                                                name="cart_id"
                                                value="<?php echo $item['cart_id']; ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="decrease"
                                            >

                                            <button type="submit">−</button>

                                        </form>


                                        <span>
                                            <?php echo $item['quantity']; ?>
                                        </span>


                                        <form action="update-cart.php" method="POST">

                                            <input
                                                type="hidden"
                                                name="cart_id"
                                                value="<?php echo $item['cart_id']; ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="increase"
                                            >

                                            <button type="submit">+</button>

                                        </form>

                                    </div>

                            </div>


                            <div class="item-total">

                                <p>
                                    ₹<?php echo number_format($item_total, 2); ?>
                                </p>

                                <a
                                    href="remove-from-cart.php?id=<?php echo $item['cart_id']; ?>"
                                    class="remove-btn"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                    Remove
                                </a>

                            </div>


                        </div>

                    <?php endforeach; ?>

                </div>


                <!--======================================
                        CART SUMMARY
                ======================================-->

                <div class="cart-summary">

                    <h2>Order Summary</h2>

                    <div class="summary-row">

                        <span>Items</span>

                        <span>
                            <?php echo $total_items; ?>
                        </span>

                    </div>


                    <div class="summary-row">

                        <span>Subtotal</span>

                        <span>
                            ₹<?php echo number_format($grand_total, 2); ?>
                        </span>

                    </div>


                    <div class="summary-row">

                        <span>Shipping</span>

                        <span>Free</span>

                    </div>


                    <div class="summary-total">

                        <span>Total</span>

                        <span>
                            ₹<?php echo number_format($grand_total, 2); ?>
                        </span>

                    </div>


                   <a href="checkout.php" class="checkout-btn">
                        Proceed to Checkout
                   </a>


                    <a href="shop.php" class="continue-shopping">
                        Continue Shopping
                    </a>

                </div>


            </div>

        <?php endif; ?>

    </div>

</section>


</body>

</html>