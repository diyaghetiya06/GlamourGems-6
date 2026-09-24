<?php
session_start();

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id']) && !isset($_GET['home'])) {
    header('Location: welcome.php');
    exit;
}

require_once __DIR__ . "/config/database.php";

/*======================================
        NEWSLETTER SUBSCRIPTION
======================================*/

$newsletter_message = '';
$newsletter_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_subscribe'])) {

    $newsletter_email = trim($_POST['newsletter_email'] ?? '');

    if ($newsletter_email === '') {

        $newsletter_message = 'Please enter your email address.';
        $newsletter_type = 'error';

    } elseif (!filter_var($newsletter_email, FILTER_VALIDATE_EMAIL)) {

        $newsletter_message = 'Please enter a valid email address.';
        $newsletter_type = 'error';

    } else {

        $check_stmt = mysqli_prepare(
            $conn,
            "SELECT id, status
             FROM newsletter_subscribers
             WHERE email = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $check_stmt,
            "s",
            $newsletter_email
        );

        mysqli_stmt_execute($check_stmt);

        $check_result = mysqli_stmt_get_result($check_stmt);
        $existing_subscriber = mysqli_fetch_assoc($check_result);

        mysqli_stmt_close($check_stmt);


        if ($existing_subscriber) {

            if ($existing_subscriber['status'] === 'inactive') {

                $update_stmt = mysqli_prepare(
                    $conn,
                    "UPDATE newsletter_subscribers
                     SET status = 'active'
                     WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $update_stmt,
                    "i",
                    $existing_subscriber['id']
                );

                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);

                $newsletter_message =
                    'Welcome back! Your newsletter subscription is active again.';

                $newsletter_type = 'success';

            } else {

                $newsletter_message =
                    'This email is already subscribed to our newsletter.';

                $newsletter_type = 'error';

            }

        } else {

            $insert_stmt = mysqli_prepare(
                $conn,
                "INSERT INTO newsletter_subscribers
                (email, status)
                VALUES (?, 'active')"
            );

            mysqli_stmt_bind_param(
                $insert_stmt,
                "s",
                $newsletter_email
            );

            if (mysqli_stmt_execute($insert_stmt)) {

                $newsletter_message =
                    'Thank you! You have successfully subscribed to Glamour Gems.';

                $newsletter_type = 'success';

            } else {

                $newsletter_message =
                    'Unable to subscribe right now. Please try again.';

                $newsletter_type = 'error';

            }

            mysqli_stmt_close($insert_stmt);
        }
    }
}

/*======================================
        INDIAN PRICE FORMAT
======================================*/

function formatIndianPrice($price)
{
    $price = number_format((float)$price, 0, '.', '');

    $lastThree = substr($price, -3);
    $remaining = substr($price, 0, -3);

    if ($remaining != '') {
        $remaining = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remaining);
        return $remaining . ',' . $lastThree;
    }

    return $lastThree;
}

/*======================================
        FETCH ACTIVE RECIPIENTS
======================================*/

$recipient_query = mysqli_query(
    $conn,
    "SELECT * FROM categories
     WHERE status = 'active'
     ORDER BY id ASC"
);

/*======================================
        FETCH ACTIVE MATERIALS
======================================*/

$material_query = mysqli_query(
    $conn,
    "SELECT * FROM materials
     WHERE status = 'active'
     ORDER BY id ASC"
);

/*======================================
        FETCH ACTIVE BANNERS
======================================*/

$banner_query = mysqli_query(
    $conn,
    "SELECT * FROM banners
     WHERE status = 1
     ORDER BY display_order ASC, id ASC"
);

/*======================================
        FETCH CURATED LOOKS
======================================*/

$curated_query = mysqli_query(
    $conn,
    "SELECT *
     FROM curated_looks
     WHERE status = 'active'
     ORDER BY display_order ASC, id ASC"
);

/*======================================
        FETCH NEW ARRIVALS
======================================*/

$new_arrivals_query = mysqli_query(
    $conn,
    "SELECT *
     FROM new_arrivals
     WHERE status = 'active'
     ORDER BY display_order ASC, id ASC"
);

