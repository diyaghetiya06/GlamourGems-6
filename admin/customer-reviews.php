<?php

require_once "includes/auth_check.php";
require_once "../config/database.php";
require_once "includes/functions.php";


/*======================================
        DELETE CUSTOMER REVIEW
======================================*/

if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    mysqli_query(
        $conn,
        "DELETE FROM customer_reviews WHERE id = $id"
    );

    set_flash(
        "success",
        "Customer review deleted successfully."
    );

    header("Location: customer-reviews.php");
    exit;
}


/*======================================
        FETCH REVIEWS
======================================*/

$reviews_query = mysqli_query(
    $conn,
    "SELECT *
     FROM customer_reviews
     ORDER BY id DESC"
);

require_once "includes/header.php";
require_once "includes/sidebar.php";

?>


<main class="main-content">

    <div class="admin-page-content">


        <!--======================================
                PAGE HEADER
        ======================================-->

        <div class="page-header">

            <div>

                <h1>Customer Reviews</h1>

                <p>
                    Manage customer reviews submitted from the website.
                </p>

            </div>

        </div>


        <?php display_flash(); ?>


        <!--======================================
                REVIEWS LIST
        ======================================-->

        <div class="gg-card">

            <div class="gg-card-header">

                <h2>
                    All Customer Reviews
                </h2>

            </div>


            <div class="gg-card-body">

                <div class="table-responsive">

                    <table class="gg-table">

                        <thead>

                            <tr>

                                <th>ID</th>

                                <th>Customer</th>

                                <th>Location</th>

                                <th>Rating</th>

                                <th>Review</th>

                                <th>Status</th>

                                <th>Actions</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (
                                $reviews_query &&
                                mysqli_num_rows($reviews_query) > 0
                            ): ?>

                                <?php while (
                                    $review =
                                    mysqli_fetch_assoc($reviews_query)
                                ): ?>

                                    <tr>


                                        <!-- ID -->

                                        <td>

                                            <?php
                                            echo (int)
                                                $review['id'];
                                            ?>

                                        </td>


                                        <!-- Customer -->

                                        <td>

                                            <strong>

                                                <?php
                                                echo htmlspecialchars(
                                                    $review['customer_name']
                                                );
                                                ?>

                                            </strong>

                                        </td>


                                        <!-- Location -->

                                        <td>

                                            <?php

                                            echo !empty(
                                                $review['location']
                                            )

                                                ? htmlspecialchars(
                                                    $review['location']
                                                )

                                                : '-';

                                            ?>

                                        </td>


                                        <!-- Rating -->

                                        <td>

                                            <span
                                                class="review-stars"
                                            >

                                                <?php

                                                for (
                                                    $i = 1;
                                                    $i <= 5;
                                                    $i++
                                                ) {

                                                    echo (
                                                        $i <=
                                                        (int)$review['rating']
                                                    )

                                                        ? '★'

                                                        : '☆';

                                                }

                                                ?>

                                            </span>

                                        </td>


                                        <!-- Review -->

                                        <td>

                                            <div
                                                class="review-text"
                                            >

                                                <?php

                                                echo htmlspecialchars(
                                                    $review['review']
                                                );

                                                ?>

                                            </div>

                                        </td>


                                        <!-- Status -->

                                        <td>

                                            <?php if (
                                                $review['status']
                                                === 'active'
                                            ): ?>

                                                <span
                                                    class="status-active"
                                                >
                                                    Active
                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="status-inactive"
                                                >
                                                    Inactive
                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- Actions -->

                                        <td>

                                            <a
                                                href="customer-reviews.php?delete=<?php echo (int)$review['id']; ?>"
                                                class="btn-action btn-action-danger"
                                                title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this review?');"
                                            >

                                                <i
                                                    class="fa-solid fa-trash"
                                                ></i>

                                            </a>

                                        </td>


                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="7"
                                        style="text-align:center;"
                                    >

                                        No customer reviews found.

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


    </div>

</main>


<style>

/*======================================
        CUSTOMER REVIEWS PAGE
======================================*/

.admin-page-content .gg-card {

    background: #111917;

    border: 1px solid #3d463f;

    border-radius: 14px;

    overflow: hidden;

    margin-bottom: 24px;

}


/*======================================
        CARD HEADER
======================================*/

.admin-page-content .gg-card-header {

    padding: 24px 28px;

    border-bottom: 1px solid #29312d;

}

