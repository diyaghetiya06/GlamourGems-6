<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Gold Collection';


/*======================================
        DELETE GOLD PRODUCT
======================================*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {

    $delete_id = (int) $_POST['delete_id'];

    $image_query = mysqli_query(
        $conn,
        "SELECT image
         FROM collection_gold_products
         WHERE id = $delete_id
         LIMIT 1"
    );

    $image_row = mysqli_fetch_assoc($image_query);

    if ($image_row) {

        mysqli_query(
            $conn,
            "DELETE FROM collection_gold_products
             WHERE id = $delete_id"
        );

        $image_path = __DIR__ . '/../assets/images/gold/' . $image_row['image'];

        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    header('Location: gold-collection.php?success=deleted');
    exit;
}


/*======================================
        TOGGLE STATUS
======================================*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {

    $toggle_id = (int) $_POST['toggle_id'];

    mysqli_query(
        $conn,
        "UPDATE collection_gold_products
         SET status = CASE
             WHEN status = 'active' THEN 'inactive'
             ELSE 'active'
         END
         WHERE id = $toggle_id"
    );

    header('Location: gold-collection.php?success=status');
    exit;
}


/*======================================
        FETCH GOLD PRODUCTS
======================================*/
$gold_query = mysqli_query(
    $conn,
    "SELECT *
     FROM collection_gold_products
     ORDER BY display_order ASC, id ASC"
);


/*======================================
        SUMMARY COUNTS
======================================*/
$total_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM collection_gold_products"
);

$total_row = mysqli_fetch_assoc($total_query);
$total_products = (int) $total_row['total'];


$active_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM collection_gold_products
     WHERE status = 'active'"
);

$active_row = mysqli_fetch_assoc($active_query);
$active_products = (int) $active_row['total'];


$inactive_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM collection_gold_products
     WHERE status = 'inactive'"
);

$inactive_row = mysqli_fetch_assoc($inactive_query);
$inactive_products = (int) $inactive_row['total'];


include(__DIR__ . '/includes/header.php');

?>

<div class="admin-page gold-collection-page">

    <!--======================================
            PAGE HEADER
    ======================================-->
    <div class="gg-page-header">

        <div>
            <h1 class="gg-page-title">
                Gold Collection
            </h1>

            <p class="gg-page-subtitle">
                Manage products displayed in the Gold Collection.
            </p>
        </div>

        <a
            href="add-gold-collection.php"
            class="btn-gg-gold"
        >
            <i class="fa-solid fa-plus"></i>
            Add Gold Product
        </a>

    </div>


    <!--======================================
            SUMMARY CARDS
    ======================================-->
    <div class="gg-stats-grid">

        <div class="gg-stat-card">

            <div class="gg-stat-icon">
                <i class="fa-solid fa-gem"></i>
            </div>

            <div>
                <span class="gg-stat-label">
                    Total Products
                </span>

                <strong class="gg-stat-value">
                    <?php echo $total_products; ?>
                </strong>
            </div>

        </div>


        <div class="gg-stat-card">

            <div class="gg-stat-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <div>
                <span class="gg-stat-label">
                    Active
                </span>

                <strong class="gg-stat-value">
                    <?php echo $active_products; ?>
                </strong>
            </div>

        </div>


        <div class="gg-stat-card">

            <div class="gg-stat-icon">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>

            <div>
                <span class="gg-stat-label">
                    Inactive
                </span>

                <strong class="gg-stat-value">
                    <?php echo $inactive_products; ?>
                </strong>
            </div>

        </div>

    </div>


    <!--======================================
            GOLD PRODUCTS TABLE
    ======================================-->
    <div class="gg-card">

        <div class="gg-card-header">

            <h2 class="gg-card-title">
                Gold Collection Products
            </h2>

        </div>


        <div class="gg-card-body">

            <div class="table-responsive">

                <table class="table-gg">

                    <thead>

                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Display Order</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>

                    </thead>


                    <tbody>

                        <?php if (mysqli_num_rows($gold_query) > 0): ?>

                            <?php while ($product = mysqli_fetch_assoc($gold_query)): ?>

                                <tr>

                                    <td>
                                        <?php echo (int) $product['id']; ?>
                                    </td>


                                    <!--======================================
                                            IMAGE
                                    ======================================-->
                                    <td>

                                        <img
                                            src="../assets/images/gold/<?php echo htmlspecialchars($product['image']); ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                                            style="
                                                width:70px;
                                                height:70px;
                                                object-fit:cover;
                                                border-radius:8px;
                                                border:1px solid rgba(200,161,101,0.25);
                                            "
                                        >

                                    </td>


                                    <!--======================================
                                            PRODUCT NAME
                                    ======================================-->
                                    <td>

                                        <strong>
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </strong>

                                    </td>


                                    <!--======================================
                                            PRICE
                                    ======================================-->
                                    <td>

                                        ₹<?php echo number_format((float) $product['price'], 0, '.', ','); ?>

                                    </td>


                                    <!--======================================
                                            DISPLAY ORDER
                                    ======================================-->
                                    <td>

                                        <?php echo (int) $product['display_order']; ?>

                                    </td>


                                    <!--======================================
                                            STATUS
                                    ======================================-->
                                    <td>

                                        <?php if ($product['status'] === 'active'): ?>

                                            <span class="badge-gg badge-gg-success">
                                                Active
                                            </span>

                                        <?php else: ?>

                                            <span class="badge-gg badge-gg-danger">
                                                Inactive
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!--======================================
                                            ACTIONS
                                    ======================================-->
                                    <td>

                                        <div style="
                                            display:flex;
                                            gap:8px;
                                            align-items:center;
                                            flex-wrap:wrap;
                                        ">

                                            <a
                                                href="edit-gold-collection.php?id=<?php echo (int) $product['id']; ?>"
                                                class="btn-gg-gold"
                                            >
                                                <i class="fa-solid fa-pen"></i>
                                                Edit
                                            </a>


                                            <form
                                                method="POST"
                                                style="display:inline;"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="toggle_id"
                                                    value="<?php echo (int) $product['id']; ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn-gg-secondary"
                                                >
                                                    <?php if ($product['status'] === 'active'): ?>

                                                        <i class="fa-solid fa-eye-slash"></i>
                                                        Hide

                                                    <?php else: ?>

                                                        <i class="fa-solid fa-eye"></i>
                                                        Show

                                                    <?php endif; ?>

                                                </button>

                                            </form>


                                            <form
                                                method="POST"
                                                style="display:inline;"
                                                onsubmit="return confirm('Are you sure you want to delete this Gold Collection product?');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="delete_id"
                                                    value="<?php echo (int) $product['id']; ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn-gg-secondary"
                                                >
                                                    <i class="fa-solid fa-trash"></i>
                                                    Delete
                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="7"
                                    style="text-align:center; padding:40px;"
                                >
                                    No Gold Collection products found.
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<?php

include(__DIR__ . '/includes/footer.php');

?>