/*======================================
        FETCH CUSTOMER REVIEWS
======================================*/

$review_query = mysqli_query(
    $conn,
    "SELECT *
     FROM customer_reviews
     WHERE status = 'active'
     ORDER BY id DESC"
);

/*======================================
        FETCH INSTAGRAM REELS
======================================*/

$instagram_reels_query = mysqli_query(
    $conn,
    "SELECT *
     FROM instagram_reels
     WHERE status = 'active'
     ORDER BY display_order ASC, id ASC"
);

?>

<?php

require_once "config/database.php";

$journey_query = "SELECT * FROM journey_numbers 
                  WHERE status = 'active' 
                  ORDER BY display_order ASC";

$journey_result = mysqli_query($conn, $journey_query);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Glamour Gems</title>

<!-- Google Fonts -->

<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Font Awesome -->

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<!-- Swiper CSS -->

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

<!-- Main CSS -->

<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<!--==========================
        TOP BAR
===========================-->

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

<!--==========================
          NAVBAR
===========================-->

<header>

<nav class="navbar">

<a href="index.php" class="logo">
    <img src="assets/images/logo/logo.png" alt="Glamour Gems Logo">
    <span>GLAMOUR GEMS</span>
</a>

<ul class="nav-menu">

    <li><a href="index.php">Home</a></li>

    <li><a href="shop.php">Shop</a></li>

    <li><a href="collections.php">Collections</a></li>

    <li><a href="about.php">About</a></li>

    <li><a href="contact.php">Contact</a></li>


</ul>
<div class="nav-icons">

    <!-- Search -->
        <a href="#new-arrivals-search" id="homeSearchBtn">
         <i class="fa-solid fa-magnifying-glass"></i>
    </a>

    <!-- Wishlist -->
    <a href="#" class="wishlist-nav">
        <i class="fa-regular fa-heart"></i>
        <span id="wishlistCount">0</span>
    </a>

    <!-- Cart -->
    <a href="cart.php">
        <i class="fa-solid fa-bag-shopping"></i>
        <span id="cartCount">0</span>
    </a>
     
     <!-- Customer Account -->

<?php if (isset($_SESSION['user_id'])): ?>

    <a href="profile.php" title="My Profile">
        <i class="fa-regular fa-user"></i>
    </a>

    <a href="switch-to-admin.php" title="Switch to Admin Panel">
        <i class="fa-solid fa-shield-halved"></i>
    </a>

    <a href="logout.php" title="Logout">
        <i class="fa-solid fa-right-from-bracket"></i>
    </a>

<?php else: ?>

    <a href="login.php" title="Customer Login">
        <i class="fa-regular fa-user"></i>
    </a>

<?php endif; ?>

</div>   



</nav>

</header>

<!--=====================================
            HERO SECTION
======================================-->

<section class="hero">

    <div class="swiper heroSlider">

        <div class="swiper-wrapper">

            <?php if ($banner_query && mysqli_num_rows($banner_query) > 0): ?>

                <?php while ($banner = mysqli_fetch_assoc($banner_query)): ?>

                    <div class="swiper-slide hero-slide">

                        <img
                            src="uploads/banners/<?php echo htmlspecialchars($banner['image']); ?>"
                            alt="<?php echo htmlspecialchars($banner['title']); ?>"
                        >

                        <div class="hero-overlay"></div>

                        <div class="hero-content">

                            <?php if (!empty($banner['title'])): ?>

                                <span>
                                    <?php echo htmlspecialchars($banner['title']); ?>
                                </span>

                            <?php endif; ?>


                            <?php if (!empty($banner['heading'])): ?>

                                <h1>
                                    <?php echo nl2br(
                                        htmlspecialchars($banner['heading'])
                                    ); ?>
                                </h1>

                            <?php endif; ?>


                            <?php if (!empty($banner['description'])): ?>

                                <p>
                                    <?php echo htmlspecialchars(
                                        $banner['description']
                                    ); ?>
                                </p>

                            <?php endif; ?>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <!-- No Active Banner -->

                <div class="swiper-slide hero-slide">

                    <img
                        src="assets/images/banners/slide1.jpg"
                        alt="Glamour Gems"
                    >

                    <div class="hero-overlay"></div>

                    <div class="hero-content">

                        <span>Glamour Gems</span>

                        <h1>
                            Timeless Elegance,<br>
                            Crafted for You
                        </h1>

                        <p>
                            Discover handcrafted jewellery designed
                            for every special moment.
                        </p>

                    </div>

                </div>

            <?php endif; ?>

        </div>
        <!-- END swiper-wrapper -->


        <!--======================================
                HERO PREVIOUS BUTTON
        ======================================-->

        <div class="swiper-button-prev hero-prev"></div>


        <!--======================================
                HERO NEXT BUTTON
        ======================================-->

        <div class="swiper-button-next hero-next"></div>


        <!--======================================
                HERO PAGINATION
        ======================================-->

        <div class="swiper-pagination hero-pagination"></div>


    </div>
    <!-- END heroSlider -->

