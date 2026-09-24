<?php

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Add Instagram Reel';


//======================================
//          ADD NEW REEL
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

    // Instagram URL validation
    if ($reel_url !== '' && !filter_var($reel_url, FILTER_VALIDATE_URL)) {

        $errors[] = 'Please enter a valid Instagram URL.';

    }


    //======================================
    //          THUMBNAIL UPLOAD
    //======================================

    $thumbnail_name = '';

    if (
        isset($_FILES['thumbnail']) &&
        $_FILES['thumbnail']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['thumbnail']['error'] !== UPLOAD_ERR_OK) {

            $errors[] = 'There was an error uploading the thumbnail.';

        } else {

            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
                'webp'
            ];

            $file_name = $_FILES['thumbnail']['name'];
            $file_tmp = $_FILES['thumbnail']['tmp_name'];
            $file_size = (int)$_FILES['thumbnail']['size'];

            $extension = strtolower(
                pathinfo($file_name, PATHINFO_EXTENSION)
            );

            if (!in_array($extension, $allowed_extensions, true)) {

                $errors[] =
                    'Only JPG, JPEG, PNG and WEBP images are allowed.';

            }

            if ($file_size > 5 * 1024 * 1024) {

                $errors[] =
                    'Thumbnail size must be less than 5 MB.';

            }

            if (empty($errors)) {

                $thumbnail_name =
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
                    $thumbnail_name;

                if (
                    !move_uploaded_file(
                        $file_tmp,
                        $upload_path
                    )
                ) {

                    $errors[] =
                        'Unable to upload thumbnail.';

                    $thumbnail_name = '';

                }

            }

        }

    }


    //======================================
    //          VIDEO FILE UPLOAD
    //======================================

    $video_file_name = '';

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


            // Maximum 50 MB
            if ($video_size > 50 * 1024 * 1024) {

                $errors[] =
                    'Video size must be less than 50 MB.';

            }


            if (empty($errors)) {

                $video_file_name =
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
                    $video_file_name;


                if (
                    !move_uploaded_file(
                        $video_tmp,
                        $video_upload_path
                    )
                ) {

                    $errors[] =
                        'Unable to upload video.';

                    $video_file_name = '';

                }

            }

        }

    }


    //======================================
    //      URL OR VIDEO REQUIRED
    //======================================

    if (
        $reel_url === '' &&
        $video_file_name === ''
    ) {

        $errors[] =
            'Please enter an Instagram Reel URL or upload a video.';

    }


    //======================================
    //          INSERT REEL
    //======================================

    if (empty($errors)) {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO instagram_reels
            (
                title,
                reel_url,
                thumbnail,
                video_file,
                status,
                display_order
            )
            VALUES (?, ?, ?, ?, ?, ?)"
        );


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "sssssi",
                $title,
                $reel_url,
                $thumbnail_name,
                $video_file_name,
                $status,
                $display_order
            );


            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header(
                    "Location: instagram-reels.php"
                );

                exit;

            } else {

                $errors[] =
                    'Unable to save reel.';

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
            ADD INSTAGRAM REEL
======================================-->

<div class="admin-page">


    <!--======================================
                PAGE HEADER
    ======================================-->

    <div class="page-header">

        <div>

            <h1 class="page-title">
                Add Instagram Reel
            </h1>

            <p class="page-subtitle">
                Add a new Instagram reel or upload a local video.
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
                    Add an Instagram URL or upload a local video.
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
                    placeholder="Enter reel title"
                    value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
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
                    placeholder="https://www.instagram.com/reel/..."
                    value="<?php echo htmlspecialchars($_POST['reel_url'] ?? ''); ?>"
                >

                <small style="color:#999;">
                    Optional — use this when adding an Instagram Reel.
                </small>

            </div>


            <!--======================================
                    LOCAL VIDEO
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
                    Local Video
                </label>

                <input
                    type="file"
                    id="video_file"
                    name="video_file"
                    class="form-control"
                    accept=".mp4,.webm,.mov"
                >

                <small style="color:#999;">
                    Optional — MP4, WEBM or MOV. Maximum 50 MB.
                </small>

            </div>


            <!--======================================
                    THUMBNAIL
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
                    Reel Thumbnail
                </label>

                <input
                    type="file"
                    id="thumbnail"
                    name="thumbnail"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <small style="color:#999;">
                    Optional — JPG, JPEG, PNG or WEBP. Maximum 5 MB.
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
                    value="<?php echo htmlspecialchars($_POST['display_order'] ?? '0'); ?>"
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
                        <?php echo (($_POST['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>
                    >
                        Active
                    </option>

                    <option
                        value="inactive"
                        <?php echo (($_POST['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>
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
                    <i class="fa-solid fa-plus"></i>
                    Add Reel
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