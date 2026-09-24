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
        GET USER & NEW ARRIVAL ID
======================================*/

$user_id = (int)$_SESSION['user_id'];

$new_arrival_id = isset($_POST['new_arrival_id'])
    ? (int)$_POST['new_arrival_id']
    : 0;


/*======================================
        VALIDATE ID
======================================*/

if ($new_arrival_id <= 0) {

    echo json_encode([
        "status" => "error",
        "message" => "Invalid new arrival."
    ]);

    exit;
}


/*======================================
        CHECK NEW ARRIVAL
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM new_arrivals
     WHERE id = ?
     AND status = 'active'
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $new_arrival_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) === 0) {

    mysqli_stmt_close($stmt);

    echo json_encode([
        "status" => "error",
        "message" => "New arrival not found."
    ]);

    exit;
}

mysqli_stmt_close($stmt);


/*======================================
        CHECK EXISTING WISHLIST
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM new_arrival_wishlist
     WHERE user_id = ?
     AND new_arrival_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $user_id,
    $new_arrival_id
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
        "DELETE FROM new_arrival_wishlist
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

    mysqli_stmt_close($stmt);

    $action = "removed";

} else {

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO new_arrival_wishlist
        (user_id, new_arrival_id)
        VALUES (?, ?)"
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


/*======================================
        WISHLIST COUNT
======================================*/

$count_stmt = mysqli_prepare(
    $conn,
    "SELECT
        (
            SELECT COUNT(*)
            FROM wishlist
            WHERE user_id = ?
        )
        +
        (
            SELECT COUNT(*)
            FROM new_arrival_wishlist
            WHERE user_id = ?
        )
        AS total_count"
);

mysqli_stmt_bind_param(
    $count_stmt,
    "ii",
    $user_id,
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