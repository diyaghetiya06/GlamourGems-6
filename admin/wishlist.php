<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/includes/functions.php');

$page_title = 'Wishlist Management';


/*======================================
        DELETE WISHLIST ITEM
======================================*/

if (isset($_GET['delete'])) {

    $wishlist_id = intval($_GET['delete']);
    $item_type = $_GET['type'] ?? 'product';

    if ($wishlist_id > 0) {

        if ($item_type === 'new_arrival') {

            $stmt = mysqli_prepare(
                $conn,
                "DELETE FROM new_arrival_wishlist WHERE id = ?"
            );

        } else {

            $stmt = mysqli_prepare(
                $conn,
                "DELETE FROM wishlist WHERE id = ?"
            );

        }

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $wishlist_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    }

    header('Location: ' . ADMIN_URL . 'wishlist.php');
    exit;
}


/*======================================
        FETCH ALL WISHLIST ITEMS
======================================*/

$wishlist_sql = "

    /*==================================
            SHOP PRODUCTS
    ==================================*/

    SELECT

        wishlist.id AS wishlist_id,

        'product' AS item_type,

        wishlist.created_at AS wishlist_date,

        users.name AS customer_name,

        users.email AS customer_email,

        products.name AS product_name,

        products.image AS product_image,

        products.price AS product_price,

        products.sale_price AS product_sale_price,

        products.stock AS product_stock

    FROM wishlist

    INNER JOIN users
        ON wishlist.user_id = users.id

    INNER JOIN products
        ON wishlist.product_id = products.id


    UNION ALL


    /*==================================
            HOME NEW ARRIVALS
    ==================================*/

    SELECT

        new_arrival_wishlist.id AS wishlist_id,

        'new_arrival' AS item_type,

        new_arrival_wishlist.created_at AS wishlist_date,

        users.name AS customer_name,

        users.email AS customer_email,

        new_arrivals.title AS product_name,

        new_arrivals.image AS product_image,

        new_arrivals.price AS product_price,

        new_arrivals.old_price AS product_sale_price,

        1 AS product_stock

    FROM new_arrival_wishlist

    INNER JOIN users
        ON new_arrival_wishlist.user_id = users.id

    INNER JOIN new_arrivals
        ON new_arrival_wishlist.new_arrival_id = new_arrivals.id


    ORDER BY wishlist_date DESC

";


$wishlist_result = mysqli_query(
    $conn,
    $wishlist_sql
);


$wishlist_items = [];

if ($wishlist_result) {

    while ($row = mysqli_fetch_assoc($wishlist_result)) {

        $wishlist_items[] = $row;

    }

}


/*======================================
        ADMIN HEADER
======================================*/

include(__DIR__ . '/includes/header.php');

?>


<!--======================================
            PAGE HEADER
======================================-->

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">

    <div>

        <h2 class="page-header-title mb-1">
            Customer Wishlist
        </h2>

        <p class="page-header-sub mb-0">
            View products and new arrivals saved by your customers.
        </p>

    </div>

</div>


<!--======================================
            WISHLIST SUMMARY
======================================-->

<div class="row g-3 mb-4">


    <!-- Total Wishlist -->

    <div class="col-12 col-md-4">

        <div class="gg-card h-100">

            <div class="gg-card-body d-flex align-items-center gap-3">

                <div
                    class="rounded-circle d-flex align-items-center justify-content-center"
                    style="
                        width:48px;
                        height:48px;
                        background:rgba(200,161,101,0.15);
                        color:#C8A165;
                    "
                >

                    <i class="fa-regular fa-heart fs-5"></i>

                </div>

                <div>

                    <small class="text-muted d-block">
                        Total Wishlist Items
                    </small>

                    <h4 class="mb-0 text-light">
                        <?php echo count($wishlist_items); ?>
                    </h4>

                </div>

            </div>

        </div>

    </div>


    <!-- Shop Products -->

    <div class="col-12 col-md-4">

        <div class="gg-card h-100">

            <div class="gg-card-body d-flex align-items-center gap-3">

                <div
                    class="rounded-circle d-flex align-items-center justify-content-center"
                    style="
                        width:48px;
                        height:48px;
                        background:rgba(35,75,67,0.35);
                        color:#C8A165;
                    "
                >

                    <i class="fa-solid fa-boxes-stacked fs-5"></i>

                </div>

                <div>

                    <small class="text-muted d-block">
                        Shop Products
                    </small>

                    <h4 class="mb-0 text-light">

                        <?php

                        $product_count = 0;

                        foreach ($wishlist_items as $item) {

                            if ($item['item_type'] === 'product') {

                                $product_count++;

                            }

                        }

                        echo $product_count;

                        ?>

                    </h4>

                </div>

            </div>

        </div>

    </div>


    <!-- New Arrivals -->

    <div class="col-12 col-md-4">

        <div class="gg-card h-100">

            <div class="gg-card-body d-flex align-items-center gap-3">

                <div
                    class="rounded-circle d-flex align-items-center justify-content-center"
                    style="
                        width:48px;
                        height:48px;
                        background:rgba(200,161,101,0.15);
                        color:#C8A165;
                    "
                >

                    <i class="fa-solid fa-wand-magic-sparkles fs-5"></i>

                </div>

                <div>

                    <small class="text-muted d-block">
                        New Arrival Wishlist
                    </small>

                    <h4 class="mb-0 text-light">

                        <?php

                        $arrival_count = 0;

                        foreach ($wishlist_items as $item) {

                            if ($item['item_type'] === 'new_arrival') {

                                $arrival_count++;

                            }

                        }

                        echo $arrival_count;

                        ?>

                    </h4>

                </div>

            </div>

        </div>

    </div>

