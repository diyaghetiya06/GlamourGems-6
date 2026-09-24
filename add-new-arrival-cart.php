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
        GET NEW ARRIVAL ID
======================================*/

$new_arrival_id = isset($_POST['new_arrival_id'])
    ? (int) $_POST['new_arrival_id']
    : 0;

if ($new_arrival_id <= 0) {
    header("Location: index.php");
    exit;
}


/*======================================
        CHECK NEW ARRIVAL
======================================*/

$check_arrival = mysqli_prepare(
    $conn,
    "SELECT id
     FROM new_arrivals
     WHERE id = ? AND status = 'active'
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $check_arrival,
    "i",
    $new_arrival_id
);

mysqli_stmt_execute($check_arrival);

mysqli_stmt_bind_result(
    $check_arrival,
    $arrival_id
);

if (!mysqli_stmt_fetch($check_arrival)) {

    mysqli_stmt_close($check_arrival);

    header("Location: index.php");
    exit;
}

mysqli_stmt_close($check_arrival);


/*======================================
          CHECK EXISTING CART
======================================*/

$check = mysqli_prepare(
    $conn,
    "SELECT id, quantity
     FROM cart
     WHERE user_id = ? AND new_arrival_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $check,
    "ii",
    $user_id,
    $new_arrival_id
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
        "INSERT INTO cart
        (user_id, new_arrival_id, quantity)
        VALUES (?, ?, 1)"
    );

    mysqli_stmt_bind_param(
        $insert,
        "ii",
        $user_id,
        $new_arrival_id
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