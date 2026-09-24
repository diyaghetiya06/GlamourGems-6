<?php

require_once 'config/database.php';

$error = "";
$success = "";


/*======================================
              CUSTOMER REGISTER
======================================*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    /*======================================
            BASIC VALIDATION
    ======================================*/

    if ($name === "" || $email === "" || $password === "") {

        $error = "Please fill all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";

    } else {

        /*======================================
                CHECK EXISTING EMAIL
        ======================================*/

        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $check,
            "s",
            $email
        );

        mysqli_stmt_execute($check);

        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {

            $error = "This email is already registered.";

            mysqli_stmt_close($check);

        } else {

            mysqli_stmt_close($check);


            /*======================================
                    CREATE PASSWORD HASH
            ======================================*/

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            /*======================================
                    CREATE CUSTOMER ACCOUNT
            ======================================*/

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users
                (name, email, phone, password, status)
                VALUES (?, ?, ?, ?, 'active')"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $name,
                $email,
                $phone,
                $hashed_password
            );


            /*======================================
                    SAVE CUSTOMER
            ======================================*/

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: login.php?registered=1");
                exit;

            } else {

                $error = "Registration failed. Please try again.";

                mysqli_stmt_close($stmt);
            }
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

    <title>Create Account | Glamour Gems</title>


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


    <style>

        /*======================================
                    RESET
        ======================================*/

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        /*======================================
                    BODY
        ======================================*/

        body {

            font-family: "Poppins", Arial, sans-serif;

            background: #f9f7f2;

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 30px 15px;
        }


        /*======================================
                REGISTER CONTAINER
        ======================================*/

        .register-container {

            width: 100%;

            max-width: 470px;

            background: #ffffff;

            padding: 42px;

            border-radius: 15px;

            border: 1px solid #e8e2d7;

            box-shadow:
                0 15px 45px rgba(0, 0, 0, 0.09);
        }


        /*======================================
                    LOGO
        ======================================*/

        .register-logo {

            text-align: center;

            margin-bottom: 30px;
        }

        .register-logo h1 {

            font-family: "Cinzel", Georgia, serif;

            color: #234B43;

            font-size: 30px;

            font-weight: 600;

            letter-spacing: 1px;
        }

        .register-logo p {

            color: #777;

            font-size: 14px;

            margin-top: 7px;
        }


        /*======================================
                    ERROR MESSAGE
        ======================================*/

        .error-message {

            background: #fff0ef;

            color: #b42318;

            border: 1px solid #f2c7c4;

            padding: 11px 13px;

            border-radius: 7px;

            margin-bottom: 20px;

            font-size: 13px;

            line-height: 1.5;
        }


        /*======================================
                    FORM GROUP
        ======================================*/

        .form-group {

            margin-bottom: 19px;
        }


        /*======================================
                    LABEL
        ======================================*/

        .form-group label {

            display: block;

            margin-bottom: 8px;

            color: #333;

            font-size: 14px;

            font-weight: 500;
        }


        /*======================================
                    INPUT
        ======================================*/

        .form-group input {

            width: 100%;

            padding: 13px 15px;

            background: #ffffff;

            border: 1px solid #dcd8d0;

            border-radius: 7px;

            outline: none;

            color: #333;

            font-family: "Poppins", sans-serif;

            font-size: 14px;

            transition: 0.3s;
        }


        .form-group input::placeholder {

            color: #999;
        }


        .form-group input:focus {

            border-color: #C8A165;

            box-shadow:
                0 0 0 3px rgba(200, 161, 101, 0.10);
        }


        /*======================================
                PASSWORD HINT
        ======================================*/

        .password-hint {

            display: block;

            margin-top: 6px;

            color: #888;

            font-size: 11px;
        }


        /*======================================
                REGISTER BUTTON
        ======================================*/

        .register-btn {

            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 7px;

            background: #234B43;

            color: #ffffff;

            font-family: "Poppins", sans-serif;

            font-size: 15px;

            font-weight: 600;

            cursor: pointer;

            transition: 0.3s;
        }


        .register-btn:hover {

            background: #1b3b35;

            transform: translateY(-1px);
        }


        /*======================================
                LOGIN LINK
        ======================================*/

        .login-link {

            text-align: center;

            margin-top: 22px;

            font-size: 14px;

            color: #666;
        }


        .login-link a {

            color: #234B43;

            text-decoration: none;

            font-weight: 600;
        }


        .login-link a:hover {

            color: #C8A165;
        }


        /*======================================
                CONTINUE SHOPPING
        ======================================*/

        .back-shop {

            text-align: center;

            margin-top: 16px;
        }


        .back-shop a {

            color: #777;

            text-decoration: none;

            font-size: 13px;

            transition: 0.3s;
        }


        .back-shop a:hover {

            color: #234B43;
        }


        /*======================================
                RESPONSIVE
        ======================================*/

        @media (max-width: 500px) {

            .register-container {

                padding: 30px 22px;
            }

            .register-logo h1 {

                font-size: 26px;
            }

        }

    </style>

</head>


<body>


<!--======================================
            REGISTER PAGE
======================================-->

<div class="register-container">


    <!--======================================
                LOGO
    ======================================-->

    <div class="register-logo">

        <h1>Glamour Gems</h1>

        <p>Create your account</p>

    </div>


    <!--======================================
                ERROR
    ======================================-->

    <?php if ($error !== ""): ?>

        <div class="error-message">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <!--======================================
                REGISTER FORM
    ======================================-->

    <form method="POST">


        <!--======================================
                    FULL NAME
        ======================================-->

        <div class="form-group">

            <label for="name">
                Full Name
            </label>

            <input
                type="text"
                id="name"
                name="name"
                placeholder="Enter your full name"
                autocomplete="name"
                required
            >

        </div>


        <!--======================================
                    EMAIL
        ======================================-->

        <div class="form-group">

            <label for="email">
                Email Address
            </label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email"
                autocomplete="email"
                required
            >

        </div>


        <!--======================================
                    PHONE
        ======================================-->

        <div class="form-group">

            <label for="phone">
                Phone Number
            </label>

            <input
                type="tel"
                id="phone"
                name="phone"
                placeholder="Enter your phone number"
                autocomplete="tel"
            >

        </div>


        <!--======================================
                    PASSWORD
        ======================================-->

        <div class="form-group">

            <label for="password">
                Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Create a password"
                minlength="6"
                autocomplete="new-password"
                required
            >

            <span class="password-hint">
                Password must be at least 6 characters.
            </span>

        </div>


        <!--======================================
                CREATE ACCOUNT BUTTON
        ======================================-->

        <button
            type="submit"
            class="register-btn"
        >

            <i class="fa-solid fa-user-plus"></i>

            Create Account

        </button>


    </form>


    <!--======================================
                LOGIN LINK
    ======================================-->

    <div class="login-link">

        Already have an account?

        <a href="login.php">
            Login
        </a>

    </div>


    <!--======================================
                BACK TO SHOP
    ======================================-->

    <div class="back-shop">

        <a href="shop.php">

            <i class="fa-solid fa-arrow-left"></i>

            Continue Shopping

        </a>

    </div>


</div>


</body>

</html>