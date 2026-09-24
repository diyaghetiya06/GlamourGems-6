<!DOCTYPE html>
<html lang="en">

<?php

require_once(__DIR__ . '/config/database.php');

/*======================================
        GOLD COLLECTION PRODUCTS
======================================*/

$gold_collection_query = mysqli_query(
    $conn,
    "SELECT *
     FROM collection_gold_products
     WHERE status = 'active'
     ORDER BY display_order ASC, id ASC"
);

/*======================================
        SILVER COLLECTION PRODUCTS
======================================*/

$silver_collection_query = mysqli_query(
    $conn,
    "SELECT *
     FROM collection_silver_products
     WHERE status = 'active'
     ORDER BY display_order ASC, id ASC"
);

$rose_gold_collection_query = mysqli_query(
    $conn,
    "SELECT *
     FROM collection_rose_gold_products
     WHERE status = 'active'
     ORDER BY display_order ASC, id ASC"
);

$diamond_collection_query = mysqli_query(
    $conn,
    "SELECT *
     FROM collection_diamond_products
     WHERE status = 'active'
     ORDER BY display_order ASC, id ASC"
);

/*======================================
        BRIDAL COLLECTION PRODUCTS
======================================*/

$bridal_collection_query = mysqli_query(
    $conn,
    "SELECT *
     FROM collection_bridal_products
     WHERE status = 'active'
     ORDER BY display_order ASC, id ASC"
);

/*======================================
        GROOM COLLECTION PRODUCTS
======================================*/

$groom_collection_query = mysqli_query(
    $conn,
    "SELECT *
     FROM collection_groom_products
     WHERE status = 'active'
     ORDER BY display_order ASC, id ASC"
);

/*======================================
        KIDS COLLECTION PRODUCTS
======================================*/

$kids_collection_query = mysqli_query(
    $conn,
    "SELECT *
     FROM collection_kids_products
     WHERE status = 'active'
     ORDER BY display_order ASC, id ASC"
);
/*======================================
        INDIAN PRICE FORMAT
======================================*/

function indianPrice($price)
{
    $price = (float) $price;

    $number = number_format($price, 0, '.', '');

    $lastThree = substr($number, -3);

    $remaining = substr($number, 0, -3);

    if ($remaining !== '') {

        $remaining = preg_replace(
            '/\B(?=(\d{2})+(?!\d))/',
            ',',
            $remaining
        );

        return $remaining . ',' . $lastThree;
    }

    return $lastThree;
}

?>

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Collections | Glamour Gems</title>

    <!-- Google Fonts -->

    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <!-- Home CSS -->

    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Collections CSS -->

    <link rel="stylesheet" href="assets/css/collections.css">

</head>

<body>

<!-- ===========================
        TOP BAR
=========================== -->

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

<!-- ===========================
            NAVBAR
=========================== -->

<header>

<nav class="navbar">

    <a href="index.php" class="logo">
    <img src="assets/images/logo/logo.png" alt="Glamour Gems Logo">
    <span>GLAMOUR GEMS</span>
   </a>

    <ul class="nav-menu">

        <li><a href="index.php">Home</a></li>

        <li><a href="shop.php">Shop</a></li>

        <li><a href="collections.php" class="active">Collections</a></li>

        <li><a href="about.php">About</a></li>

        <li><a href="contact.php">Contact</a></li>

    </ul>

    <div class="nav-icons">

        <a href="#"><i class="fa-solid fa-magnifying-glass"></i></a>

       <a href="shop.php" class="wishlist-nav">
    <i class="fa-regular fa-heart"></i>
    <span id="wishlistCount">0</span>
</a>

       <a href="cart.php">
    <i class="fa-solid fa-bag-shopping"></i>
    <span id="cartCount">0</span>
</a>

        <a href="login.php">
    <i class="fa-regular fa-user"></i>
</a>

    </div>

</nav>

</header>

<!-- =========================
        COLLECTION HERO
========================= -->

