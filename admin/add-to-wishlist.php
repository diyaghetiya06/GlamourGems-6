<?php

session_start();

require_once(__DIR__ . '/config/database.php');


/*======================================
        JSON RESPONSE
======================================*/

header('Content-Type: application/json');


/*======================================
        CHECK LOGIN
======================================*/

if (!isset($_SESSION['user_id'])) {

    echo json_encode([
        'success' => false,
        'login_required' => true,
        'message' => 'Please login first.'
    ]);

    exit;
}


$user_id = (int) $_SESSION['user_id'];


/*======================================
        GET PRODUCT / NEW ARRIVAL ID
======================================*/

$product_id = isset($_POST['product_id'])
    ? (int) $_POST['product_id']
    : 0;

$new_arrival_id = isset($_POST['new_arrival_id'])
    ? (int) $_POST['new_arrival_id']
    : 0;


/*======================================
        VALIDATE ID
======================================*/

if ($product_id <= 0 && $new_arrival_id <= 0) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid item.'
    ]);

    exit;
}


/*======================================
        NEW ARRIVAL WISHLIST
======================================*/

if ($new_arrival_id > 0) {

    /* Check New Arrival exists */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id
         FROM new_arrivals
         WHERE id = ?
         AND status = 'active'"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $new_arrival_id
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_bind_result(
        $stmt,
        $arrival_exists
    );

    $found = mysqli_stmt_fetch($stmt);

    mysqli_stmt_close($stmt);


    if (!$found) {

        echo json_encode([
            'success' => false,
            'message' => 'New Arrival not found.'
        ]);

        exit;
    }


    /* Check existing wishlist */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id
         FROM wishlist
         WHERE user_id = ?
         AND new_arrival_id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $user_id,
        $new_arrival_id
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_bind_result(
        $stmt,
        $wishlist_id
    );

    $exists = mysqli_stmt_fetch($stmt);

    mysqli_stmt_close($stmt);


    /* Remove */

    if ($exists) {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM wishlist
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $wishlist_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        $action = "removed";

    }

    /* Add */

    else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO wishlist
             (user_id, product_id, new_arrival_id)
             VALUES (?, NULL, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $user_id,
            $new_arrival_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        $action = "added";
    }

}


/*======================================
        NORMAL PRODUCT WISHLIST
======================================*/

else {

    /* Check product exists */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id
         FROM products
         WHERE id = ?
         AND status = 'active'"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $product_id
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_bind_result(
        $stmt,
        $product_exists
    );

    $found = mysqli_stmt_fetch($stmt);

    mysqli_stmt_close($stmt);


    if (!$found) {

        echo json_encode([
            'success' => false,
            'message' => 'Product not found.'
        ]);

        exit;
    }


    /* Check existing wishlist */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id
         FROM wishlist
         WHERE user_id = ?
         AND product_id = ?
         AND new_arrival_id IS NULL"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $user_id,
        $product_id
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_bind_result(
        $stmt,
        $wishlist_id
    );

    $exists = mysqli_stmt_fetch($stmt);

    mysqli_stmt_close($stmt);


    /* Remove */

    if ($exists) {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM wishlist
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $wishlist_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        $action = "removed";

    }

    /* Add */

    else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO wishlist
             (user_id, product_id, new_arrival_id)
             VALUES (?, ?, NULL)"
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

}


/*======================================
        GET WISHLIST COUNT
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*)
     FROM wishlist
     WHERE user_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result(
    $stmt,
    $count
);

mysqli_stmt_fetch($stmt);

mysqli_stmt_close($stmt);


/*======================================
        SUCCESS RESPONSE
======================================*/

echo json_encode([
    'success' => true,
    'action' => $action,
    'count' => (int) $count
]);

exit;

?>