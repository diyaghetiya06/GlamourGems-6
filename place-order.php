<?php

require_once 'config/config.php';
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit;
}


/*======================================
        GET CUSTOMER DETAILS
======================================*/

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$pincode = trim($_POST['pincode'] ?? '');


/*======================================
        VALIDATION
======================================*/

if (
    $name === '' ||
    $email === '' ||
    $phone === '' ||
    $address === '' ||
    $city === '' ||
    $pincode === ''
) {
    die("Please fill all delivery details.");
}


/*======================================
        SHIPPING ADDRESS
======================================*/

$shipping_address = $address . ", " . $city . " - " . $pincode;


/*======================================
        GET CART PRODUCTS
======================================*/

$sql = "
    SELECT
        cart.product_id,
        cart.quantity,
        products.price,
        products.sale_price
    FROM cart
    INNER JOIN products
        ON cart.product_id = products.id
    WHERE cart.user_id = ?
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Cart Query Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result(
    $stmt,
    $product_id,
    $quantity,
    $price,
    $sale_price
);

$cart_items = [];
$total_amount = 0;

while (mysqli_stmt_fetch($stmt)) {

    $final_price = !empty($sale_price)
        ? $sale_price
        : $price;

    $item_total = $final_price * $quantity;

    $total_amount += $item_total;

    $cart_items[] = [
        'product_id' => $product_id,
        'quantity' => $quantity,
        'price' => $final_price
    ];
}

mysqli_stmt_close($stmt);


if (empty($cart_items)) {
    header("Location: cart.php");
    exit;
}


/*======================================
        CREATE ORDER NUMBER
======================================*/

$order_number = 'GG' . date('YmdHis') . rand(100, 999);


/*======================================
        ORDER DETAILS
======================================*/

$payment_method = 'Credit Card';
$payment_status = 'Pending';
$order_status = 'Pending';


/*======================================
        START TRANSACTION
======================================*/

mysqli_begin_transaction($conn);


/*======================================
        INSERT ORDER
======================================*/

$order_stmt = mysqli_prepare(
    $conn,
    "INSERT INTO orders
    (
        order_number,
        customer_name,
        customer_email,
        customer_phone,
        shipping_address,
        total_amount,
        payment_method,
        payment_status,
        order_status
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$order_stmt) {

    mysqli_rollback($conn);

    die("Order Query Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $order_stmt,
    "sssssisss",
    $order_number,
    $name,
    $email,
    $phone,
    $shipping_address,
    $total_amount,
    $payment_method,
    $payment_status,
    $order_status
);

if (!mysqli_stmt_execute($order_stmt)) {

    mysqli_rollback($conn);

    mysqli_stmt_close($order_stmt);

    die("Unable to place order: " . mysqli_error($conn));
}

$order_id = mysqli_insert_id($conn);

mysqli_stmt_close($order_stmt);

/*======================================
        SAVE ORDER ITEMS
======================================*/

foreach ($cart_items as $item) {

    $product_id = (int) $item['product_id'];
    $quantity = (int) $item['quantity'];
    $price = (float) $item['price'];
    $product_total = $price * $quantity;


    /* GET PRODUCT NAME */

    $product_stmt = mysqli_prepare(
        $conn,
        "SELECT name
         FROM products
         WHERE id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $product_stmt,
        "i",
        $product_id
    );

    mysqli_stmt_execute($product_stmt);

    mysqli_stmt_bind_result(
        $product_stmt,
        $product_name
    );

    mysqli_stmt_fetch($product_stmt);

    mysqli_stmt_close($product_stmt);


    /* INSERT ORDER ITEM */

    $item_stmt = mysqli_prepare(
        $conn,
        "INSERT INTO order_items
        (
            order_id,
            product_id,
            product_name,
            price,
            quantity,
            total
        )
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    if (!$item_stmt) {

        mysqli_rollback($conn);

        die("Order Item Query Error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param(
        $item_stmt,
        "iisidi",
        $order_id,
        $product_id,
        $product_name,
        $price,
        $quantity,
        $product_total
    );

    if (!mysqli_stmt_execute($item_stmt)) {

        mysqli_rollback($conn);

        mysqli_stmt_close($item_stmt);

        die("Unable to save order items: " . mysqli_error($conn));
    }

    mysqli_stmt_close($item_stmt);
}

/*======================================
        CLEAR CART
======================================*/

$clear_stmt = mysqli_prepare(
    $conn,
    "DELETE FROM cart WHERE user_id = ?"
);

if (!$clear_stmt) {

    mysqli_rollback($conn);

    die("Cart Clear Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $clear_stmt,
    "i",
    $user_id
);

if (!mysqli_stmt_execute($clear_stmt)) {

    mysqli_rollback($conn);

    mysqli_stmt_close($clear_stmt);

    die("Unable to clear cart.");
}

mysqli_stmt_close($clear_stmt);


/*======================================
        COMPLETE ORDER
======================================*/

mysqli_commit($conn);


/*======================================
        REDIRECT
======================================*/

header(
    "Location: order-success.php?order_id=" . $order_id
);

exit;

?>