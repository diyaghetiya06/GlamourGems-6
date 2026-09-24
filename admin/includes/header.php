<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($page_title)) {
    $page_title = 'Dashboard';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo $page_title; ?> | Glamour Gems Admin
    </title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome Icons -->
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Admin CSS -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">

</head>

<body>

<?php
include(__DIR__ . '/sidebar.php');
?>

<div id="content-wrapper">

    <header class="top-header">
        <div class="d-flex align-items-center justify-content-between gap-3 w-100">
            <h5 class="mb-0">GLAMOUR GEMS</h5>
            <a href="<?php echo BASE_URL; ?>switch-to-user.php" class="btn btn-sm btn-outline-light">
                <i class="fa-solid fa-store me-1"></i> Switch to User Panel
            </a>
        </div>
    </header>

    <main class="main-content">
 <?php display_flash(); ?>      