<section class="collections-hero">

    <div class="collections-hero-overlay">

        <h1>Our Exclusive Collections</h1>

        <p>
            Discover handcrafted jewellery collections designed
            for every occasion with timeless elegance.
        </p>

    </div>

</section>
<!-- ===========================
        BREADCRUMB
=========================== -->

<section class="collection-breadcrumb">

    <div class="container">

        <a href="index.php">Home</a>

        <span>/</span>

        <span>Collections</span>

    </div>

</section>
<!--======================================
        NO SEARCH RESULT MESSAGE
======================================-->

<div class="collection-no-result" style="display: none;">

    <h3>No Jewellery Found</h3>

    <p>
        Try searching for another jewellery type
        or collection.
    </p>

</div>
<!--=================================
        GOLD COLLECTION
==================================-->

<section class="collection-section" id="gold">

    <div class="container">

        <div class="section-title">

            <h2>Gold Collection</h2>

            <p>
                Discover our premium handcrafted gold jewellery for men and women.
            </p>

        </div>

          <div class="collection-grid">

    <?php if (mysqli_num_rows($gold_collection_query) > 0): ?>

        <?php while ($gold_product = mysqli_fetch_assoc($gold_collection_query)): ?>

            <div class="collection-product">

                <img
                    src="assets/images/gold/<?php echo htmlspecialchars($gold_product['image']); ?>"
                    alt="<?php echo htmlspecialchars($gold_product['name']); ?>"
                >

                <div class="product-info">

                    <h3>
                        <?php echo htmlspecialchars($gold_product['name']); ?>
                    </h3>

                    <span>
                        ₹<?php echo indianPrice($gold_product['price']); ?>
                    </span>

                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p class="collection-empty">
            No Gold Collection products available.
        </p>

    <?php endif; ?>

</div>     
    </div>
</section>

<!--=================================
        SILVER COLLECTION
==================================-->

<section class="collection-section" id="silver">

    <div class="container">

        <div class="section-title">

            <h2>Silver Collection</h2>

            <p>
                Explore our elegant silver jewellery collection designed for men and women with timeless style.
            </p>

        </div>

        <div class="collection-grid">

            <?php if (mysqli_num_rows($silver_collection_query) > 0): ?>

                <?php while ($silver_product = mysqli_fetch_assoc($silver_collection_query)): ?>

                    <div class="collection-product">

                        <img
                            src="assets/images/silver/<?php echo htmlspecialchars($silver_product['image']); ?>"
                            alt="<?php echo htmlspecialchars($silver_product['name']); ?>"
                        >

                        <div class="product-info">

                            <h3>
                                <?php echo htmlspecialchars($silver_product['name']); ?>
                            </h3>

                            <span>
                                ₹<?php echo indianPrice($silver_product['price']); ?>
                            </span>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p class="collection-empty">
                    No Silver Collection products available.
                </p>

            <?php endif; ?>

        </div>

    </div>

</section>


<!--=================================
      ROSE GOLD COLLECTION
==================================-->

<section class="collection-section" id="rose-gold">

    <div class="container">

        <div class="section-title">

            <h2>Rose Gold Collection</h2>

            <p>
                Explore our elegant rose gold jewellery collection designed for men and women with timeless style.
            </p>

        </div>

        <div class="collection-grid">

            <?php if (mysqli_num_rows($rose_gold_collection_query) > 0): ?>

                <?php while ($rose_product = mysqli_fetch_assoc($rose_gold_collection_query)): ?>

                    <div class="collection-product">

                        <img
                            src="assets/images/rose-gold/<?php echo htmlspecialchars($rose_product['image']); ?>"
                            alt="<?php echo htmlspecialchars($rose_product['name']); ?>"
                        >

                        <div class="product-info">

                            <h3>
                                <?php echo htmlspecialchars($rose_product['name']); ?>
                            </h3>

                            <span>
                                ₹<?php echo indianPrice($rose_product['price']); ?>
                            </span>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p class="collection-empty">
                    No Rose Gold Collection products available.
                </p>

            <?php endif; ?>

        </div>

    </div>

