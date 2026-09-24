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
            GET CART ID
======================================*/

$cart_id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($cart_id <= 0) {
    header("Location: cart.php");
    exit;
}


/*======================================
          REMOVE CART ITEM
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM cart
     WHERE id = ? AND user_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $cart_id,
    $user_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);


/*======================================
              CART PAGE
======================================*/

header("Location: cart.php");
exit;

?>