</div>


<!--======================================
            WISHLIST TABLE
======================================-->

<div class="gg-card">


    <!-- Card Header -->

    <div class="gg-card-header">

        <h5 class="gg-card-title">

            <i class="fa-regular fa-heart"></i>

            Wishlist Items
            (<?php echo count($wishlist_items); ?>)

        </h5>

    </div>


    <!-- Card Body -->

    <div class="gg-card-body p-0">

        <div class="table-responsive">

            <table class="table table-gg align-middle mb-0">


                <!--==================================
                        TABLE HEADER
                ==================================-->

                <thead>

                    <tr>

                        <th>
                            #
                        </th>

                        <th>
                            Customer
                        </th>

                        <th>
                            Product
                        </th>

                        <th>
                            Type
                        </th>

                        <th>
                            Image
                        </th>

                        <th>
                            Price
                        </th>

                        <th>
                            Added On
                        </th>

                        <th class="text-end">
                            Action
                        </th>

                    </tr>

                </thead>


                <!--==================================
                        TABLE BODY
                ==================================-->

                <tbody>


                    <?php if (!empty($wishlist_items)): ?>


                        <?php

                        $counter = 1;

                        foreach ($wishlist_items as $item):

                        ?>


                            <tr>


                                <!-- Number -->

                                <td>

                                    <?php echo $counter++; ?>

                                </td>


                                <!-- Customer -->

                                <td>

                                    <div class="fw-semibold text-light">

                                        <?php
                                        echo htmlspecialchars(
                                            $item['customer_name']
                                        );
                                        ?>

                                    </div>

                                    <small class="text-muted">

                                        <?php
                                        echo htmlspecialchars(
                                            $item['customer_email']
                                        );
                                        ?>

                                    </small>

                                </td>


                                <!-- Product -->

                                <td>

                                    <div class="fw-semibold text-light">

                                        <?php
                                        echo htmlspecialchars(
                                            $item['product_name']
                                        );
                                        ?>

                                    </div>

                                </td>


                                <!-- Type -->

                                <td>

                                    <?php if ($item['item_type'] === 'new_arrival'): ?>

                                        <span class="badge-gg badge-gg-success">

                                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>

                                            New Arrival

                                        </span>

                                    <?php else: ?>

                                        <span class="badge-gg badge-gg-warning">

                                            <i class="fa-solid fa-box me-1"></i>

                                            Product

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- Image -->

                                <td>

                                    <?php

                                    if (
                                        $item['item_type'] === 'new_arrival'
                                    ) {

                                        $image_path =
                                            '../assets/images/arrivals/' .
                                            $item['product_image'];

                                    } else {

                                        $image_path =
                                            '../assets/images/shops/' .
                                            $item['product_image'];

                                    }

                                    ?>


                                    <img
                                        src="<?php echo htmlspecialchars($image_path); ?>"
                                        alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                        style="
                                            width:55px;
                                            height:55px;
                                            object-fit:cover;
                                            border-radius:7px;
                                            border:1px solid #444;
                                        "
                                    >

                                </td>


                                <!-- Price -->

                                <td>

                                    <?php

                                    if (
                                        $item['item_type'] === 'new_arrival'
                                    ) {

                                        echo format_price(
                                            $item['product_price']
                                        );

                                    } elseif (
                                        !empty($item['product_sale_price']) &&
                                        $item['product_sale_price'] > 0
                                    ) {

                                    ?>

                                        <span class="fw-bold text-gold">

                                            <?php
                                            echo format_price(
                                                $item['product_sale_price']
                                            );
                                            ?>

                                        </span>

                                        <small
                                            class="text-muted text-decoration-line-through d-block"
                                        >

                                            <?php
                                            echo format_price(
                                                $item['product_price']
                                            );
                                            ?>

                                        </small>

                                    <?php

                                    } else {

                                        echo format_price(
                                            $item['product_price']
                                        );

                                    }

                                    ?>

                                </td>


                                <!-- Date -->

                                <td class="text-muted fs-8">

                                    <?php

                                    echo date(
                                        'd M Y, h:i A',
                                        strtotime(
                                            $item['wishlist_date']
                                        )
                                    );

                                    ?>

                                </td>


                                <!-- Delete -->

                                <td class="text-end">

                                    <a
                                        href="<?php echo ADMIN_URL; ?>wishlist.php?delete=<?php echo (int)$item['wishlist_id']; ?>&type=<?php echo urlencode($item['item_type']); ?>"
                                        class="btn-action text-danger"
                                        title="Remove Wishlist"
                                        onclick="return confirm('Are you sure you want to remove this wishlist item?');"
                                    >

                                        <i class="fa-solid fa-trash"></i>

                                    </a>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <!--==================================
                                EMPTY STATE
                        ==================================-->

                        <tr>

                            <td
                                colspan="8"
                                class="text-center py-5"
                            >

                                <div class="py-4">

                                    <i
                                        class="fa-regular fa-heart"
                                        style="
                                            font-size:45px;
                                            color:#C8A165;
                                        "
                                    ></i>

                                    <h5 class="text-light mt-3 mb-2">

                                        No Wishlist Items

                                    </h5>

                                    <p class="text-muted mb-0">

                                        No customers have added products
                                        to their wishlist yet.

                                    </p>

                                </div>

                            </td>

                        </tr>


                    <?php endif; ?>


                </tbody>

            </table>

        </div>

    </div>

</div>


<?php

/*======================================
            ADMIN FOOTER
======================================*/

include(__DIR__ . '/includes/footer.php');

?>