</section>

<!--=================================
        DIAMOND COLLECTION
==================================-->

<section class="collection-section" id="diamond">

    <div class="container">

        <div class="section-title">

            <h2>Diamond Collection</h2>

            <p>
                Discover our sparkling diamond jewellery crafted with elegance and timeless luxury for men and women.
            </p>

        </div>

        <div class="collection-grid">

            <?php if (mysqli_num_rows($diamond_collection_query) > 0): ?>

                <?php while ($diamond_product = mysqli_fetch_assoc($diamond_collection_query)): ?>

                    <div class="collection-product">

                        <img
                            src="assets/images/diamond/<?php echo htmlspecialchars($diamond_product['image']); ?>"
                            alt="<?php echo htmlspecialchars($diamond_product['name']); ?>"
                        >

                        <div class="product-info">

                            <h3>
                                <?php echo htmlspecialchars($diamond_product['name']); ?>
                            </h3>

                            <span>
                                ₹<?php echo indianPrice($diamond_product['price']); ?>
                            </span>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p class="collection-empty">
                    No Diamond Collection products available.
                </p>

            <?php endif; ?>

        </div>

    </div>

</section>

<!--=================================
        BRIDAL COLLECTION
==================================-->

<section class="collection-section" id="bridal">

    <div class="container">

        <div class="section-title">

            <h2>Bridal Collection</h2>

            <p>
                Discover luxurious bridal jewellery crafted with timeless elegance for your special day.
            </p>

        </div>

        <div class="collection-grid">

            <?php if (mysqli_num_rows($bridal_collection_query) > 0): ?>

                <?php while ($bridal_product = mysqli_fetch_assoc($bridal_collection_query)): ?>

                    <div class="collection-product">

                        <img
                            src="assets/images/bridal/<?php echo htmlspecialchars($bridal_product['image']); ?>"
                            alt="<?php echo htmlspecialchars($bridal_product['name']); ?>"
                        >

                        <div class="product-info">

                            <h3>
                                <?php echo htmlspecialchars($bridal_product['name']); ?>
                            </h3>

                            <span>
                                ₹<?php echo indianPrice($bridal_product['price']); ?>
                            </span>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p class="collection-empty">
                    No Bridal Collection products available.
                </p>

            <?php endif; ?>

        </div>

    </div>

</section>
<!--=================================
        GROOM COLLECTION
==================================-->

<section class="collection-section" id="groom">

    <div class="container">

        <div class="section-title">

            <h2>Groom Collection</h2>

            <p>
                Premium jewellery crafted for the modern groom with elegance, luxury and timeless style.
            </p>

        </div>

        <div class="collection-grid">

            <?php if ($groom_collection_query && mysqli_num_rows($groom_collection_query) > 0): ?>

                <?php while ($groom_product = mysqli_fetch_assoc($groom_collection_query)): ?>

                    <div class="collection-product">

                        <img
                            src="assets/images/groom/<?php echo htmlspecialchars($groom_product['image']); ?>"
                            alt="<?php echo htmlspecialchars($groom_product['name']); ?>"
                        >

                        <div class="product-info">

                            <h3>
                                <?php echo htmlspecialchars($groom_product['name']); ?>
                            </h3>

                            <span>
                                ₹<?php echo indianPrice($groom_product['price']); ?>
                            </span>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p class="collection-empty">
                    No Groom Collection products available.
                </p>

            <?php endif; ?>

        </div>

    </div>

</section>
<!--=================================
        KIDS COLLECTION
==================================-->