</section>

<!--======================================
        SHOP BY RECIPIENT
======================================-->

<section class="recipient">

    <div class="container">

        <div class="section-heading">

            <span>OUR COLLECTION</span>

            <h2>Shop By Recipient</h2>

            <p>
                Find the perfect jewellery for every special person in your life.
            </p>

        </div>

        <div class="recipient-grid">

            <?php if (mysqli_num_rows($recipient_query) > 0): ?>

                <?php while ($recipient = mysqli_fetch_assoc($recipient_query)): ?>

                    <a href="shop.php?category=<?php echo urlencode($recipient['slug']); ?>"
                       class="recipient-card">

                        <img src="assets/images/recipient/<?php echo htmlspecialchars($recipient['image']); ?>"
                             alt="<?php echo htmlspecialchars($recipient['name']); ?>">

                        <div class="recipient-overlay">

                            <h3>
                                <?php echo htmlspecialchars($recipient['name']); ?>
                            </h3>

                        </div>

                    </a>

                <?php endwhile; ?>

            <?php endif; ?>

        </div>

    </div>

</section>

<!--=====================================
        SHOP BY MATERIAL
======================================-->

<section class="material">

    <div class="container">

        <div class="section-heading">

            <span>PREMIUM MATERIALS</span>

            <h2>Shop By Material</h2>

            <p>
                Discover timeless jewellery crafted in the finest materials for every occasion.
            </p>

        </div>

        <div class="material-grid">

            <?php if (mysqli_num_rows($material_query) > 0): ?>

                <?php while ($material = mysqli_fetch_assoc($material_query)): ?>

                    <a href="collections.php#<?php echo htmlspecialchars($material['slug']); ?>"
                       class="material-card">

                        <img
                            src="assets/images/materials/<?php echo htmlspecialchars($material['image']); ?>"
                            alt="<?php echo htmlspecialchars($material['name']); ?>"
                        >

                        <div class="material-overlay">

                            <h3>
                                <?php echo htmlspecialchars($material['name']); ?>
                            </h3>

                            <span>Explore Collection</span>

                        </div>

                    </a>

                <?php endwhile; ?>

            <?php endif; ?>

        </div>

    </div>

</section>

<!--=====================================
        FEATURED COLLECTION
======================================-->

<section class="featured">

    <div class="container">

        <div class="section-heading">

            <span>FEATURED COLLECTION</span>

            <h2>Curated Looks</h2>

            <p>
                Trendsetting diamond jewellery suited for every occasion.
            </p>

        </div>

          <div class="swiper featuredSlider">

    <div class="swiper-wrapper">

        <?php if ($curated_query && mysqli_num_rows($curated_query) > 0): ?>

            <?php while ($look = mysqli_fetch_assoc($curated_query)): ?>

                <div class="swiper-slide">

                    <div class="feature-card">

                        <img
                            src="assets/images/products/<?php echo htmlspecialchars($look['image']); ?>"
                            alt="<?php echo htmlspecialchars($look['title']); ?>"
                            loading="lazy"
                        >

                        <div class="feature-info">

                            <h3>
                                <?php echo htmlspecialchars($look['title']); ?>
                            </h3>

                            <span>
                                ₹<?php echo number_format($look['price'], 0); ?>
                            </span>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="swiper-slide">

                <div class="feature-card">

                    <div class="feature-info">

                        <h3>No Curated Looks Available</h3>

                    </div>

                </div>

            </div>

        <?php endif; ?>

    </div>

    <!--======================================
            CURATED LOOKS NAVIGATION
    ======================================-->

    <div class="swiper-button-prev feature-prev"></div>

    <div class="swiper-button-next feature-next"></div>

    <!--======================================
            CURATED LOOKS PAGINATION
    ======================================-->

    <div class="swiper-pagination feature-pagination"></div>

