<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Instagram Reels';


//======================================
//          DELETE REEL
//======================================

if (isset($_GET['delete'])) {

    $delete_id = (int)$_GET['delete'];

    if ($delete_id > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM instagram_reels WHERE id = ?"
        );

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "i", $delete_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

        }
    }

    header("Location: instagram-reels.php");
    exit;
}


//======================================
//          TOGGLE STATUS
//======================================

if (isset($_GET['toggle'])) {

    $toggle_id = (int)$_GET['toggle'];

    if ($toggle_id > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE instagram_reels
             SET status = CASE
                 WHEN status = 'active' THEN 'inactive'
                 ELSE 'active'
             END
             WHERE id = ?"
        );

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "i", $toggle_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

        }
    }

    header("Location: instagram-reels.php");
    exit;
}


//======================================
//          FETCH REELS
//======================================

$reels_query = mysqli_query(
    $conn,
    "SELECT *
     FROM instagram_reels
     ORDER BY display_order ASC, id ASC"
);


//======================================
//          SUMMARY COUNTS
//======================================

$total_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM instagram_reels"
);

$total_row = mysqli_fetch_assoc($total_result);
$total_reels = (int)$total_row['total'];


$active_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM instagram_reels
     WHERE status = 'active'"
);

$active_row = mysqli_fetch_assoc($active_result);
$active_reels = (int)$active_row['total'];


$inactive_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM instagram_reels
     WHERE status = 'inactive'"
);

$inactive_row = mysqli_fetch_assoc($inactive_result);
$inactive_reels = (int)$inactive_row['total'];


include(__DIR__ . '/includes/header.php');

?>

<!--======================================
            INSTAGRAM REELS
======================================-->