<section class="collection-section" id="kids">

    <div class="container">

        <div class="section-title">

            <h2>Kids Collection</h2>

            <p>
                Cute, colourful and comfortable jewellery specially designed for little stars with premium quality.
            </p>

        </div>


        <div class="collection-grid">

            <?php if (
                $kids_collection_query &&
                mysqli_num_rows($kids_collection_query) > 0
            ): ?>

                <?php while (
                    $kids_product =
                    mysqli_fetch_assoc($kids_collection_query)
                ): ?>

                    <div class="collection-product">

                        <img
                            src="assets/images/kids/<?php echo htmlspecialchars($kids_product['image']); ?>"
                            alt="<?php echo htmlspecialchars($kids_product['name']); ?>"
                        >

                        <div class="product-info">

                            <h3>
                                <?php echo htmlspecialchars($kids_product['name']); ?>
                            </h3>

                            <span>
                                ₹<?php echo indianPrice($kids_product['price']); ?>
                            </span>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p class="collection-empty">
                    No Kids Collection products available.
                </p>

            <?php endif; ?>

        </div>

    </div>

</section>
<!--======================================
            FOOTER
=======================================-->

<footer class="footer">

    <div class="container">

        <div class="footer-grid">

            <!-- About -->

            <div class="footer-about">

                <h2>Glamour Gems</h2>

                <p>
                    Discover timeless jewellery crafted with elegance,
                    luxury and exceptional craftsmanship. Every piece
                    is designed to celebrate your special moments.
                </p>

                <div class="social-icons">

                    <a href="#"><i class="fab fa-facebook-f"></i></a>

                    <a href="#"><i class="fab fa-instagram"></i></a>

                    <a href="#"><i class="fab fa-x-twitter"></i></a>

                    <a href="#"><i class="fab fa-pinterest-p"></i></a>

                </div>

            </div>

            <!-- Quick Links -->

            <div class="footer-links">

                <h3>Quick Links</h3>

                <ul>

                    <li><a href="index.php">Home</a></li>

                    <li><a href="shop.php">Shop</a></li>

                    <li><a href="collections.php">Collections</a></li>

                    <li><a href="about.php">About Us</a></li>

                    <li><a href="contact.php">Contact</a></li>

                </ul>

            </div>

            <!-- Collections -->

            <div class="footer-links">

                <h3>Collections</h3>

                <ul>

                     <li>
                        <a href="collections.php#gold">Gold Jewellery</a>
                    </li>

                    <li><a href="collections.php#diamond">Diamond Jewellery</a></li>

                    <li><a href="collections.php#silver">Silver Jewellery</a></li>

                    <li><a href="collections.php#bridal">Bridal Collection</a></li>

                    <li><a href="collections.php#groom">Groom Collection</a></li>

                    <li><a href="collections.php#kids">Kids Collection</a></li>

                </ul>

            </div>

            <!-- Contact -->

            <div class="footer-contact">

                <h3>Contact Us</h3>

                <p><i class="fas fa-location-dot"></i> Mumbai, India</p>

                <p><i class="fas fa-phone"></i> +91 98765 43210</p>

                <p><i class="fas fa-envelope"></i> info@glamourgems.com</p>

                <p><i class="fas fa-clock"></i> Mon - Sat : 10:00 AM - 8:00 PM</p>

            </div>

        </div>

        <div class="footer-bottom">

            <p>© 2026 Glamour Gems. All Rights Reserved.</p>

        </div>

    </div>

</footer>
<!-- Collections JS -->

<script src="assets/js/collections.js"></script>

<script>

/*======================================
        COLLECTION FILTER
======================================*/

document.addEventListener("DOMContentLoaded", function () {

    const params = new URLSearchParams(window.location.search);
    const selectedCollection = params.get("collection");

    if (!selectedCollection) {
        return;
    }

    const sections = document.querySelectorAll(".collection-section");

    sections.forEach(function (section) {

        if (section.id === selectedCollection) {

            section.style.display = "block";

        } else {

            section.style.display = "none";

        }

    });

    const selectedSection = document.getElementById(selectedCollection);

    if (selectedSection) {

        setTimeout(function () {

            selectedSection.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });

        }, 200);

    }

});

</script>

</body>

</html>