.admin-page-content .gg-card-header h2 {

    margin: 0;

    font-family: "Cinzel", serif;

    font-size: 27px;

    font-weight: 500;

    color: #ffffff;

}


/*======================================
        CARD BODY
======================================*/

.admin-page-content .gg-card-body {

    padding: 28px;

}


/*======================================
        TABLE RESPONSIVE
======================================*/

.admin-page-content .table-responsive {

    width: 100%;

    overflow-x: auto;

}


/*======================================
        CUSTOMER REVIEWS TABLE
======================================*/

.admin-page-content .gg-table {

    width: 100%;

    min-width: 900px;

    border-collapse: collapse;

    table-layout: fixed;

}


/*======================================
        TABLE HEADER
======================================*/

.admin-page-content .gg-table th {

    padding: 14px 12px;

    text-align: left;

    color: #c8a165;

    font-size: 13px;

    font-weight: 600;

    white-space: nowrap;

    border-bottom: 1px solid #39433e;

}


/*======================================
        TABLE BODY
======================================*/

.admin-page-content .gg-table td {

    padding: 15px 12px;

    color: #ffffff;

    font-size: 13px;

    line-height: 1.5;

    vertical-align: middle;

    border-bottom: 1px solid #252d29;

}


/*======================================
        COLUMN WIDTHS
======================================*/

.admin-page-content .gg-table th:nth-child(1),
.admin-page-content .gg-table td:nth-child(1) {

    width: 6%;

}

.admin-page-content .gg-table th:nth-child(2),
.admin-page-content .gg-table td:nth-child(2) {

    width: 15%;

}

.admin-page-content .gg-table th:nth-child(3),
.admin-page-content .gg-table td:nth-child(3) {

    width: 14%;

}

.admin-page-content .gg-table th:nth-child(4),
.admin-page-content .gg-table td:nth-child(4) {

    width: 11%;

}

.admin-page-content .gg-table th:nth-child(5),
.admin-page-content .gg-table td:nth-child(5) {

    width: 32%;

}

.admin-page-content .gg-table th:nth-child(6),
.admin-page-content .gg-table td:nth-child(6) {

    width: 12%;

}

.admin-page-content .gg-table th:nth-child(7),
.admin-page-content .gg-table td:nth-child(7) {

    width: 10%;

}


/*======================================
        CUSTOMER NAME
======================================*/

.admin-page-content .gg-table td:nth-child(2) strong {

    color: #ffffff;

    font-weight: 600;

}


/*======================================
        REVIEW TEXT
======================================*/

.admin-page-content .review-text {

    color: #e3e7e5;

    line-height: 1.6;

    word-break: normal;

    overflow-wrap: break-word;

}


/*======================================
        RATING
======================================*/

.admin-page-content .review-stars {

    color: #c8a165;

    font-size: 14px;

    white-space: nowrap;

}


/*======================================
        ACTIVE STATUS
======================================*/

.admin-page-content .status-active {

    display: inline-block;

    padding: 6px 12px;

    border-radius: 20px;

    background: rgba(46, 204, 113, 0.12);

    color: #2ecc71;

    font-size: 12px;

    font-weight: 600;

    white-space: nowrap;

}


/*======================================
        INACTIVE STATUS
======================================*/

.admin-page-content .status-inactive {

    display: inline-block;

    padding: 6px 12px;

    border-radius: 20px;

    background: rgba(231, 76, 60, 0.12);

    color: #e74c3c;

    font-size: 12px;

    font-weight: 600;

    white-space: nowrap;

}


/*======================================
        ACTION BUTTON
======================================*/

.admin-page-content .btn-action {

    width: 36px;

    height: 36px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border: 1px solid #39433e;

    border-radius: 7px;

    background: transparent;

    color: #ffffff;

    text-decoration: none;

    transition: 0.3s;

}


/*======================================
        DELETE BUTTON
======================================*/

.admin-page-content .btn-action-danger:hover {

    background: #b02a37;

    border-color: #b02a37;

}


/*======================================
        RESPONSIVE
======================================*/

@media (max-width: 768px) {

    .admin-page-content .gg-card-header {

        padding: 20px;

    }

    .admin-page-content .gg-card-header h2 {

        font-size: 22px;

    }

    .admin-page-content .gg-card-body {

        padding: 20px;

    }

}

</style>


<?php require_once "includes/footer.php"; ?>