<div class="admin-page">


    <!--======================================
                PAGE HEADER
    ======================================-->

    <div class="page-header">

        <div>

            <h1 class="page-title">
                Instagram Reels
            </h1>

            <p class="page-subtitle">
                Manage Instagram reels displayed on the website.
            </p>

        </div>


        <div>

            <a
                href="add-instagram-reel.php"
                class="btn-gg-gold"
            >
                <i class="fa-solid fa-plus"></i>
                Add New Reel
            </a>

        </div>

    </div>


    <!--======================================
                SUMMARY CARDS
    ======================================-->

    <div class="stats-grid">


        <!-- TOTAL REELS -->

        <div class="stat-card">

            <div class="stat-icon">
                <i class="fa-brands fa-instagram"></i>
            </div>

            <div class="stat-content">

                <span class="stat-label">
                    Total Reels
                </span>

                <h3>
                    <?php echo $total_reels; ?>
                </h3>

            </div>

        </div>


        <!-- ACTIVE REELS -->

        <div class="stat-card">

            <div class="stat-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <div class="stat-content">

                <span class="stat-label">
                    Active Reels
                </span>

                <h3>
                    <?php echo $active_reels; ?>
                </h3>

            </div>

        </div>


        <!-- INACTIVE REELS -->

        <div class="stat-card">

            <div class="stat-icon">
                <i class="fa-solid fa-eye-slash"></i>
            </div>

            <div class="stat-content">

                <span class="stat-label">
                    Inactive Reels
                </span>

                <h3>
                    <?php echo $inactive_reels; ?>
                </h3>

            </div>

        </div>


    </div>


    <!--======================================
                REELS TABLE
    ======================================-->

    <div class="gg-card">


        <!--======================================
                CARD HEADER
        ======================================-->

        <div class="gg-card-header">

            <div>

                <h2 class="gg-card-title">
                    All Instagram Reels
                </h2>

                <p class="gg-card-subtitle">
                    Manage current and future Instagram reels.
                </p>

            </div>

        </div>


        <!--======================================
                TABLE
        ======================================-->

        <div class="table-responsive">

            <table class="table-gg">


                <!--======================================
                        TABLE HEADER
                ======================================-->

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Preview</th>

                        <th>Title</th>

                        <th>Reel Source</th>

                        <th>Order</th>

                        <th>Status</th>

                        <th>Created</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <!--======================================
                        TABLE BODY
                ======================================-->

                <tbody>


                    <?php if ($reels_query && mysqli_num_rows($reels_query) > 0): ?>


                        <?php

                        $count = 1;

                        while ($reel = mysqli_fetch_assoc($reels_query)):

                        ?>


                            <tr>


                                <!-- NUMBER -->

                                <td>

                                    <?php echo $count++; ?>

                                </td>


                                <!--======================================
                                        REEL PREVIEW
                                ======================================-->

                                <td>


                                    <?php if (!empty($reel['video_file'])): ?>


                                        <video
                                            width="70"
                                            height="100"
                                            controls
                                            muted
                                            preload="metadata"
                                            style="
                                                width:70px;
                                                height:100px;
                                                object-fit:cover;
                                                border-radius:8px;
                                                border:1px solid #3c443d;
                                                background:#000;
                                            "
                                        >

                                            <source
                                                src="../assets/videos/<?php echo htmlspecialchars($reel['video_file']); ?>"
                                                type="video/mp4"
                                            >

                                            Your browser does not support video.

                                        </video>


                                    <?php elseif (!empty($reel['thumbnail'])): ?>


                                        <img
                                            src="../uploads/reels/<?php echo htmlspecialchars($reel['thumbnail']); ?>"
                                            alt="<?php echo htmlspecialchars($reel['title']); ?>"
                                            style="
                                                width:70px;
                                                height:100px;
                                                object-fit:cover;
                                                border-radius:8px;
                                                border:1px solid #3c443d;
                                            "
                                        >


                                    <?php else: ?>


                                        <div
                                            style="
                                                width:70px;
                                                height:100px;
                                                display:flex;
                                                align-items:center;
                                                justify-content:center;
                                                background:#171e1a;
                                                border-radius:8px;
                                                color:#c8a165;
                                            "
                                        >

                                            <i class="fa-brands fa-instagram"></i>

                                        </div>


                                    <?php endif; ?>


                                </td>


                                <!--======================================
                                        TITLE
                                ======================================-->

                                <td>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $reel['title']
                                        );
                                        ?>

                                    </strong>

                                </td>


                                <!--======================================
                                        REEL SOURCE
                                ======================================-->

                                <td>


                                    <?php if (!empty($reel['reel_url'])): ?>


                                        <a
                                            href="<?php echo htmlspecialchars($reel['reel_url']); ?>"
                                            target="_blank"
                                            style="
                                                color:#c8a165;
                                                text-decoration:none;
                                            "
                                        >

                                            Instagram

                                            <i
                                                class="fa-solid fa-arrow-up-right-from-square"
                                            ></i>

                                        </a>


                                    <?php elseif (!empty($reel['video_file'])): ?>


                                        <span style="color:#aaa;">

                                            Local Video

                                        </span>


                                    <?php else: ?>


                                        <span style="color:#777;">

                                            —

                                        </span>


                                    <?php endif; ?>


                                </td>


                                <!--======================================
                                        DISPLAY ORDER
                                ======================================-->

                                <td>

                                    <?php
                                    echo (int)$reel['display_order'];
                                    ?>

                                </td>


                                <!--======================================
                                        STATUS
                                ======================================-->

                                <td>


                                    <?php if ($reel['status'] === 'active'): ?>


                                        <span class="badge-gg badge-success">

                                            Active

                                        </span>


                                    <?php else: ?>


                                        <span class="badge-gg badge-danger">

                                            Inactive

                                        </span>


                                    <?php endif; ?>


                                </td>


                                <!--======================================
                                        CREATED DATE
                                ======================================-->

                                <td>

                                    <?php

                                    echo date(
                                        'd M Y',
                                        strtotime(
                                            $reel['created_at']
                                        )
                                    );

                                    ?>

                                    <br>

                                    <small>

                                        <?php

                                        echo date(
                                            'h:i A',
                                            strtotime(
                                                $reel['created_at']
                                            )
                                        );

                                        ?>

                                    </small>

                                </td>


                                <!--======================================
                                        ACTIONS
                                ======================================-->

                                <td>


                                    <div
                                        style="
                                            display:flex;
                                            gap:8px;
                                            align-items:center;
                                        "
                                    >


                                        <!-- EDIT -->

                                        <a
                                            href="edit-instagram-reel.php?id=<?php echo (int)$reel['id']; ?>"
                                            class="btn-gg-small"
                                            title="Edit Reel"
                                        >

                                            <i class="fa-solid fa-pen"></i>

                                        </a>


                                        <!-- TOGGLE STATUS -->

                                        <a
                                            href="instagram-reels.php?toggle=<?php echo (int)$reel['id']; ?>"
                                            class="btn-gg-small"
                                            title="Toggle Status"
                                        >


                                            <?php if ($reel['status'] === 'active'): ?>


                                                <i
                                                    class="fa-solid fa-eye-slash"
                                                ></i>


                                            <?php else: ?>


                                                <i
                                                    class="fa-solid fa-eye"
                                                ></i>


                                            <?php endif; ?>


                                        </a>


                                        <!-- DELETE -->

                                        <a
                                            href="instagram-reels.php?delete=<?php echo (int)$reel['id']; ?>"
                                            class="btn-gg-small"
                                            title="Delete Reel"
                                            onclick="return confirm('Are you sure you want to delete this reel?');"
                                        >

                                            <i
                                                class="fa-solid fa-trash"
                                            ></i>

                                        </a>


                                    </div>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <!--======================================
                                EMPTY STATE
                        ======================================-->

                        <tr>

                            <td
                                colspan="8"
                                class="text-center"
                            >

                                <div class="empty-state">

                                    <i
                                        class="fa-brands fa-instagram"
                                    ></i>

                                    <h3>
                                        No Instagram Reels Found
                                    </h3>

                                    <p>
                                        Add your first Instagram reel to display it on the website.
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

include(__DIR__ . '/includes/footer.php');

?>