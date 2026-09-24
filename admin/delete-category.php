<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/includes/functions.php');


/*======================================
        VALIDATE CATEGORY ID
======================================*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    set_flash('danger', 'Invalid category ID.');

    header("Location: " . ADMIN_URL . "categories.php");
    exit;

}

$category_id = (int) $_GET['id'];


/*======================================
        FETCH CATEGORY
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT image FROM categories WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $category_id);

mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result($stmt, $category_image);

if (!mysqli_stmt_fetch($stmt)) {

    mysqli_stmt_close($stmt);

    set_flash('danger', 'Category not found.');

    header("Location: " . ADMIN_URL . "categories.php");
    exit;

}

mysqli_stmt_close($stmt);


/*======================================
        DELETE CATEGORY
======================================*/

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM categories WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $category_id);

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);


    /*======================================
            DELETE CATEGORY IMAGE
    ======================================*/

    if (
        !empty($category_image) &&
        $category_image !== 'category_default.jpg'
    ) {

        $image_path = __DIR__ . '/../assets/images/recipient/' . $category_image;

        if (file_exists($image_path)) {

            unlink($image_path);

        }

    }


    set_flash('success', 'Category deleted successfully.');

} else {

    mysqli_stmt_close($stmt);

    set_flash('danger', 'Failed to delete category.');

}


/*======================================
        REDIRECT
======================================*/

header("Location: " . ADMIN_URL . "categories.php");
exit;

?>