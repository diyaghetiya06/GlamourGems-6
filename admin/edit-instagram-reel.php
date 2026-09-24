<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Edit Instagram Reel';


//======================================
//          GET REEL ID
//======================================

$reel_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($reel_id <= 0) {

    header("Location: instagram-reels.php");
    exit;

}


//======================================
//          FETCH REEL
//======================================

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM instagram_reels
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $reel_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$reel = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$reel) {

    header("Location: instagram-reels.php");
    exit;

}


//======================================
//          UPDATE REEL
//======================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $reel_url = trim($_POST['reel_url'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);

    $status = ($_POST['status'] ?? 'active') === 'inactive'
        ? 'inactive'
        : 'active';

    $errors = [];


    //======================================
    //          VALIDATION
    //======================================

    if ($title === '') {

        $errors[] = 'Please enter reel title.';

    }


    if (
        $reel_url !== '' &&
        !filter_var($reel_url, FILTER_VALIDATE_URL)
    ) {

        $errors[] =
            'Please enter a valid Instagram URL.';

    }


    //======================================
    //          CURRENT FILES
    //======================================

    $thumbnail_name =
        $reel['thumbnail'];

    $video_file_name =
        $reel['video_file'];


    //======================================
    //          NEW THUMBNAIL
    //======================================

    if (
        isset($_FILES['thumbnail']) &&
        $_FILES['thumbnail']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['thumbnail']['error'] !== UPLOAD_ERR_OK) {

            $errors[] =
                'There was an error uploading the thumbnail.';

        } else {

            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
                'webp'
            ];

            $file_name =
                $_FILES['thumbnail']['name'];

            $file_tmp =
                $_FILES['thumbnail']['tmp_name'];

            $file_size =
                (int)$_FILES['thumbnail']['size'];

            $extension =
                strtolower(
                    pathinfo(
                        $file_name,
                        PATHINFO_EXTENSION
                    )
                );


            if (
                !in_array(
                    $extension,
                    $allowed_extensions,
                    true
                )
            ) {

                $errors[] =
                    'Only JPG, JPEG, PNG and WEBP images are allowed.';

            }


            if ($file_size > 5 * 1024 * 1024) {

                $errors[] =
                    'Thumbnail size must be less than 5 MB.';

            }


            if (empty($errors)) {

                $new_thumbnail =
                    'reel_' .
                    time() .
                    '_' .
                    bin2hex(random_bytes(4)) .
                    '.' .
                    $extension;


                $upload_dir =
                    __DIR__ .
                    '/../uploads/reels/';


                if (!is_dir($upload_dir)) {

                    mkdir(
                        $upload_dir,
                        0777,
                        true
                    );

                }


                $upload_path =
                    $upload_dir .
                    $new_thumbnail;


                if (
                    move_uploaded_file(
                        $file_tmp,
                        $upload_path
                    )
                ) {

                    // Delete old thumbnail
                    if (!empty($reel['thumbnail'])) {

                        $old_file =
                            $upload_dir .
                            $reel['thumbnail'];

                        if (file_exists($old_file)) {

                            unlink($old_file);

                        }

                    }


                    $thumbnail_name =
                        $new_thumbnail;

                } else {

                    $errors[] =
                        'Unable to upload new thumbnail.';

                }

            }

        }

    }


    //======================================
    //          NEW VIDEO
    //======================================

    if (
        isset($_FILES['video_file']) &&
        $_FILES['video_file']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['video_file']['error'] !== UPLOAD_ERR_OK) {

            $errors[] =
                'There was an error uploading the video.';

        } else {

            $allowed_video_extensions = [
                'mp4',
                'webm',
                'mov'
            ];

            $video_original_name =
                $_FILES['video_file']['name'];

            $video_tmp =
                $_FILES['video_file']['tmp_name'];

            $video_size =
                (int)$_FILES['video_file']['size'];

            $video_extension =
                strtolower(
                    pathinfo(
                        $video_original_name,
                        PATHINFO_EXTENSION
                    )
                );


            if (
                !in_array(
                    $video_extension,
                    $allowed_video_extensions,
                    true
                )
            ) {

                $errors[] =
                    'Only MP4, WEBM and MOV videos are allowed.';

            }


            if ($video_size > 50 * 1024 * 1024) {

                $errors[] =
                    'Video size must be less than 50 MB.';

            }


            if (empty($errors)) {

                $new_video =
                    'reel_' .
                    time() .
                    '_' .
                    bin2hex(random_bytes(4)) .
                    '.' .
                    $video_extension;


                $video_upload_dir =
                    __DIR__ .
                    '/../assets/videos/';


                if (!is_dir($video_upload_dir)) {

                    mkdir(
                        $video_upload_dir,
                        0777,
                        true
                    );

                }


                $video_upload_path =
                    $video_upload_dir .
                    $new_video;


                if (
                    move_uploaded_file(
                        $video_tmp,
                        $video_upload_path
                    )
                ) {

                    // Delete old local video
                    if (!empty($reel['video_file'])) {

                        $old_video =
                            $video_upload_dir .
                            $reel['video_file'];

                        if (file_exists($old_video)) {

                            unlink($old_video);

                        }

                    }


                    $video_file_name =
                        $new_video;

                } else {

                    $errors[] =
                        'Unable to upload new video.';

                }

            }

        }

    }


    //======================================
    //          UPDATE DATABASE
    //======================================

    if (empty($errors)) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE instagram_reels
             SET
                title = ?,
                reel_url = ?,
                thumbnail = ?,
                video_file = ?,
                status = ?,
                display_order = ?
             WHERE id = ?"
        );


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "sssssii",
                $title,
                $reel_url,
                $thumbnail_name,
                $video_file_name,
                $status,
                $display_order,
                $reel_id
            );


            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header(
                    "Location: instagram-reels.php"
                );

                exit;

            } else {

                $errors[] =
                    'Unable to update reel.';

                mysqli_stmt_close($stmt);

            }

        } else {

            $errors[] =
                'Unable to prepare database query.';

        }

    }

}