</div>

</section>

<!--=====================================
          NEW ARRIVALS
======================================-->
<section class="new-arrivals" id="new-arrivals-search">

    <div class="container">

        <div class="section-heading">

            <span>JUST ARRIVED</span>

            <h2>New Arrivals</h2>

            <p>
                Explore our newest jewellery collection crafted with elegance and timeless beauty.
            </p>

        </div>
             
            <div class="arrival-grid">

    <?php if ($new_arrivals_query && mysqli_num_rows($new_arrivals_query) > 0): ?>

        <?php while ($arrival = mysqli_fetch_assoc($new_arrivals_query)): ?>

            <div class="arrival-card">

                <span class="arrival-badge">NEW</span>
                 
             <a
                href="#"
                class="arrival-heart"
                data-new-arrival-id="<?php echo (int)$arrival['id']; ?>"
            >
                <i class="fa-regular fa-heart"></i>
            </a>

                <div class="arrival-image">
                    <img
                        src="assets/images/arrivals/<?php echo htmlspecialchars($arrival['image']); ?>"
                        alt="<?php echo htmlspecialchars($arrival['title']); ?>"
                    >
                </div>

                <div class="arrival-content">

                    <div class="arrival-rating">★★★★★</div>

                    <h3>
                        <?php echo htmlspecialchars($arrival['title']); ?>
                    </h3>

                    <div class="arrival-price">

                        <span class="price-new">
                            ₹<?php echo formatIndianPrice($arrival['price']); ?>
                        </span>

                        <span class="price-old">
                            ₹<?php echo formatIndianPrice($arrival['old_price']); ?>
                        </span>

                    </div>

                        <form
                            action="add-new-arrival-cart.php"
                            method="POST"
                            class="arrival-cart-form"
                        >

                            <input
                                type="hidden"
                                name="new_arrival_id"
                                value="<?php echo (int)$arrival['id']; ?>"
                            >

                            <button
                                type="submit"
                                class="arrival-btn"
                            >
                                Add to Cart
                            </button>

                        </form>
                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

         <div
            class="arrival-card"
            data-new-arrival-id="<?php echo (int)$arrival['id']; ?>"
        >

            <div class="arrival-content">
                <h3>No New Arrivals Available</h3>
            </div>

        </div>

    <?php endif; ?>

</div>

    </div>

</section>

<!--=====================================
        WHY CHOOSE GLAMOUR GEMS
======================================-->

<section class="why-choose">

    <div class="container">

        <div class="section-heading">

            <span>WHY CHOOSE US</span>

            <h2>Why Choose Glamour Gems</h2>

            <p>
                Discover timeless jewellery crafted with exceptional quality,
                elegance and trust for every special moment.
            </p>

        </div>

        <div class="why-grid">

            <!-- Card 1 -->

            <div class="why-card">

                <div class="why-icon">

                    <i class="fa-solid fa-certificate"></i>

                </div>

                <h3>Certified Jewellery</h3>

                <p>
                    Every jewellery piece is BIS Hallmarked and certified for complete authenticity.
                </p>

            </div>

            <!-- Card 2 -->

            <div class="why-card">

                <div class="why-icon">

                    <i class="fa-solid fa-truck-fast"></i>

                </div>

                <h3>Free Shipping</h3>

                <p>
                    Enjoy free, insured and fast delivery anywhere across India.
                </p>

            </div>

            <!-- Card 3 -->

            <div class="why-card">

                <div class="why-icon">

                    <i class="fa-solid fa-shield-halved"></i>

                </div>

                <h3>Secure Payment</h3>

                <p>
                    Safe and encrypted payment methods for worry-free shopping.
                </p>

            </div>

            <!-- Card 4 -->

            <div class="why-card">

                <div class="why-icon">

                    <i class="fa-solid fa-gift"></i>

                </div>

                <h3>Luxury Packaging</h3>

                <p>
                    Every order arrives in premium gift-ready luxury packaging.
                </p>

            </div>

        </div>

    </div>

