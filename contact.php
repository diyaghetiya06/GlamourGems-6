<?php

require_once(__DIR__ . '/config/database.php');

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (
        $name === '' ||
        $email === '' ||
        $phone === '' ||
        $subject === '' ||
        $message === ''
    ) {

        $error_message = 'Please fill in all fields.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error_message = 'Please enter a valid email address.';

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO contacts
            (name, email, phone, subject, message, status)
            VALUES (?, ?, ?, ?, ?, 'unread')"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "sssss",
                $name,
                $email,
                $phone,
                $subject,
                $message
            );

            if (mysqli_stmt_execute($stmt)) {

                $success_message =
                    'Your message has been sent successfully. We will get back to you soon.';

                $_POST = [];

            } else {

                $error_message =
                    'Your message could not be sent. Please try again.';
            }

            mysqli_stmt_close($stmt);

        } else {

            $error_message =
                'Something went wrong. Please try again.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contact | Glamour Gems</title>

    <!-- Google Fonts -->

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Cinzel:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- CSS -->

    <link rel="stylesheet" href="assets/css/contact.css">

</head>

<body>

<!--==================================
            TOP BAR
===================================-->

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

<!--==================================
            HEADER
===================================-->

<header>

    <nav class="navbar">

        <a href="index.php" class="logo">
        <img src="assets/images/contact/logo.png" alt="Glamour Gems Logo">
        <span>GLAMOUR GEMS</span>
        </a>

        <ul class="nav-menu">

            <li><a href="index.php">Home</a></li>

            <li><a href="shop.php">Shop</a></li>

            <li><a href="collections.php">Collections</a></li>

            <li><a href="about.php">About</a></li>

            <li><a href="contact.php" class="active">Contact</a></li>

        </ul>

        <div class="nav-icons">

              <!-- Search -->
            <a href="shop.php" class="search-toggle">
                <i class="fa-solid fa-magnifying-glass"></i>
            </a>

                <!-- Wishlist -->
            <a href="shop.php" class="wishlist-nav">
                <i class="fa-regular fa-heart"></i>
                <span id="wishlistCount">0</span>
            </a>
            
             <!-- Cart -->
            <a href="cart.php">
                <i class="fa-solid fa-bag-shopping"></i>
                <span id="cartCount">0</span>
            </a>
           
               <!-- Account -->
            <a href="login.php">
                <i class="fa-regular fa-user"></i>
            </a>

           

        </div>

    </nav>

</header>

<!--==================================
            HERO
===================================-->

<section class="contact-hero">

    <div class="contact-overlay">

        <div class="contact-content">

            <h1>

                Contact Us

            </h1>

            <p>

                We'd love to hear from you. Whether you have a question
                about our jewellery, need assistance, or want to discuss
                a custom design, our team is always here to help you.

            </p>

        </div>

    </div>

</section>

<!--==================================
            BREADCRUMB
===================================-->

<section class="breadcrumb">

    <div class="container">

        <a href="index.php">

            Home

        </a>

        <span>/</span>

        <span>

            Contact

        </span>

    </div>

</section>

<!--==================================
        NEXT SECTION
===================================-->

<!--=================================
        CONTACT SECTION
==================================-->

<section class="contact-section" id="contact-form">

   <div class="section-heading">

    <span>Get In Touch</span>

    <h2>We're Always Here To Help</h2>

    <p>
        Have questions about our jewellery or need assistance?
        Contact our team and we'll be happy to help you.
    </p>

</div>

        <!--=========================
            CONTACT INFO
        ==========================-->

        <div class="contact-info">

            <!-- Address -->

            <div class="info-card">

                <div class="info-icon">

                    <i class="fa-solid fa-location-dot"></i>

                </div>

                <h3>Address</h3>

                <p>

                    101 Luxury Street<br>
                    Mumbai, Maharashtra 400001

                </p>

            </div>

            <!-- Phone -->

            <div class="info-card">

                <div class="info-icon">

                    <i class="fa-solid fa-phone"></i>

                </div>

                <h3>Phone</h3>

                <p>

                    +91 98765 43210<br>
                    +91 91234 56789

                </p>

            </div>

            <!-- Email -->

            <div class="info-card">

                <div class="info-icon">

                    <i class="fa-solid fa-envelope"></i>

                </div>

                <h3>Email</h3>

                <p>

                    support@glamourgems.com<br>
                    info@glamourgems.com

                </p>

            </div>

            <!-- Working Hours -->

            <div class="info-card">

                <div class="info-icon">

                    <i class="fa-solid fa-clock"></i>

                </div>

                <h3>Working Hours</h3>

                <p>

                    Monday - Saturday<br>
                    10:00 AM - 8:00 PM

                </p>

            </div>

        </div>

        <!--=========================
            CONTACT FORM
        ==========================-->

        <div class="contact-form">

            <h2>Send Us A Message</h2>

            <p>

                Fill out the form below and our team will contact you shortly.

            </p>

                  <form action="contact.php" method="POST">

    <div class="input-group">

        <input
            type="text"
            name="name"
            placeholder="Full Name"
            value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
            required
        >

        <input
            type="email"
            name="email"
            placeholder="Email Address"
            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
            required
        >

    </div>


    <div class="input-group">

        <input
            type="text"
            name="phone"
            placeholder="Phone Number"
            value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
            required
        >

        <input
            type="text"
            name="subject"
            placeholder="Subject"
            value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>"
            required
        >

    </div>


    <textarea
        name="message"
        rows="7"
        placeholder="Write Your Message..."
        required
    ><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>


    <button type="submit">
        Send Message
    </button>

</form>

        </div>

    </div>

</section>

<!--=================================
        STORE LOCATION
==================================-->

<section class="store-location">

    <div class="container">

        <div class="section-heading">

            <span>Visit Our Store</span>

            <h2>Find Glamour Gems</h2>

            <p>

                Visit our luxury showroom and explore our exclusive jewellery
                collections crafted with elegance and perfection.

            </p>

        </div>

        <div class="map-container">

            <iframe
                src="https://www.google.com/maps?q=Mumbai,Maharashtra&output=embed"
                allowfullscreen=""
                loading="lazy">
            </iframe>

        </div>

        <div class="store-features">

            <div class="feature-box">

                <i class="fa-solid fa-location-dot"></i>

                <h3>Prime Location</h3>

                <p>
                    Located in the heart of Mumbai for easy access.
                </p>

            </div>

            <div class="feature-box">

                <i class="fa-solid fa-car"></i>

                <h3>Free Parking</h3>

                <p>
                    Spacious parking available for all visitors.
                </p>

            </div>

            <div class="feature-box">

                <i class="fa-solid fa-train-subway"></i>

                <h3>Easy Transport</h3>

                <p>
                    Well connected by metro, bus and taxi services.
                </p>

            </div>

            <div class="feature-box">

                <i class="fa-solid fa-mug-hot"></i>

                <h3>Luxury Lounge</h3>

                <p>
                    Relax in our premium customer lounge while shopping.
                </p>

            </div>

        </div>

    </div>

</section>

<!--=================================
            FAQ SECTION
==================================-->

<section class="faq-section">

    <div class="container">

        <div class="section-heading">

            <span>Frequently Asked Questions</span>

            <h2>Need Help?</h2>

            <p>

                Find answers to the most commonly asked questions about
                our jewellery, orders and services.

            </p>

        </div>

        <div class="faq-container">

            <details>

                <summary>How can I place an order?</summary>

                <p>

                    Browse our collections, choose your favourite jewellery,
                    and place your order through our secure checkout process.

                </p>

            </details>

            <details>

                <summary>Do you offer custom jewellery?</summary>

                <p>

                    Yes. We create personalized jewellery based on your
                    preferred design and requirements.

                </p>

            </details>

            <details>

                <summary>What payment methods do you accept?</summary>

                <p>

                    We accept UPI, Credit Card, Debit Card,
                    Net Banking and Cash on Delivery (selected locations).

                </p>

            </details>

            <details>

                <summary>How long does delivery take?</summary>

                <p>

                    Standard delivery usually takes 3–7 business days,
                    depending on your location.

                </p>

            </details>

            <details>

                <summary>Do you provide jewellery certificates?</summary>

                <p>

                    Yes. All certified diamond jewellery comes with
                    authenticity and quality certificates.

                </p>

            </details>

            <details>

                <summary>What is your return policy?</summary>

                <p>

                    We offer an easy return and exchange policy
                    according to our terms and conditions.

                </p>

            </details>

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

                    <li><a href="collections.php?collection=diamond">Diamond Jewellery</a></li>

                    <li><a href="collections.php?collection=silver">Silver Jewellery</a></li>

                    <li><a href="collections.php?collection=bridal">Bridal Collection</a></li>

                    <li><a href="collections.php?collection=groom">Groom Collection</a></li>

                    <li><a href="collections.php?collection=kids">Kids Collection</a></li>

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

<!-- =====================================
     MESSAGE SUCCESS POPUP
====================================== -->

<?php if ($success_message !== ''): ?>

<div class="message-popup" id="messagePopup">

    <div class="message-popup-box">

        <div class="message-popup-icon">
            <i class="fa-solid fa-check"></i>
        </div>

        <h3>Message Sent Successfully!</h3>

        <p>
            Thank you for contacting Glamour Gems.
            Your message has been received successfully.
            Our team will get back to you soon.
        </p>

        <button type="button" id="closeMessagePopup">
            OK
        </button>

    </div>

</div>

<script>

document.addEventListener("DOMContentLoaded", function () {

    const popup = document.getElementById("messagePopup");
    const closeButton = document.getElementById("closeMessagePopup");

    if (popup) {

        /* Open popup automatically */
        setTimeout(function () {
            popup.classList.add("show");
        }, 100);

    }

    if (closeButton) {

        closeButton.addEventListener("click", function () {
            popup.classList.remove("show");
        });

    }

});

</script>

<?php endif; ?>


</body>
</html>