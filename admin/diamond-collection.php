<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Diamond Collection';


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


/*======================================
            DELETE PRODUCT
======================================*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_id'])
) {

    $delete_id = (int) $_POST['delete_id'];

    $image_query = mysqli_query(
        $conn,
        "SELECT image
         FROM collection_diamond_products
         WHERE id = $delete_id
         LIMIT 1"
    );

    $image_row = mysqli_fetch_assoc($image_query);

    if ($image_row) {

        mysqli_query(
            $conn,
            "DELETE FROM collection_diamond_products
             WHERE id = $delete_id"
        );

        $image_path =
            __DIR__ .
            '/../assets/images/diamond/' .
            $image_row['image'];

        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    header('Location: diamond-collection.php?success=deleted');
    exit;
}


/*======================================
            TOGGLE STATUS
======================================*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['toggle_id'])
) {

    $toggle_id = (int) $_POST['toggle_id'];

    mysqli_query(
        $conn,
        "UPDATE collection_diamond_products
         SET status = CASE
             WHEN status = 'active' THEN 'inactive'
             ELSE 'active'
         END
         WHERE id = $toggle_id"
    );

    header('Location: diamond-collection.php?success=status');
    exit;
}


/*======================================
            FETCH PRODUCTS
======================================*/

$diamond_query = mysqli_query(
    $conn,
    "SELECT *
     FROM collection_diamond_products
     ORDER BY display_order ASC, id ASC"
);


/*======================================
            STATISTICS
======================================*/

$total_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM collection_diamond_products"
);

$total_row = mysqli_fetch_assoc($total_query);

$total_products = (int) $total_row['total'];


$active_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM collection_diamond_products
     WHERE status = 'active'"
);

$active_row = mysqli_fetch_assoc($active_query);

$active_products = (int) $active_row['total'];


$inactive_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM collection_diamond_products
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
                Diamond Collection
            </h1>

            <p class="gg-page-subtitle">
                Manage products displayed in the Diamond Collection.
            </p>

        </div>

        <a
            href="add-diamond-collection.php"
            class="btn-gg-gold"
        >
            <i class="fa-solid fa-plus"></i>
            Add Diamond Product
        </a>

    </div>


    <!--======================================
                STATISTICS
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
            DIAMOND PRODUCTS TABLE
    ======================================-->

    <div class="gg-card">

        <div class="gg-card-header">

            <h2 class="gg-card-title">
                Diamond Collection Products
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

                        <?php if (mysqli_num_rows($diamond_query) > 0): ?>


                            <?php while ($product = mysqli_fetch_assoc($diamond_query)): ?>

                                <tr>


                                    <td>

                                        <?php
                                        echo (int) $product['id'];
                                        ?>

                                    </td>


                                    <td>

                                        <img
                                            src="../assets/images/diamond/<?php echo htmlspecialchars($product['image']); ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                                            style="
                                                width:72px;
                                                height:72px;
                                                object-fit:cover;
                                                border-radius:10px;
                                                border:1px solid rgba(200,161,101,0.25);
                                            "
                                        >

                                    </td>


                                    <td>

                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $product['name']
                                            );
                                            ?>
                                        </strong>

                                    </td>


                                    <td>

                                        ₹<?php
                                        echo indianPrice(
                                            $product['price']
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo (int) $product['display_order'];
                                        ?>

                                    </td>


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


                                    <td>

                                        <div
                                            style="
                                                display:flex;
                                                gap:8px;
                                                align-items:center;
                                                flex-wrap:wrap;
                                            "
                                        >


                                            <a
                                                href="edit-diamond-collection.php?id=<?php echo (int) $product['id']; ?>"
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
                                                onsubmit="return confirm('Are you sure you want to delete this Diamond Collection product?');"
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
                                    style="
                                        text-align:center;
                                        padding:40px;
                                    "
                                >

                                    No Diamond Collection products found.

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