</section>


<!--======================================
        OUR JOURNEY IN NUMBERS
======================================-->

<section class="experience">

    <div class="container">

        <div class="section-heading">

            <span>OUR EXPERIENCE</span>

            <h2>Our Journey in Numbers</h2>

            <p>
                For over two decades, Glamour Gems has been creating timeless jewellery and unforgettable moments for thousands of happy customers.
            </p>

        </div>


        <div class="experience-grid">

            <?php if ($journey_result && mysqli_num_rows($journey_result) > 0): ?>

                <?php while ($journey = mysqli_fetch_assoc($journey_result)): ?>

                    <div class="experience-card">

                        <div class="experience-icon">

                            <i class="<?php echo htmlspecialchars($journey['icon']); ?>"></i>

                        </div>


                        <h3 class="counter"
                            data-target="<?php echo htmlspecialchars($journey['number_value']); ?>">

                            0

                        </h3>


                        <p>
                            <?php echo htmlspecialchars($journey['title']); ?>
                        </p>

                    </div>

                <?php endwhile; ?>

            <?php endif; ?>

        </div>

    </div>

</section>

<!--=====================================
        CUSTOMER REVIEWS
======================================-->

<section class="reviews">

    <div class="container">

        <div class="section-heading">

            <span>TESTIMONIALS</span>

            <h2>What Our Customers Say</h2>

            <p>
                Thousands of happy customers trust Glamour Gems for quality,
                elegance and exceptional craftsmanship.
            </p>

        </div>


        <div class="swiper reviewSlider">

            <div class="swiper-wrapper">

                <?php if ($review_query && mysqli_num_rows($review_query) > 0): ?>

                    <?php while ($review = mysqli_fetch_assoc($review_query)): ?>

                        <div class="swiper-slide">

                            <div class="review-card">

                                <?php if (!empty($review['image'])): ?>

    <?php
    $review_image = $review['image'];

    $upload_image = __DIR__ . "/uploads/reviews/" . $review_image;
    $old_image = __DIR__ . "/assets/images/reviews/" . $review_image;

    if (file_exists($upload_image)) {
        $review_image_path = "uploads/reviews/" . $review_image;
    } elseif (file_exists($old_image)) {
        $review_image_path = "assets/images/reviews/" . $review_image;
    } else {
        $review_image_path = "";
    }
    ?>

                <?php if (!empty($review_image_path)): ?>

                    <img
                        src="<?php echo htmlspecialchars($review_image_path); ?>"
                        alt="<?php echo htmlspecialchars($review['customer_name']); ?>"
                    >

                <?php else: ?>

                    <div class="review-default-image">
                        <i class="fa-solid fa-user"></i>
                    </div>

                <?php endif; ?>

            <?php else: ?>

                <div class="review-default-image">
                    <i class="fa-solid fa-user"></i>
                </div>

            <?php endif; ?>

                             


                                <h3>
                                    <?php echo htmlspecialchars($review['customer_name']); ?>
                                </h3>


                                <?php if (!empty($review['location'])): ?>

                                    <span>
                                        <?php echo htmlspecialchars($review['location']); ?>
                                    </span>

                                <?php endif; ?>


                                <div class="stars">

                                    <?php
                                    $rating = (int)$review['rating'];

                                    for ($i = 1; $i <= 5; $i++) {
                                        echo ($i <= $rating) ? '★' : '☆';
                                    }
                                    ?>

                                </div>


                                <p>
                                    <?php echo htmlspecialchars($review['review']); ?>
                                </p>

                            </div>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="swiper-slide">

                        <div class="review-card">

                            <div class="review-default-image">
                                <i class="fa-solid fa-user"></i>
                            </div>

                            <h3>No Reviews Yet</h3>

                            <p>
                                Be the first customer to share your experience.
                            </p>

                        </div>

                    </div>

                <?php endif; ?>

            </div>


            <!--======================================
                    REVIEW NAVIGATION
            ======================================-->

            <div class="swiper-button-prev review-prev"></div>

            <div class="swiper-button-next review-next"></div>

            <div class="swiper-pagination review-pagination"></div>

        </div>

    </div>

