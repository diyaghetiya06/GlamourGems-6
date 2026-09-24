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
            GET DATA
======================================*/

$cart_id = isset($_POST['cart_id'])
    ? (int) $_POST['cart_id']
    : 0;

$action = $_POST['action'] ?? '';


if ($cart_id <= 0) {
    header("Location: cart.php");
    exit;
}


/*======================================
          GET CURRENT QUANTITY
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT quantity
     FROM cart
     WHERE id = ? AND user_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $cart_id,
    $user_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result(
    $stmt,
    $quantity
);

if (!mysqli_stmt_fetch($stmt)) {

    mysqli_stmt_close($stmt);

    header("Location: cart.php");
    exit;
}

mysqli_stmt_close($stmt);


/*======================================
          UPDATE QUANTITY
======================================*/

if ($action === 'increase') {

    $new_quantity = $quantity + 1;

} elseif ($action === 'decrease') {

    $new_quantity = $quantity - 1;

} else {

    header("Location: cart.php");
    exit;
}


/*======================================
            REMOVE IF ZERO
======================================*/

if ($new_quantity <= 0) {

    $delete = mysqli_prepare(
        $conn,
        "DELETE FROM cart
         WHERE id = ? AND user_id = ?"
    );

    mysqli_stmt_bind_param(
        $delete,
        "ii",
        $cart_id,
        $user_id
    );

    mysqli_stmt_execute($delete);

    mysqli_stmt_close($delete);

} else {

    $update = mysqli_prepare(
        $conn,
        "UPDATE cart
         SET quantity = ?
         WHERE id = ? AND user_id = ?"
    );

    mysqli_stmt_bind_param(
        $update,
        "iii",
        $new_quantity,
        $cart_id,
        $user_id
    );

    mysqli_stmt_execute($update);

    mysqli_stmt_close($update);
}


/*======================================
              CART PAGE
======================================*/

header("Location: cart.php");
exit;

?>