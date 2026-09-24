<?php

session_start();

require_once "config/database.php";
require_once "config/config.php";


/*======================================
            CHECK CUSTOMER LOGIN
======================================*/

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit;

}


/*======================================
            VARIABLES
======================================*/

$error = "";

$user_name = $_SESSION['user_name'] ?? "";
$user_email = $_SESSION['user_email'] ?? "";


/*======================================
            SUBMIT REVIEW
======================================*/

if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['submit_review'])) {


    /*======================================
            GET FORM DATA
    ======================================*/

    $customer_name = trim(
        $_POST['customer_name'] ?? ""
    );

    $location = trim(
        $_POST['location'] ?? ""
    );

    $review = trim(
        $_POST['review'] ?? ""
    );

    $rating = (int) (
        $_POST['rating'] ?? 5
    );


    /*======================================
            VALIDATION
    ======================================*/

    if ($customer_name === "") {

        $error = "Please enter your name.";

    } elseif ($review === "") {

        $error = "Please enter your review.";

    } elseif ($rating < 1 || $rating > 5) {

        $error = "Please select a valid rating.";

    }


    /*======================================
            IMAGE UPLOAD
    ======================================*/

    $image_name = null;


    if ($error === ""
        && isset($_FILES['image'])
        && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {


        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

            $error = "There was a problem uploading your image.";

        } else {


            $allowed_extensions = [
                'jpg',
                'jpeg',
                'png',
                'webp'
            ];


            $original_name =
                $_FILES['image']['name'];

            $extension =
                strtolower(
                    pathinfo(
                        $original_name,
                        PATHINFO_EXTENSION
                    )
                );


            if (!in_array(
                $extension,
                $allowed_extensions,
                true
            )) {

                $error =
                    "Only JPG, JPEG, PNG and WEBP images are allowed.";

            } else {


                /*======================================
                        CREATE REVIEW UPLOAD FOLDER
                ======================================*/

                $upload_directory =
                    __DIR__ . "/uploads/reviews/";


                if (!is_dir($upload_directory)) {

                    mkdir(
                        $upload_directory,
                        0777,
                        true
                    );

                }


                /*======================================
                        CREATE UNIQUE IMAGE NAME
                ======================================*/

                $image_name =
                    "review_" .
                    time() .
                    "_" .
                    bin2hex(
                        random_bytes(4)
                    ) .
                    "." .
                    $extension;


                $image_path =
                    $upload_directory .
                    $image_name;


                /*======================================
                        MOVE IMAGE
                ======================================*/

                if (!move_uploaded_file(
                    $_FILES['image']['tmp_name'],
                    $image_path
                )) {

                    $error =
                        "Unable to upload the image.";

                    $image_name = null;

                }

            }

        }

    }


    /*======================================
            INSERT REVIEW
    ======================================*/

    if ($error === "") {


        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO customer_reviews
            (
                customer_name,
                location,
                review,
                rating,
                image,
                status
            )
            VALUES (?, ?, ?, ?, ?, 'active')"
        );


        mysqli_stmt_bind_param(
            $stmt,
            "sssds",
            $customer_name,
            $location,
            $review,
            $rating,
            $image_name
        );


        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);


            /*======================================
                    REDIRECT TO HOME PAGE
            ======================================*/

            header("Location: index.php");
            exit;

        } else {

            $error =
                "Unable to submit your review. Please try again.";

            mysqli_stmt_close($stmt);

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Write a Review | Glamour Gems</title>


    <!--======================================
                GOOGLE FONTS
    ======================================-->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet"
    >


    <!--======================================
                FONT AWESOME
    ======================================-->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >


    <!--======================================
                REVIEW CSS
    ======================================-->

    <link
        rel="stylesheet"
        href="assets/css/review.css"
    >

</head>


<body>


<!--======================================
                REVIEW PAGE
======================================-->

<section class="review-page">

    <div class="review-container">


        <!--======================================
                    REVIEW HEADER
        ======================================-->

        <div class="review-header">

            <span>GLAMOUR GEMS</span>

            <h1>Share Your Experience</h1>

            <p>
                We would love to hear about your experience with Glamour Gems.
            </p>

        </div>


        <!--======================================
                    ERROR MESSAGE
        ======================================-->

        <?php if ($error !== ""): ?>

            <div
                style="
                    background:#fff0f0;
                    color:#c0392b;
                    border:1px solid #f0caca;
                    padding:12px 15px;
                    border-radius:6px;
                    margin-bottom:20px;
                    font-size:13px;
                "
            >

                <i class="fa-solid fa-circle-exclamation"></i>

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <!--======================================
                    REVIEW FORM
        ======================================-->

        <div class="review-form-card">

            <form
                action=""
                method="POST"
                enctype="multipart/form-data"
            >


                <!--======================================
                        CUSTOMER NAME
                ======================================-->

                <div class="form-group">

                    <label for="customer_name">
                        Your Name
                    </label>

                    <input
                        type="text"
                        id="customer_name"
                        name="customer_name"
                        placeholder="Enter your name"
                        value="<?php
                            echo htmlspecialchars(
                                $user_name
                            );
                        ?>"
                        required
                    >

                </div>


                <!--======================================
                        LOCATION
                ======================================-->

                <div class="form-group">

                    <label for="location">
                        Location
                    </label>

                    <input
                        type="text"
                        id="location"
                        name="location"
                        placeholder="e.g. Rajkot"
                    >

                </div>


                <!--======================================
                        RATING
                ======================================-->

                <div class="form-group">

                    <label>
                        Your Rating
                    </label>

                    <div class="rating-stars">


                        <input
                            type="radio"
                            name="rating"
                            value="5"
                            id="star5"
                            checked
                        >

                        <label for="star5">★</label>


                        <input
                            type="radio"
                            name="rating"
                            value="4"
                            id="star4"
                        >

                        <label for="star4">★</label>


                        <input
                            type="radio"
                            name="rating"
                            value="3"
                            id="star3"
                        >

                        <label for="star3">★</label>


                        <input
                            type="radio"
                            name="rating"
                            value="2"
                            id="star2"
                        >

                        <label for="star2">★</label>


                        <input
                            type="radio"
                            name="rating"
                            value="1"
                            id="star1"
                        >

                        <label for="star1">★</label>


                    </div>

                </div>


                <!--======================================
                        CUSTOMER REVIEW
                ======================================-->

                <div class="form-group">

                    <label for="review">
                        Your Review
                    </label>

                    <textarea
                        id="review"
                        name="review"
                        rows="6"
                        placeholder="Tell us about your experience..."
                        required
                    ></textarea>

                </div>


                <!--======================================
                        CUSTOMER IMAGE
                ======================================-->

                <div class="form-group">

                    <label for="image">

                        Your Photo

                        <span>
                            (Optional)
                        </span>

                    </label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                </div>


                <!--======================================
                        SUBMIT BUTTON
                ======================================-->

                <button
                    type="submit"
                    name="submit_review"
                    class="submit-review-btn"
                >

                    <i class="fa-solid fa-paper-plane"></i>

                    Submit Review

                </button>


            </form>

        </div>


        <!--======================================
                    BACK TO HOME
        ======================================-->

        <a
            href="index.php"
            class="back-home"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back to Home

        </a>


    </div>

</section>


</body>

</html>