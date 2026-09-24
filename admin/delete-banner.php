<?php

require_once "includes/auth_check.php";
require_once "../config/database.php";
require_once "includes/functions.php";


/*======================================
        GET BANNER ID
======================================*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    set_flash("Invalid banner ID.", "danger");

    header("Location: banners.php");
    exit;
}

$id = (int) $_GET['id'];


/*======================================
        FETCH BANNER
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT image FROM banners WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $image);

if (!mysqli_stmt_fetch($stmt)) {

    mysqli_stmt_close($stmt);

    set_flash("Banner not found.", "danger");

    header("Location: banners.php");
    exit;
}

mysqli_stmt_close($stmt);


/*======================================
        DELETE BANNER
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM banners WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);


if (mysqli_stmt_execute($stmt)) {

    /* Delete banner image */

    if (!empty($image)) {

        $image_path = "../uploads/banners/" . $image;

        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    set_flash(
        "Banner deleted successfully.",
        "success"
    );

} else {

    set_flash(
        "Unable to delete banner.",
        "danger"
    );
}


mysqli_stmt_close($stmt);


/*======================================
        REDIRECT
======================================*/

header("Location: banners.php");
exit;

?>