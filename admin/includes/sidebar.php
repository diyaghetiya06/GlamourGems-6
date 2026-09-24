<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav id="sidebar">

    <!--======================================
                SIDEBAR HEADER
    ======================================-->

    <div class="sidebar-header">

        <a
            href="<?php echo ADMIN_URL; ?>dashboard.php"
            class="brand-title"
        >
            <i class="fa-solid fa-gem"></i>
            <span>GLAMOUR GEMS</span>
        </a>

    </div>


    <div class="sidebar-menu">


        <!--======================================
                    MAIN
        ======================================-->

        <div class="menu-label">
            Main
        </div>

        <ul class="nav flex-column">

            <li class="nav-item">

                <a
                    class="nav-link-custom <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>dashboard.php"
                >

                    <i class="fa-solid fa-chart-pie"></i>

                    <span>Dashboard</span>

                </a>

            </li>

        </ul>


        <!--======================================
                CATALOG MANAGEMENT
        ======================================-->

        <div class="menu-label">
            Catalog Management
        </div>

        <ul class="nav flex-column">


            <!-- All Products -->

            <li class="nav-item">

                <a
                    class="nav-link-custom <?php echo ($current_page == 'products.php' || $current_page == 'edit-product.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>products.php"
                >

                    <i class="fa-solid fa-boxes-stacked"></i>

                    <span>All Products</span>

                </a>

            </li>

            <!-- Banners -->

            <li class="nav-item">

                <a
                    class="nav-link-custom <?php echo ($current_page == 'banners.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>banners.php"
                >

                    <i class="fa-solid fa-images"></i>

                    <span>Banners</span>

                </a>

            </li>


            <!-- Categories -->

            <li class="nav-item">

                <a
                    class="nav-link-custom <?php echo ($current_page == 'categories.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>categories.php"
                >

                    <i class="fa-solid fa-tags"></i>

                    <span>Categories</span>

                </a>

            </li>


            <!-- Materials -->

            <li class="nav-item">

                <a
                    class="nav-link-custom <?php echo ($current_page == 'materials.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>materials.php"
                >

                    <i class="fa-solid fa-layer-group"></i>

                    <span>Materials</span>

                </a>

            </li>


            <!-- Curated Looks -->

            <li class="nav-item">

                <a
                    class="nav-link-custom <?php echo ($current_page == 'curated-looks.php' || $current_page == 'edit-curated-look.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>curated-looks.php"
                >

                    <i class="fa-solid fa-star"></i>

                    <span>Curated Looks</span>

                </a>

            </li>


            <!-- New Arrivals -->

            <li class="nav-item">

                <a
                    class="nav-link-custom <?php echo ($current_page == 'new-arrivals.php' || $current_page == 'edit-new-arrival.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>new-arrivals.php"
                >

                    <i class="fa-solid fa-wand-magic-sparkles"></i>

                    <span>New Arrivals</span>

                </a>

            </li>


            <!-- Journey Numbers -->

            <li class="nav-item">

                <a
                    class="nav-link-custom <?php echo ($current_page == 'journey-numbers.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>journey-numbers.php"
                >

                    <i class="fa-solid fa-chart-simple"></i>

                    <span>Journey Numbers</span>

                </a>

            </li>

            <li class="nav-item">
                    <a
                        class="nav-link-custom <?php echo ($current_page == 'gold-collection.php') ? 'active' : ''; ?>"
                        href="<?php echo ADMIN_URL; ?>gold-collection.php"
                    >
                        <i class="fa-solid fa-gem"></i>
                        <span>Gold Collection</span>
                    </a>
             </li>

             <li class="nav-item">
                <a
                    class="nav-link-custom <?php echo ($current_page == 'silver-collection.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>silver-collection.php"
                >
                    <i class="fa-solid fa-ring"></i>
                    <span>Silver Collection</span>
                </a>
            </li>

            <!--======================================
                        ROSE GOLD COLLECTION
              ======================================-->

                <li class="nav-item">
                    <a
                        class="nav-link-custom <?php echo ($current_page == 'rose-gold-collection.php') ? 'active' : ''; ?>"
                        href="<?php echo ADMIN_URL; ?>rose-gold-collection.php"
                    >
                        <i class="fa-solid fa-ring"></i>
                        <span>Rose Gold Collection</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link-custom <?php echo ($current_page == 'diamond-collection.php') ? 'active' : ''; ?>"
                        href="<?php echo ADMIN_URL; ?>diamond-collection.php"
                    >
                        <i class="fa-solid fa-gem"></i>
                        <span>Diamond Collection</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link-custom <?php echo ($current_page == 'bridal-collection.php') ? 'active' : ''; ?>"
                        href="<?php echo ADMIN_URL; ?>bridal-collection.php"
                    >
                        <i class="fa-solid fa-crown"></i>
                        <span>Bridal Collection</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link-custom <?php echo ($current_page == 'groom-collection.php') ? 'active' : ''; ?>"
                        href="<?php echo ADMIN_URL; ?>groom-collection.php"
                    >
                        <i class="fa-solid fa-user-tie"></i>
                        <span>Groom Collection</span>
                    </a>
                </li>

                          <li class="nav-item">
                    <a
                        class="nav-link-custom <?php echo ($current_page == 'kids-collection.php') ? 'active' : ''; ?>"
                        href="<?php echo ADMIN_URL; ?>kids-collection.php"
                    >
                        <i class="fa-solid fa-child"></i>
                        <span>Kids Collection</span>
                    </a>
                </li>

        </ul>


        <!--======================================
                    SALES & ORDERS
        ======================================-->

        <div class="menu-label">
            Sales & Orders
        </div>

        <ul class="nav flex-column">


            <!-- Orders -->

            <li class="nav-item">

                <a
                    class="nav-link-custom <?php echo ($current_page == 'orders.php' || $current_page == 'order-details.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>orders.php"
                >

                    <i class="fa-solid fa-bag-shopping"></i>

                    <span>Orders</span>

                </a>

            </li>


            <!-- Payments -->

            <li class="nav-item">

                <a
                    class="nav-link-custom <?php echo ($current_page == 'payments.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>payments.php"
                >

                    <i class="fa-solid fa-credit-card"></i>

                    <span>Payments</span>

                </a>

            </li>

        </ul>


        <!--======================================
                    CUSTOMERS
        ======================================-->

        <div class="menu-label">
            Customers
        </div>

        <ul class="nav flex-column">


            <!-- Customers -->

            <li class="nav-item">

                <a
                    class="nav-link-custom <?php echo ($current_page == 'users.php' || $current_page == 'customer-details.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>users.php"
                >

                    <i class="fa-solid fa-users"></i>

                    <span>Customers</span>

                </a>

            </li>


            <!-- Wishlist -->

            <li class="nav-item">

                <a
                    class="nav-link-custom <?php echo ($current_page == 'wishlist.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>wishlist.php"
                >

                    <i class="fa-regular fa-heart"></i>

                    <span>Wishlist</span>

                </a>

            </li>


            <!-- Customer Reviews -->

            <li class="nav-item">

                <a
                    class="nav-link-custom <?php echo ($current_page == 'customer-reviews.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>customer-reviews.php"
                >

                    <i class="fa-solid fa-star"></i>

                    <span>Customer Reviews</span>

                </a>

            </li>

        </ul>


        <!--======================================
                    COMMUNICATION
        ======================================-->

        <div class="menu-label">
            Communication
        </div>

        <ul class="nav flex-column">


            <!-- Contact Messages -->

            <li class="nav-item">

                <a
                    class="nav-link-custom <?php echo ($current_page == 'contacts.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>contacts.php"
                >

                    <i class="fa-solid fa-envelope"></i>

                    <span>Contact Messages</span>

                </a>

            </li>


            <!-- Newsletter Subscribers -->

            <li class="nav-item">

                <a
                    class="nav-link-custom <?php echo ($current_page == 'newsletter-subscribers.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>newsletter-subscribers.php"
                >
                    <i class="fa-solid fa-envelope"></i>
                    <span>Newsletter Subscribers</span>
                </a>

        </li>

        </ul>


        <!--======================================
                WEBSITE MANAGEMENT
        ======================================-->

        <div class="menu-label">
            Website Management
        </div>

        <ul class="nav flex-column">
            
            <li class="nav-item">
                <a
                    class="nav-link-custom <?php echo ($current_page == 'instagram-reels.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>instagram-reels.php"
                >
                    <i class="fa-brands fa-instagram"></i>
                    <span>Instagram Reels</span>
                </a>
            </li>

            <!-- Site Content -->

            <li class="nav-item">

                <a
                    class="nav-link-custom <?php echo ($current_page == 'site-content.php') ? 'active' : ''; ?>"
                    href="<?php echo ADMIN_URL; ?>site-content.php"
                >

                    <i class="fa-solid fa-globe"></i>

                    <span>Site Content</span>

                </a>

            </li>

        </ul>


        <!--======================================
                    ACCOUNT
        ======================================-->

        <div class="menu-label">
            Account
        </div>

        <ul class="nav flex-column mb-4">


            <!-- Logout -->

            <li class="nav-item">

                <a
                    class="nav-link-custom text-danger"
                    href="<?php echo ADMIN_URL; ?>logout.php"
                >

                    <i class="fa-solid fa-right-from-bracket"></i>

                    <span>Logout</span>

                </a>

            </li>

        </ul>

    </div>


    <!--======================================
                SIDEBAR FOOTER
    ======================================-->

    <div class="sidebar-footer">

        <div class="d-flex align-items-center justify-content-between">

            <small class="text-muted">
                Status
            </small>

            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 rounded-pill fs-8">
                System Online
            </span>

        </div>

    </div>

</nav>