</section>

<!--=====================================
        INSTAGRAM REELS
======================================-->

<section class="instagram-reels">

    <div class="container">

        <div class="section-heading">

            <span>FOLLOW US</span>

            <h2>Instagram Reels</h2>

            <p>
                Watch our latest jewellery styling videos and behind-the-scenes moments.
            </p>

        </div>


        <div class="reels-grid">

            <?php if ($instagram_reels_query && mysqli_num_rows($instagram_reels_query) > 0): ?>

                <?php while ($reel = mysqli_fetch_assoc($instagram_reels_query)): ?>

                    <div class="reel-card">

                        <?php if (!empty($reel['video_file'])): ?>

                            <video
                                muted
                                loop
                                autoplay
                                playsinline
                                preload="metadata"
                            >

                                <source
                                    src="assets/videos/<?php echo htmlspecialchars($reel['video_file']); ?>"
                                    type="video/mp4"
                                >

                            </video>

                        <?php elseif (!empty($reel['thumbnail'])): ?>

                            <img
                                src="uploads/reels/<?php echo htmlspecialchars($reel['thumbnail']); ?>"
                                alt="<?php echo htmlspecialchars($reel['title']); ?>"
                            >

                        <?php endif; ?>


                        <div class="video-overlay">

                            <div class="play-btn">

                                <i class="fa-solid fa-play"></i>

                            </div>

                        </div>


                        <div class="video-info">

                            <h3>
                                <?php echo htmlspecialchars($reel['title']); ?>
                            </h3>

                            <p>
                                Glamour Gems
                            </p>

                            <span></span>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="reel-card">

                    <div class="video-info">

                        <h3>
                            No Instagram Reels Available
                        </h3>

                    </div>

                </div>

            <?php endif; ?>

        </div>


        <!--======================================
                INSTAGRAM BUTTON
        ======================================-->

        <div class="instagram-btn">

            <a href="#">

                <i class="fa-brands fa-instagram"></i>

                Follow Us on Instagram

            </a>

        </div>

    </div>

</section>

<!--=====================================
          NEWSLETTER
======================================-->

<section class="newsletter">

    <div class="container">

        <div class="newsletter-box">

            <div class="newsletter-content">

                <span>NEWSLETTER</span>

                <h2>Stay Connected With Glamour Gems</h2>

                <p>
                    Subscribe to receive exclusive offers, new arrivals,
                    jewellery trends and special discounts directly in your inbox.
                </p>

            </div>


            <form
                class="newsletter-form"
                method="POST"
                action=""
            >

                <input
                    type="email"
                    name="newsletter_email"
                    placeholder="Enter Your Email Address"
                    value="<?php echo htmlspecialchars($_POST['newsletter_email'] ?? ''); ?>"
                    required
                >

                <button
                    type="submit"
                    name="newsletter_subscribe"
                >
                    Subscribe Now
                </button>

            </form>


            <?php if (!empty($newsletter_message)): ?>

                <div
                    style="
                        margin-top:15px;
                        padding:12px 16px;
                        border-radius:6px;
                        font-family:Poppins,sans-serif;
                        <?php
                        if ($newsletter_type === 'success') {
                            echo 'background:#e8f5ee;color:#234B43;border:1px solid #b8dcc7;';
                        } else {
                            echo 'background:#fff0f0;color:#b42323;border:1px solid #f0b5b5;';
                        }
                        ?>
                    "
                >

                    <i
                        class="fa-solid
                        <?php
                        echo ($newsletter_type === 'success')
                            ? 'fa-circle-check'
                            : 'fa-circle-exclamation';
                        ?>"
                    ></i>

                    <?php echo htmlspecialchars($newsletter_message); ?>

                </div>

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
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script src="assets/js/script.js"></script>

