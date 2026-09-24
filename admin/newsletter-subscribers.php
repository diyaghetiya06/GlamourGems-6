<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Newsletter Subscribers';


//======================================
//          DELETE SUBSCRIBER
//======================================

if (
    isset($_GET['delete']) &&
    (int)$_GET['delete'] > 0
) {

    $delete_id = (int)$_GET['delete'];

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM newsletter_subscribers
         WHERE id = ?"
    );

    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $delete_id
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

    }

    header("Location: newsletter-subscribers.php");
    exit;
}


//======================================
//          TOGGLE STATUS
//======================================

if (
    isset($_GET['toggle']) &&
    (int)$_GET['toggle'] > 0
) {

    $toggle_id = (int)$_GET['toggle'];

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE newsletter_subscribers
         SET status =
            CASE
                WHEN status = 'active'
                THEN 'inactive'
                ELSE 'active'
            END
         WHERE id = ?"
    );

    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $toggle_id
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

    }

    header("Location: newsletter-subscribers.php");
    exit;
}


//======================================
//          SEARCH
//======================================

$search = trim($_GET['search'] ?? '');

$where_sql = '';

if ($search !== '') {

    $safe_search = mysqli_real_escape_string(
        $conn,
        $search
    );

    $where_sql =
        "WHERE email LIKE '%$safe_search%'";
}


//======================================
//          SUMMARY COUNTS
//======================================

$total_subscribers = 0;
$active_subscribers = 0;
$inactive_subscribers = 0;


$total_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM newsletter_subscribers"
);

if ($total_result) {

    $total_row = mysqli_fetch_assoc($total_result);

    $total_subscribers =
        (int)$total_row['total'];

}


$active_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM newsletter_subscribers
     WHERE status = 'active'"
);

if ($active_result) {

    $active_row = mysqli_fetch_assoc($active_result);

    $active_subscribers =
        (int)$active_row['total'];

}


$inactive_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM newsletter_subscribers
     WHERE status = 'inactive'"
);

if ($inactive_result) {

    $inactive_row = mysqli_fetch_assoc($inactive_result);

    $inactive_subscribers =
        (int)$inactive_row['total'];

}


//======================================
//          FETCH SUBSCRIBERS
//======================================

$subscriber_query = mysqli_query(
    $conn,
    "SELECT *
     FROM newsletter_subscribers
     $where_sql
     ORDER BY id DESC"
);


include(__DIR__ . '/includes/header.php');

?>

<!--======================================
        NEWSLETTER SUBSCRIBERS
======================================-->

