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
            GET PRODUCT ID
======================================*/

$product_id = isset($_POST['product_id'])
    ? (int) $_POST['product_id']
    : 0;

if ($product_id <= 0) {
    header("Location: shop.php");
    exit;
}


/*======================================
          CHECK EXISTING CART
======================================*/

$check = mysqli_prepare(
    $conn,
    "SELECT id, quantity
     FROM cart
     WHERE user_id = ? AND product_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $check,
    "ii",
    $user_id,
    $product_id
);

mysqli_stmt_execute($check);

mysqli_stmt_bind_result(
    $check,
    $cart_id,
    $quantity
);


if (mysqli_stmt_fetch($check)) {

    mysqli_stmt_close($check);

    $new_quantity = $quantity + 1;

    $update = mysqli_prepare(
        $conn,
        "UPDATE cart
         SET quantity = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $update,
        "ii",
        $new_quantity,
        $cart_id
    );

    mysqli_stmt_execute($update);

    mysqli_stmt_close($update);

} else {

    mysqli_stmt_close($check);

    $insert = mysqli_prepare(
        $conn,
        "INSERT INTO cart (user_id, product_id, quantity)
         VALUES (?, ?, 1)"
    );

    mysqli_stmt_bind_param(
        $insert,
        "ii",
        $user_id,
        $product_id
    );

    mysqli_stmt_execute($insert);

    mysqli_stmt_close($insert);
}


/*======================================
              CART PAGE
======================================*/

header("Location: cart.php");
exit;

?>