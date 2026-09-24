<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');


/*======================================
        GET CURATED LOOK ID
======================================*/

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {

    header('Location: ' . ADMIN_URL . 'curated-looks.php');
    exit;

}


/*======================================
        FETCH CURATED LOOK
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT image
     FROM curated_looks
     WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$look = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$look) {

    set_flash(
        'error',
        'Curated Look not found.'
    );

    header('Location: ' . ADMIN_URL . 'curated-looks.php');
    exit;

}


/*======================================
        DELETE DATABASE RECORD
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM curated_looks
     WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    set_flash(
        'success',
        'Curated Look deleted successfully.'
    );

} else {

    set_flash(
        'error',
        'Failed to delete Curated Look.'
    );

    mysqli_stmt_close($stmt);

}


/*======================================
        REDIRECT
======================================*/

header(
    'Location: ' .
    ADMIN_URL .
    'curated-looks.php'
);

exit;

?>