<div class="admin-page">


    <!--======================================
                PAGE HEADER
    ======================================-->

    <div class="page-header">

        <div>

            <h1 class="page-title">
                Newsletter Subscribers
            </h1>

            <p class="page-subtitle">
                Manage customers subscribed to the Glamour Gems newsletter.
            </p>

        </div>

    </div>


    <!--======================================
                SUMMARY CARDS
    ======================================-->

    <div
        class="stats-grid"
        style="
            display:grid;
            grid-template-columns:repeat(3, 1fr);
            gap:20px;
            margin-bottom:25px;
        "
    >


        <!-- Total Subscribers -->

        <div class="gg-card">

            <div
                style="
                    padding:22px;
                    display:flex;
                    align-items:center;
                    justify-content:space-between;
                "
            >

                <div>

                    <div
                        style="
                            color:#999;
                            font-size:14px;
                            margin-bottom:6px;
                        "
                    >
                        Total Subscribers
                    </div>

                    <div
                        style="
                            color:#ffffff;
                            font-size:28px;
                            font-weight:600;
                        "
                    >
                        <?php echo $total_subscribers; ?>
                    </div>

                </div>


                <div
                    style="
                        width:48px;
                        height:48px;
                        border-radius:10px;
                        background:rgba(200,161,101,0.12);
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        color:#C8A165;
                        font-size:20px;
                    "
                >

                    <i class="fa-solid fa-users"></i>

                </div>

            </div>

        </div>


        <!-- Active Subscribers -->

        <div class="gg-card">

            <div
                style="
                    padding:22px;
                    display:flex;
                    align-items:center;
                    justify-content:space-between;
                "
            >

                <div>

                    <div
                        style="
                            color:#999;
                            font-size:14px;
                            margin-bottom:6px;
                        "
                    >
                        Active Subscribers
                    </div>

                    <div
                        style="
                            color:#ffffff;
                            font-size:28px;
                            font-weight:600;
                        "
                    >
                        <?php echo $active_subscribers; ?>
                    </div>

                </div>


                <div
                    style="
                        width:48px;
                        height:48px;
                        border-radius:10px;
                        background:rgba(46,204,113,0.12);
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        color:#2ecc71;
                        font-size:20px;
                    "
                >

                    <i class="fa-solid fa-circle-check"></i>

                </div>

            </div>

        </div>


        <!-- Inactive Subscribers -->

        <div class="gg-card">

            <div
                style="
                    padding:22px;
                    display:flex;
                    align-items:center;
                    justify-content:space-between;
                "
            >

                <div>

                    <div
                        style="
                            color:#999;
                            font-size:14px;
                            margin-bottom:6px;
                        "
                    >
                        Inactive Subscribers
                    </div>

                    <div
                        style="
                            color:#ffffff;
                            font-size:28px;
                            font-weight:600;
                        "
                    >
                        <?php echo $inactive_subscribers; ?>
                    </div>

                </div>


                <div
                    style="
                        width:48px;
                        height:48px;
                        border-radius:10px;
                        background:rgba(231,76,60,0.12);
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        color:#e74c3c;
                        font-size:20px;
                    "
                >

                    <i class="fa-solid fa-user-slash"></i>

                </div>

            </div>

        </div>

    </div>


    <!--======================================
                SEARCH
    ======================================-->

    <div
        class="gg-card"
        style="margin-bottom:25px;"
    >

        <div style="padding:20px;">

            <form
                method="GET"
                style="
                    display:flex;
                    gap:12px;
                    align-items:center;
                "
            >

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search subscriber email..."
                    value="<?php echo htmlspecialchars($search); ?>"
                    style="max-width:400px;"
                >


                <button
                    type="submit"
                    class="btn-gg-gold"
                >

                    <i class="fa-solid fa-magnifying-glass"></i>

                    Search

                </button>


                <?php if ($search !== ''): ?>

                    <a
                        href="newsletter-subscribers.php"
                        class="btn-gg-secondary"
                    >

                        Clear

                    </a>

                <?php endif; ?>

            </form>

        </div>

    </div>


    <!--======================================
                SUBSCRIBERS TABLE
    ======================================-->

    <div class="gg-card">

        <div class="gg-card-header">

            <div>

                <h2 class="gg-card-title">
                    Subscriber List
                </h2>

                <p class="gg-card-subtitle">
                    All newsletter subscribers.
                </p>

            </div>

        </div>


        <div style="overflow-x:auto;">

            <table class="table-gg">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Email</th>

                        <th>Status</th>

                        <th>Subscribed On</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (
                        $subscriber_query &&
                        mysqli_num_rows($subscriber_query) > 0
                    ): ?>

                        <?php
                        $serial = 1;
                        ?>

                        <?php while (
                            $subscriber =
                            mysqli_fetch_assoc($subscriber_query)
                        ): ?>

                            <tr>


                                <!-- Number -->

                                <td>

                                    <?php echo $serial++; ?>

                                </td>


                                <!-- Email -->

                                <td>

                                    <div
                                        style="
                                            display:flex;
                                            align-items:center;
                                            gap:10px;
                                        "
                                    >

                                        <div
                                            style="
                                                width:36px;
                                                height:36px;
                                                border-radius:50%;
                                                background:rgba(200,161,101,0.12);
                                                color:#C8A165;
                                                display:flex;
                                                align-items:center;
                                                justify-content:center;
                                            "
                                        >

                                            <i class="fa-solid fa-envelope"></i>

                                        </div>


                                        <span>

                                            <?php

                                            echo htmlspecialchars(
                                                $subscriber['email']
                                            );

                                            ?>

                                        </span>

                                    </div>

                                </td>


                                <!-- Status -->

                                <td>

                                    <?php if (
                                        $subscriber['status'] === 'active'
                                    ): ?>

                                        <span
                                            class="badge-gg"
                                            style="
                                                background:rgba(46,204,113,0.12);
                                                color:#2ecc71;
                                            "
                                        >

                                            Active

                                        </span>

                                    <?php else: ?>

                                        <span
                                            class="badge-gg"
                                            style="
                                                background:rgba(231,76,60,0.12);
                                                color:#e74c3c;
                                            "
                                        >

                                            Inactive

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- Date -->

                                <td>

                                    <?php

                                    echo date(
                                        'd M Y, h:i A',
                                        strtotime(
                                            $subscriber['created_at']
                                        )
                                    );

                                    ?>

                                </td>


                                <!-- Actions -->

                                <td>

                                    <div
                                        style="
                                            display:flex;
                                            gap:8px;
                                        "
                                    >


                                        <!-- Toggle -->

                                        <a
                                            href="?toggle=<?php echo (int)$subscriber['id']; ?>"
                                            class="btn-gg-secondary"
                                            title="Toggle Status"
                                        >

                                            <i
                                                class="fa-solid
                                                <?php

                                                echo (
                                                    $subscriber['status'] === 'active'
                                                )
                                                    ? 'fa-toggle-on'
                                                    : 'fa-toggle-off';

                                                ?>"
                                            ></i>

                                        </a>


                                        <!-- Delete -->

                                        <a
                                            href="?delete=<?php echo (int)$subscriber['id']; ?>"
                                            class="btn-gg-secondary"
                                            title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this subscriber?');"
                                            style="color:#e74c3c;"
                                        >

                                            <i class="fa-solid fa-trash"></i>

                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="5"
                                style="
                                    text-align:center;
                                    padding:40px;
                                    color:#999;
                                "
                            >

                                <i
                                    class="fa-solid fa-envelope-open-text"
                                    style="
                                        font-size:32px;
                                        margin-bottom:12px;
                                    "
                                ></i>

                                <div>
                                    No newsletter subscribers found.
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

include(__DIR__ . '/includes/footer.php');

?>