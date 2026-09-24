<?php

require_once 'config/config.php';
require_once 'config/database.php';

/*======================================
        RECIPIENT FILTER
======================================*/

$selected_category = isset($_GET['category'])
    ? strtolower(trim($_GET['category']))
    : '';

/*======================================
        COLLECTION FILTER
======================================*/

$selected_collection = isset($_GET['collection'])
    ? strtolower(trim($_GET['collection']))
    : '';

/*======================================
        PRODUCT QUERY
======================================*/

$query = "SELECT * FROM products WHERE status = 'active'";

/*======================================
        CATEGORY FILTER
======================================*/

if ($selected_category !== '') {

    $selected_category = mysqli_real_escape_string(
        $conn,
        $selected_category
    );

    $query .= " AND category_id IN (
        SELECT id
        FROM categories
        WHERE LOWER(slug) = '$selected_category'
        AND status = 'active'
    )";
}

/*======================================
        COLLECTION FILTER
======================================*/

if ($selected_collection !== '') {

    $selected_collection = mysqli_real_escape_string(
        $conn,
        $selected_collection
    );

    /* Gold Jewellery */

    if ($selected_collection === 'gold') {

        $query .= " AND LOWER(metal) = 'gold'";
    }

    /* Diamond Jewellery */

    elseif ($selected_collection === 'diamond') {

        $query .= " AND LOWER(metal) = 'diamond'";
    }

    /* Silver Jewellery */

    elseif ($selected_collection === 'silver') {

        $query .= " AND LOWER(metal) = 'silver'";
    }

    /* Bridal Collection */

    elseif ($selected_collection === 'bridal') {

        $query .= " AND LOWER(jewellery_type) = 'bridal'";
    }

    /* Men's Collection */

    elseif ($selected_collection === 'men') {

        $query .= " AND category_id IN (
            SELECT id
            FROM categories
            WHERE LOWER(slug) = 'men'
            AND status = 'active'
        )";
    }
}

/*======================================
        ORDER PRODUCTS
======================================*/

$query .= " ORDER BY id ASC";

$result = mysqli_query($conn, $query);

$products = [];

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {

        $products[] = $row;
    }
}

/*======================================
        CATEGORY DATA
======================================*/

$categories = [];

$categoryResult = mysqli_query(
    $conn,
    "SELECT id, name FROM categories"
);

if ($categoryResult) {

    while ($row = mysqli_fetch_assoc($categoryResult)) {

        $categories[$row['id']] = $row['name'];
    }
}

/*======================================
        INDIAN PRICE FORMAT
======================================*/

