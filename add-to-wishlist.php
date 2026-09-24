<?php

session_start();

require_once __DIR__ . "/config/database.php";

/*======================================
        JSON RESPONSE
======================================*/

header("Content-Type: application/json");


/*======================================
        LOGIN CHECK
======================================*/

if (!isset($_SESSION['user_id'])) {

    echo json_encode([
        "status" => "login"
    ]);

    exit;
}


/*======================================
        GET USER & PRODUCT
======================================*/

$user_id = (int)$_SESSION['user_id'];

$product_id = isset($_POST['product_id'])
    ? (int)$_POST['product_id']
    : 0;


/*======================================
        VALIDATE PRODUCT
======================================*/

if ($product_id <= 0) {

    echo json_encode([
        "status" => "error",
        "message" => "Invalid product."
    ]);

    exit;
}


/*======================================
        CHECK PRODUCT
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM products
     WHERE id = ?
     AND status = 'active'
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $product_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) === 0) {

    mysqli_stmt_close($stmt);

    echo json_encode([
        "status" => "error",
        "message" => "Product not found."
    ]);

    exit;
}

mysqli_stmt_close($stmt);


/*======================================
        CHECK WISHLIST
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM wishlist
     WHERE user_id = ?
     AND product_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $user_id,
    $product_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_store_result($stmt);

$exists = mysqli_stmt_num_rows($stmt) > 0;

mysqli_stmt_close($stmt);


/*======================================
        ADD / REMOVE WISHLIST
======================================*/

if ($exists) {

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM wishlist
         WHERE user_id = ?
         AND product_id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $user_id,
        $product_id
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    $action = "removed";

} else {

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO wishlist
        (user_id, product_id)
        VALUES (?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $user_id,
        $product_id
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    $action = "added";
}


/*======================================
        WISHLIST COUNT
======================================*/

$count_stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*)
     FROM wishlist
     WHERE user_id = ?"
);

mysqli_stmt_bind_param(
    $count_stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($count_stmt);

mysqli_stmt_bind_result(
    $count_stmt,
    $wishlist_count
);

mysqli_stmt_fetch($count_stmt);

mysqli_stmt_close($count_stmt);


/*======================================
        RESPONSE
======================================*/

echo json_encode([

    "status" => "success",

    "action" => $action,

    "count" => (int)$wishlist_count

]);

exit;

?>