include(__DIR__ . '/includes/header.php');

?>

<!--======================================
            EDIT INSTAGRAM REEL
======================================-->

<div class="admin-page">


    <!--======================================
                PAGE HEADER
    ======================================-->

    <div class="page-header">

        <div>

            <h1 class="page-title">
                Edit Instagram Reel
            </h1>

            <p class="page-subtitle">
                Update your Instagram reel or local video.
            </p>

        </div>


        <div>

            <a
                href="instagram-reels.php"
                class="btn-gg-secondary"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Back to Reels
            </a>

        </div>

    </div>


    <!--======================================
                ERROR MESSAGES
    ======================================-->

    <?php if (!empty($errors)): ?>

        <div
            style="
                background:#3a1717;
                border:1px solid #7d3030;
                color:#ff9b9b;
                padding:15px 18px;
                border-radius:8px;
                margin-bottom:20px;
            "
        >

            <?php foreach ($errors as $error): ?>

                <div>

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?php echo htmlspecialchars($error); ?>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>


    <!--======================================
                FORM CARD
    ======================================-->

    <div class="gg-card">

        <div class="gg-card-header">

            <div>

                <h2 class="gg-card-title">
                    Reel Information
                </h2>

                <p class="gg-card-subtitle">
                    Update the details of this reel.
                </p>

            </div>

        </div>


        <form
            method="POST"
            enctype="multipart/form-data"
            style="padding:25px;"
        >


            <!--======================================
                    REEL TITLE
            ======================================-->

            <div style="margin-bottom:20px;">

                <label
                    for="title"
                    style="
                        display:block;
                        margin-bottom:8px;
                        color:#ffffff;
                        font-weight:500;
                    "
                >
                    Reel Title
                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    class="form-control"
                    value="<?php echo htmlspecialchars($reel['title']); ?>"
                    required
                >

            </div>


            <!--======================================
                    INSTAGRAM URL
            ======================================-->

            <div style="margin-bottom:20px;">

                <label
                    for="reel_url"
                    style="
                        display:block;
                        margin-bottom:8px;
                        color:#ffffff;
                        font-weight:500;
                    "
                >
                    Instagram Reel URL
                </label>

                <input
                    type="url"
                    id="reel_url"
                    name="reel_url"
                    class="form-control"
                    value="<?php echo htmlspecialchars($reel['reel_url']); ?>"
                >

                <small style="color:#999;">
                    Optional — leave empty for a local video.
                </small>

            </div>


            <!--======================================
                    CURRENT VIDEO
            ======================================-->

            <?php if (!empty($reel['video_file'])): ?>

                <div style="margin-bottom:20px;">

                    <label
                        style="
                            display:block;
                            margin-bottom:8px;
                            color:#ffffff;
                            font-weight:500;
                        "
                    >
                        Current Local Video
                    </label>

                    <video
                        width="180"
                        height="220"
                        controls
                        muted
                        preload="metadata"
                        style="
                            display:block;
                            object-fit:cover;
                            border-radius:8px;
                            border:1px solid #3c443d;
                            margin-bottom:12px;
                            background:#111;
                        "
                    >

                        <source
                            src="../assets/videos/<?php echo htmlspecialchars($reel['video_file']); ?>"
                            type="video/mp4"
                        >

                        Your browser does not support video.

                    </video>

                    <small style="color:#999;">
                        <?php echo htmlspecialchars($reel['video_file']); ?>
                    </small>

                </div>

            <?php endif; ?>


            <!--======================================
                    NEW VIDEO
            ======================================-->

            <div style="margin-bottom:20px;">

                <label
                    for="video_file"
                    style="
                        display:block;
                        margin-bottom:8px;
                        color:#ffffff;
                        font-weight:500;
                    "
                >
                    Change Local Video
                </label>

                <input
                    type="file"
                    id="video_file"
                    name="video_file"
                    class="form-control"
                    accept=".mp4,.webm,.mov"
                >

                <small style="color:#999;">
                    Leave empty to keep the current video. MP4, WEBM or MOV — Maximum 50 MB.
                </small>

            </div>


            <!--======================================
                    CURRENT THUMBNAIL
            ======================================-->

            <div style="margin-bottom:20px;">

                <label
                    style="
                        display:block;
                        margin-bottom:8px;
                        color:#ffffff;
                        font-weight:500;
                    "
                >
                    Current Thumbnail
                </label>


                <?php if (!empty($reel['thumbnail'])): ?>

                    <img
                        src="../uploads/reels/<?php echo htmlspecialchars($reel['thumbnail']); ?>"
                        alt="<?php echo htmlspecialchars($reel['title']); ?>"
                        style="
                            width:90px;
                            height:110px;
                            object-fit:cover;
                            border-radius:8px;
                            border:1px solid #3c443d;
                            margin-bottom:12px;
                        "
                    >

                <?php else: ?>

                    <p style="color:#999;">
                        No thumbnail uploaded.
                    </p>

                <?php endif; ?>

            </div>


            <!--======================================
                    NEW THUMBNAIL
            ======================================-->

            <div style="margin-bottom:20px;">

                <label
                    for="thumbnail"
                    style="
                        display:block;
                        margin-bottom:8px;
                        color:#ffffff;
                        font-weight:500;
                    "
                >
                    Change Thumbnail
                </label>

                <input
                    type="file"
                    id="thumbnail"
                    name="thumbnail"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <small style="color:#999;">
                    Leave empty to keep the current thumbnail.
                </small>

            </div>


            <!--======================================
                    DISPLAY ORDER
            ======================================-->

            <div style="margin-bottom:20px;">

                <label
                    for="display_order"
                    style="
                        display:block;
                        margin-bottom:8px;
                        color:#ffffff;
                        font-weight:500;
                    "
                >
                    Display Order
                </label>

                <input
                    type="number"
                    id="display_order"
                    name="display_order"
                    class="form-control"
                    value="<?php echo (int)$reel['display_order']; ?>"
                    min="0"
                >

                <small style="color:#999;">
                    Lower numbers appear first.
                </small>

            </div>


            <!--======================================
                    STATUS
            ======================================-->

            <div style="margin-bottom:25px;">

                <label
                    for="status"
                    style="
                        display:block;
                        margin-bottom:8px;
                        color:#ffffff;
                        font-weight:500;
                    "
                >
                    Status
                </label>

                <select
                    id="status"
                    name="status"
                    class="form-control"
                >

                    <option
                        value="active"
                        <?php echo ($reel['status'] === 'active') ? 'selected' : ''; ?>
                    >
                        Active
                    </option>

                    <option
                        value="inactive"
                        <?php echo ($reel['status'] === 'inactive') ? 'selected' : ''; ?>
                    >
                        Inactive
                    </option>

                </select>

            </div>


            <!--======================================
                    FORM BUTTONS
            ======================================-->

            <div
                style="
                    display:flex;
                    gap:12px;
                    align-items:center;
                "
            >

                <button
                    type="submit"
                    class="btn-gg-gold"
                >
                    <i class="fa-solid fa-floppy-disk"></i>
                    Update Reel
                </button>


                <a
                    href="instagram-reels.php"
                    class="btn-gg-secondary"
                >
                    Cancel
                </a>

            </div>


        </form>

    </div>

</div>


<?php

include(__DIR__ . '/includes/footer.php');

?>