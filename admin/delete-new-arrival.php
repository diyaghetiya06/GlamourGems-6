<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/includes/functions.php');


/*======================================
        VALIDATE ID
======================================*/

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {

    set_flash('danger', 'Invalid New Arrival ID.');

    header("Location: " . ADMIN_URL . "new-arrivals.php");

    exit;
}


/*======================================
        FETCH IMAGE
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT image FROM new_arrivals WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result($stmt, $image);

if (!mysqli_stmt_fetch($stmt)) {

    mysqli_stmt_close($stmt);

    set_flash('danger', 'New Arrival not found.');

    header("Location: " . ADMIN_URL . "new-arrivals.php");

    exit;
}

mysqli_stmt_close($stmt);


/*======================================
        DELETE DATABASE RECORD
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM new_arrivals WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);


if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);


    /*======================================
            DELETE IMAGE
    ======================================*/

    if (!empty($image)) {

        $image_path =
            __DIR__ .
            '/../assets/images/arrivals/' .
            $image;

        if (
            file_exists($image_path) &&
            is_file($image_path)
        ) {

            unlink($image_path);
        }
    }


    set_flash(
        'success',
        'New Arrival deleted successfully.'
    );

} else {

    mysqli_stmt_close($stmt);

    set_flash(
        'danger',
        'Failed to delete New Arrival.'
    );
}


/*======================================
        REDIRECT
======================================*/

header(
    "Location: " .
    ADMIN_URL .
    "new-arrivals.php"
);

exit;

?>