<script>

/*======================================
            HOME SEARCH
======================================*/

document.addEventListener("DOMContentLoaded", function () {

    const searchBtn = document.getElementById("homeSearchBtn");
    const arrivalSection = document.querySelector(".new-arrivals");
    const arrivalGrid = document.querySelector(".arrival-grid");

    if (!searchBtn || !arrivalSection || !arrivalGrid) {
        console.log("Home Search elements missing");
        return;
    }

    searchBtn.onclick = function (e) {

        e.preventDefault();

        let box = document.getElementById("ggHomeSearch");

        /* CREATE SEARCH BOX */

        if (!box) {

            box = document.createElement("div");

            box.id = "ggHomeSearch";

            box.style.cssText = `
                width:90%;
                max-width:900px;
                margin:0 auto 30px auto;
                padding:15px;
                background:#ffffff;
                border:1px solid #eeeeee;
                border-radius:8px;
                box-shadow:0 5px 20px rgba(0,0,0,0.08);
                display:flex;
                gap:10px;
                box-sizing:border-box;
            `;

            box.innerHTML = `
                <input
                    type="text"
                    id="ggSearchInput"
                    placeholder="Search New Arrivals..."
                    autocomplete="off"
                    style="
                        flex:1;
                        height:50px;
                        padding:0 16px;
                        border:1px solid #ddd;
                        border-radius:5px;
                        outline:none;
                        font-family:Poppins,sans-serif;
                        font-size:15px;
                    "
                >

                <button
                    type="button"
                    id="ggSearchClose"
                    style="
                        width:50px;
                        height:50px;
                        border:none;
                        border-radius:5px;
                        background:#234B43;
                        color:#fff;
                        font-size:20px;
                        cursor:pointer;
                    "
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;

            arrivalSection.insertBefore(box, arrivalGrid);

            /* SEARCH */

            document
            .getElementById("ggSearchInput")
            .addEventListener("input", function () {

                const value = this.value.trim().toLowerCase();

                const cards =
                    arrivalGrid.querySelectorAll(".arrival-card");

                let found = 0;

                cards.forEach(function (card) {

                    const title = card.querySelector("h3");

                    if (!title) return;

                    const name =
                        title.textContent.trim().toLowerCase();

                    if (
                        value === "" ||
                        name.includes(value)
                    ) {

                        card.style.display = "";

                        found++;

                    } else {

                        card.style.display = "none";

                    }

                });

                let message =
                    document.getElementById("ggNoArrival");

                if (!message) {

                    message =
                    document.createElement("div");

                    message.id = "ggNoArrival";

                    message.textContent =
                    "No New Arrivals Found";

                    message.style.cssText = `
                        width:100%;
                        text-align:center;
                        padding:30px 0;
                        color:#777;
                        font-family:Poppins,sans-serif;
                        display:none;
                    `;

                    arrivalGrid.parentNode.insertBefore(
                        message,
                        arrivalGrid
                    );
                }

                if (value !== "" && found === 0) {

                    message.style.display = "block";

                } else {

                    message.style.display = "none";

                }

            });

            /* CLOSE */

            document
            .getElementById("ggSearchClose")
            .addEventListener("click", function () {

                box.style.display = "none";

                const input =
                    document.getElementById("ggSearchInput");

                if (input) {
                    input.value = "";
                }

                arrivalGrid
                .querySelectorAll(".arrival-card")
                .forEach(function (card) {
                    card.style.display = "";
                });

                const message =
                    document.getElementById("ggNoArrival");

                if (message) {
                    message.style.display = "none";
                }

            });

        }

        box.style.display = "flex";

        box.scrollIntoView({
            behavior: "smooth",
            block: "center"
        });

        setTimeout(function () {

            const input =
                document.getElementById("ggSearchInput");

            if (input) {
                input.focus();
            }

        }, 300);

    };

});

</script>

</body>
</html>