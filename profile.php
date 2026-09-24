<?php

session_start();

require_once __DIR__ . "/config/database.php";

/*======================================
        CHECK CUSTOMER LOGIN
======================================*/

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit;

}

/*======================================
        FETCH CUSTOMER DETAILS
======================================*/

$user_id = (int) $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, name, email, phone
     FROM users
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$user) {

    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile | Glamour Gems</title>

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >

    <!-- Profile CSS -->
    <link rel="stylesheet" href="assets/css/profile.css">

</head>

<body>

<!--======================================
            TOP BAR
======================================-->

<div class="top-bar">

    <div>
        <i class="fa-solid fa-location-dot"></i>
        Mumbai Store
    </div>

    <div>
        Free Shipping Above ₹999
    </div>

    <div>
        <i class="fa-solid fa-phone"></i>
        +91 98765 43210
    </div>

</div>


<!--======================================
            NAVBAR
======================================-->

<header>

    <nav class="navbar">

        <a href="index.php" class="logo">

            <img
                src="assets/images/logo/logo.png"
                alt="Glamour Gems Logo"
            >

            <span>GLAMOUR GEMS</span>

        </a>


        <ul class="nav-menu">

            <li>
                <a href="index.php">Home</a>
            </li>

            <li>
                <a href="shop.php">Shop</a>
            </li>

            <li>
                <a href="collections.php">Collections</a>
            </li>

            <li>
                <a href="about.php">About</a>
            </li>

            <li>
                <a href="contact.php">Contact</a>
            </li>

        </ul>


        <div class="nav-icons">

            <!-- Search -->
            <a href="shop.php" title="Search">
                <i class="fa-solid fa-magnifying-glass"></i>
            </a>


            <!-- Wishlist -->
            <a href="wishlist.php" title="Wishlist">
                <i class="fa-regular fa-heart"></i>
            </a>


            <!-- Cart -->
            <a href="cart.php" title="Cart">
                <i class="fa-solid fa-bag-shopping"></i>
            </a>


            <!-- Profile -->
            <a
                href="profile.php"
                class="active"
                title="My Profile"
            >
                <i class="fa-regular fa-user"></i>
            </a>

        </div>

    </nav>

</header>


<!--======================================
            PROFILE SECTION
======================================-->

<section class="profile-section">

    <div class="profile-container">


        <!--======================================
                PROFILE HEADER
        ======================================-->

        <div class="profile-heading">

            <span>MY ACCOUNT</span>

            <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>

            <p>
                Manage your Glamour Gems account and personal information.
            </p>

            <a href="index.php" class="back-home-btn">
                <i class="fa-solid fa-arrow-left"></i> Back to Home
            </a>

        </div>


        <!--======================================
                PROFILE CARD
        ======================================-->

        <div class="profile-card">


            <!-- Profile Icon -->

            <div class="profile-avatar">

                <i class="fa-solid fa-user"></i>

            </div>


            <div class="profile-info">

                <h2>
                    <?php echo htmlspecialchars($user['name']); ?>
                </h2>

                <div class="profile-detail">

                    <i class="fa-solid fa-envelope"></i>

                    <div>

                        <span>Email</span>

                        <p>
                            <?php echo htmlspecialchars($user['email']); ?>
                        </p>

                    </div>

                </div>


                <div class="profile-detail">

                    <i class="fa-solid fa-phone"></i>

                    <div>

                        <span>Phone</span>

                        <p>
                            <?php
                            echo !empty($user['phone'])
                                ? htmlspecialchars($user['phone'])
                                : "Not Added";
                            ?>
                        </p>

                    </div>

                </div>

            </div>

        </div>


        <!--======================================
                ACCOUNT ACTIONS
        ======================================-->

        <div class="account-actions">


            <a href="orders.php" class="account-action">

                <div class="action-icon">

                    <i class="fa-solid fa-box"></i>

                </div>

                <div>

                    <h3>My Orders</h3>

                    <p>View your orders and purchases</p>

                </div>

                <i class="fa-solid fa-chevron-right arrow"></i>

            </a>


            <a href="wishlist.php" class="account-action">

                <div class="action-icon">

                    <i class="fa-regular fa-heart"></i>

                </div>

                <div>

                    <h3>My Wishlist</h3>

                    <p>View your saved jewellery</p>

                </div>

                <i class="fa-solid fa-chevron-right arrow"></i>

            </a>


            <a href="logout.php" class="account-action logout-action">

                <div class="action-icon">

                    <i class="fa-solid fa-right-from-bracket"></i>

                </div>

                <div>

                    <h3>Logout</h3>

                    <p>Sign out from your account</p>

                </div>

                <i class="fa-solid fa-chevron-right arrow"></i>

            </a>

        </div>

    </div>

</section>


<!--======================================
            FOOTER
======================================-->

<footer class="profile-footer">

    <p>
        © 2026 Glamour Gems. All Rights Reserved.
    </p>

</footer>

</body>

</html>