function formatIndianPrice($price)
{
    $price = (int)$price;

    $lastThree = substr($price, -3);

    $rest = substr($price, 0, -3);

    if ($rest != '') {

        $rest = preg_replace(
            '/\B(?=(\d{2})+(?!\d))/',
            ',',
            $rest
        );

        return $rest . ',' . $lastThree;
    }

    return $lastThree;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Glamour Gems | Shop</title>

<!--======================================
            GOOGLE FONTS
=======================================-->

<link
    href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap"
    rel="stylesheet"
>

<!--======================================
            FONT AWESOME
=======================================-->

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
>

<!--======================================
            SWIPER CSS
=======================================-->

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
>

<!--======================================
            MAIN CSS
=======================================-->

<link
    rel="stylesheet"
    href="assets/css/style.css"
>

<!--======================================
            SHOP CSS
=======================================-->

<link
    rel="stylesheet"
    href="assets/css/shop.css"
>

</head>

<body>

<!--======================================
                TOP BAR
=======================================-->

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
=======================================-->

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
            <a href="index.php">
                Home
            </a>
        </li>

        <li>
            <a href="shop.php">
                Shop
            </a>
        </li>

        <li>
            <a href="collections.php">
                Collections
            </a>
        </li>

        <li>
            <a href="about.php">
                About
            </a>
        </li>

        <li>
            <a href="contact.php">
                Contact
            </a>
        </li>

    </ul>


    <div class="nav-icons">

        <a href="#" class="search-toggle">

            <i class="fa-solid fa-magnifying-glass"></i>

        </a>


        <a href="#" class="wishlist-nav">

            <i class="fa-regular fa-heart"></i>

            <span id="wishlistCount">
                0
            </span>

        </a>


        <a href="cart.php">

            <i class="fa-solid fa-bag-shopping"></i>

            <span id="cartCount">
                0
            </span>

        </a>


        <a href="login.php">

            <i class="fa-regular fa-user"></i>

        </a>

    </div>

</nav>

</header>


<!--======================================
                SHOP HERO
=======================================-->

<section class="shop-hero">

    <div class="shop-hero-overlay">

        <h1>
            Luxury Jewellery Shop
        </h1>

        <p>
            Explore our premium collection of Gold, Diamond,
            Rose Gold and Silver Jewellery crafted with
            timeless elegance and exceptional quality.
        </p>

    </div>

</section>


<!--======================================
                BREADCRUMB
=======================================-->

<section class="breadcrumb">

    <div class="container">

        <a href="index.php">
            Home
        </a>

        <span>
            /
        </span>

        <span>
            Shop
        </span>

    </div>

</section>


<!--======================================
                SHOP SECTION
=======================================-->

<section class="shop-section">

<div class="container">


<!--======================================
                SHOP HEADER
=======================================-->

<div class="shop-header">

    <div class="search-box">

        <input
            type="text"
            id="searchInput"
            placeholder="Search Jewellery..."
        >

        <i class="fas fa-search"></i>

    </div>


    <div class="sort-box">

        <select id="sort">

            <option value="default">
                Sort By
            </option>

            <option value="low">
                Price : Low to High
            </option>

            <option value="high">
                Price : High to Low
            </option>

            <option value="name">
                Name A-Z
            </option>

        </select>

    </div>

</div>


<div class="shop-wrapper">


<!--======================================
            FILTER SIDEBAR
=======================================-->

<aside class="filter-sidebar">

    <h2 class="filter-heading">
        Filter Products
    </h2>


    <!--================ PRICE ================-->

    <div class="filter-box">

        <div class="filter-title">

            <h3>
                Price Range
            </h3>

            <span class="toggle-icon">
                +
            </span>

        </div>


        <div class="filter-content">

            <label>

                <input
                    type="radio"
                    class="price-filter"
                    name="price"
                    value="10000-25000"
                >

                ₹10,000 - ₹25,000

            </label>


            <label>

                <input
                    type="radio"
                    class="price-filter"
                    name="price"
                    value="25000-50000"
                >

                ₹25,000 - ₹50,000

            </label>


            <label>

                <input
                    type="radio"
                    class="price-filter"
                    name="price"
                    value="50000-100000"
                >

                ₹50,000 - ₹1,00,000

            </label>


            <label>

                <input
                    type="radio"
                    class="price-filter"
                    name="price"
                    value="100000+"
                >

                Above ₹1,00,000

            </label>

        </div>

    </div>


    <!--================ CATEGORY ================-->

    <div class="filter-box">

        <div class="filter-title">

            <h3>
                Category
            </h3>

            <span class="toggle-icon">
                +
            </span>

        </div>


        <div class="filter-content">

            <label>

                <input
                    type="checkbox"
                    class="category-filter"
                    value="women"
                >

                Women

            </label>


            <label>

                <input
                    type="checkbox"
                    class="category-filter"
                    value="men"
                >

                Men

            </label>


            <label>

                <input
                    type="checkbox"
                    class="category-filter"
                    value="kids"
                >

                Kids

            </label>


            <label>

                <input
                    type="checkbox"
                    class="category-filter"
                    value="bridal"
                >

                Bridal

            </label>


            <label>

                <input
                    type="checkbox"
                    class="category-filter"
                    value="groom"
                >

                Groom

            </label>

        </div>

    </div>


    <!--================ METAL ================-->

    <div class="filter-box">

        <div class="filter-title">

            <h3>
                Metal
            </h3>

            <span class="toggle-icon">
                +
            </span>

        </div>


        <div class="filter-content">

            <label>

                <input
                    type="checkbox"
                    class="metal-filter"
                    value="gold"
                >

                Gold

            </label>


            <label>

                <input
                    type="checkbox"
                    class="metal-filter"
                    value="diamond"
                >

                Diamond

            </label>


            <label>

                <input
                    type="checkbox"
                    class="metal-filter"
                    value="silver"
                >

                Silver

            </label>


            <label>

                <input
                    type="checkbox"
                    class="metal-filter"
                    value="rose gold"
                >

                Rose Gold

            </label>

        </div>

    </div>


    <!--================ JEWELLERY TYPE ================-->

    <div class="filter-box">

        <div class="filter-title">

            <h3>
                Jewellery Type
            </h3>

            <span class="toggle-icon">
                +
            </span>

        </div>


        <div class="filter-content">

            <label>
                <input
                    type="checkbox"
                    class="type-filter"
                    value="bangles"
                >
                Bangles
            </label>


            <label>
                <input
                    type="checkbox"
                    class="type-filter"
                    value="necklace"
                >
                Necklaces
            </label>


            <label>
                <input
                    type="checkbox"
                    class="type-filter"
                    value="groom"
                >
                Groom
            </label>


            <label>
                <input
                    type="checkbox"
                    class="type-filter"
                    value="bridal"
                >
                Bridal
            </label>


            <label>
                <input
                    type="checkbox"
                    class="type-filter"
                    value="earring"
                >
                Earrings
            </label>


            <label>
                <input
                    type="checkbox"
                    class="type-filter"
                    value="brouch"
                >
                Brouch
            </label>


            <label>
                <input
                    type="checkbox"
                    class="type-filter"
                    value="bracelet"
                >
                Bracelet
            </label>


            <label>
                <input
                    type="checkbox"
                    class="type-filter"
                    value="pendant"
                >
                Pendant
            </label>


            <label>
                <input
                    type="checkbox"
                    class="type-filter"
                    value="ring"
                >
                Ring
            </label>

        </div>

    </div>


    <!--================ FILTER BUTTONS ================-->

    <div class="filter-buttons">

        <button
            type="button"
            class="apply-filter"
        >
            Apply Filters
        </button>


        <button
            type="button"
            class="clear-filter"
        >
            Clear Filters
        </button>

    </div>

</aside>


<!--======================================
            PRODUCTS AREA
=======================================-->

<div class="products-area">


    <div class="products-header">

        <h2>
            Featured Jewellery
        </h2>

    </div>


    <div class="products-grid">


        <div
            class="no-products-message"
            style="display: none;"
        >

            <h3>
                No Products Found
            </h3>

            <p>
                Try changing your filters to find more products.
            </p>

        </div>


        <?php foreach ($products as $product): ?>


        <!--======================================
                PRODUCT CARD
        =======================================-->

        <div
            class="product-card"
            data-name="<?php echo htmlspecialchars($product['name']); ?>"
            data-price="<?php echo htmlspecialchars($product['sale_price']); ?>"
            data-category="<?php echo htmlspecialchars($product['jewellery_type']); ?>"
            data-metal="<?php echo htmlspecialchars($product['metal']); ?>"
            data-gender="<?php echo htmlspecialchars($categories[$product['category_id']] ?? ''); ?>"
        >


            <span class="product-badge">
                NEW
            </span>


            <!--================ WISHLIST ================-->

            <form
                action="add-to-wishlist.php"
                method="POST"
                class="wishlist-form"
            >

                <input
                    type="hidden"
                    name="product_id"
                    value="<?php echo (int)$product['id']; ?>"
                >


                <input
                    type="hidden"
                    name="redirect"
                    value="shop"
                >


                <button
                    type="submit"
                    class="wishlist-btn"
                    title="Add to Wishlist"
                >

                    <i class="fa-regular fa-heart"></i>

                </button>

            </form>


            <!--================ PRODUCT IMAGE ================-->

            <div class="product-image">

                <img
                    src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                >


                <div class="image-overlay">

                    <button class="quick-view-btn">
                        Quick View
                    </button>

                </div>

            </div>


            <!--================ PRODUCT INFO ================-->

            <div class="product-info">


                <span class="product-category">

                    <?php
                    echo htmlspecialchars(
                        $product['jewellery_type']
                    );
                    ?>

                </span>


                <h3>

                    <?php
                    echo htmlspecialchars(
                        $product['name']
                    );
                    ?>

                </h3>


                <div class="product-rating">

                    ★★★★★

                </div>


                <div class="product-price">

                    <span class="new-price">

                        ₹<?php
                        echo formatIndianPrice(
                            $product['sale_price']
                        );
                        ?>

                    </span>


                    <span class="old-price">

                        ₹<?php
                        echo formatIndianPrice(
                            $product['price']
                        );
                        ?>

                    </span>

                </div>


                <button
                    class="cart-btn"
                    data-product-id="<?php echo (int)$product['id']; ?>"
                >

                    Add To Cart

                </button>


            </div>

        </div>


        <?php endforeach; ?>


    </div>

</div>

</div>

</div>

</section>


<!--======================================
            SPECIAL OFFER BANNER
=======================================-->

<section class="offer-banner">

    <div class="offer-overlay">

        <div class="offer-content">

            <span class="offer-tag">
                ✨ Limited Time Offer
            </span>


            <h2>

                Luxury Jewellery

                <span>
                    Up To 50% Off
                </span>

            </h2>


            <h3>
                Celebrate Every Moment with Elegance
            </h3>


            <p>

                Discover our exclusive collection of handcrafted diamond,
                gold, silver, and bridal jewellery. Shop today and enjoy
                premium quality, certified designs, and exciting seasonal
                discounts for a limited time.

            </p>

        </div>

    </div>

</section>


<!--======================================
                FOOTER
=======================================-->

<footer class="footer">

<div class="container">

<div class="footer-grid">


    <!--================ ABOUT ================-->

    <div class="footer-about">

        <h2>
            Glamour Gems
        </h2>


        <p>

            Discover timeless jewellery crafted with elegance,
            luxury and exceptional craftsmanship. Every piece
            is designed to celebrate your special moments.

        </p>


        <div class="social-icons">

            <a href="#">
                <i class="fab fa-facebook-f"></i>
            </a>

            <a href="#">
                <i class="fab fa-instagram"></i>
            </a>

            <a href="#">
                <i class="fab fa-x-twitter"></i>
            </a>

            <a href="#">
                <i class="fab fa-pinterest-p"></i>
            </a>

        </div>

    </div>


    <!--================ QUICK LINKS ================-->

    <div class="footer-links">

        <h3>
            Quick Links
        </h3>


        <ul>

            <li>
                <a href="index.php">
                    Home
                </a>
            </li>


            <li>
                <a href="shop.php">
                    Shop
                </a>
            </li>


            <li>
                <a href="collections.php">
                    Collections
                </a>
            </li>


            <li>
                <a href="about.php">
                    About Us
                </a>
            </li>


            <li>
                <a href="contact.php">
                    Contact
                </a>
            </li>

        </ul>

    </div>


    <!--================ COLLECTIONS ================-->

    <div class="footer-links">

        <h3>
            Collections
        </h3>


        <ul>

            <li>
                <a href="collections.php?collection=gold">
                    Gold Jewellery
                </a>
            </li>

            <li>
                <a href="collections.php?collection=diamond">
                    Diamond Jewellery
                </a>
            </li>

            <li>
                <a href="collections.php?collection=silver">
                    Silver Jewellery
                </a>
            </li>

            <li>
                <a href="collections.php?collection=bridal">
                    Bridal Collection
                </a>
            </li>

            <li>
                <a href="collections.php?collection=groom">
                    Groom Collection
                </a>
            </li>

            <li>
                <a href="collections.php?collection=kids">
                    Kids Collection
                </a>
            </li>


        </ul>

    </div>


    <!--================ CONTACT ================-->

    <div class="footer-contact">

        <h3>
            Contact Us
        </h3>


        <p>

            <i class="fas fa-location-dot"></i>

            Mumbai, India

        </p>


        <p>

            <i class="fas fa-phone"></i>

            +91 98765 43210

        </p>


        <p>

            <i class="fas fa-envelope"></i>

            info@glamourgems.com

        </p>


        <p>

            <i class="fas fa-clock"></i>

            Mon - Sat : 10:00 AM - 8:00 PM

        </p>

    </div>


</div>


<div class="footer-bottom">

    <p>
        © 2026 Glamour Gems. All Rights Reserved.
    </p>

</div>

</div>

</footer>


<!--======================================
            QUICK VIEW MODAL
=======================================-->

<div class="quick-view-modal">


    <div class="quick-view-container">


        <button class="close-modal">

            <i class="fas fa-times"></i>

        </button>


        <div class="quick-view-image">

            <img
                id="quickImage"
                src=""
                alt="Product"
            >

        </div>


        <div class="quick-view-details">


            <span
                class="quick-category"
                id="quickCategory"
            ></span>


            <h2 id="quickTitle"></h2>


            <div class="quick-rating">
                ★★★★★
            </div>


            <div class="quick-price">

                <span
                    class="new-price"
                    id="quickPrice"
                ></span>

            </div>


            <p class="quick-description">

                Premium handcrafted jewellery made
                with certified materials and elegant
                finishing for every special occasion.

            </p>


            <button class="modal-cart-btn">

                Add To Cart

            </button>


        </div>

    </div>

</div>


<!--======================================
            SHOP JAVASCRIPT
=======================================-->

<script src="assets/js/shop.js"></script>

</body>

</html>