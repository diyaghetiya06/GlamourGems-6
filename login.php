<?php

session_start();

require_once 'config/database.php';

$error = "";


/*======================================
              LOGIN
======================================*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $identifier = trim(
        $_POST['identifier']
        ?? $_POST['email']
        ?? $_POST['username']
        ?? ''
    );
    $password = $_POST['password'] ?? '';

    if ($identifier === "" || $password === "") {

        $error = "Please enter your email/name and password.";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, name, email, password
             FROM users
             WHERE (LOWER(email) = LOWER(?) OR LOWER(name) = LOWER(?))
             AND status = 'active'
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "ss", $identifier, $identifier);
        mysqli_stmt_execute($stmt);

        mysqli_stmt_bind_result(
            $stmt,
            $user_id,
            $user_name,
            $user_email,
            $user_password
        );

        if (mysqli_stmt_fetch($stmt)) {

            if (password_verify($password, $user_password)) {

                session_regenerate_id(true);
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $user_name;
                $_SESSION['user_email'] = $user_email;

                mysqli_stmt_close($stmt);

               header("Location: index.php");
                exit;

            } else {

                $error = "Invalid email or password.";

            }

        } else {

            $error = "Invalid email or password.";

        }

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login | Glamour Gems</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Poppins, Arial, sans-serif;
            background: #f9f7f2;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 420px;
            max-width: 90%;
            background: #ffffff;
            padding: 45px;
            border-radius: 14px;
            box-shadow: 0 15px 45px rgba(0,0,0,0.10);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo h1 {
            font-family: Cinzel, Georgia, serif;
            color: #234B43;
            font-size: 30px;
            letter-spacing: 1px;
        }

        .login-logo p {
            color: #777;
            font-size: 14px;
            margin-top: 6px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 13px 15px;
            border: 1px solid #ddd;
            border-radius: 7px;
            outline: none;
            font-size: 14px;
        }

        .form-group input:focus {
            border-color: #C8A165;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 7px;
            background: #234B43;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        .login-btn:hover {
            background: #1b3b35;
        }

        .error-message {
            background: #ffe8e8;
            color: #b42318;
            padding: 11px 13px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .success-message {
            background: #e8f5ed;
            color: #21633d;
            padding: 11px 13px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        /*======================================
        REGISTER OPTION
======================================*/

.register-option {
    text-align: center;
    margin-top: 25px;
    padding-top: 22px;
    border-top: 1px solid #eee;
}

.register-option p {
    color: #777;
    font-size: 13px;
    margin-bottom: 8px;
}

.register-option a {
    display: inline-block;
    color: #234B43;
    background: #f5f1e9;
    border: 1px solid #C8A165;
    padding: 9px 22px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.3px;
    transition: 0.3s ease;
}

.register-option a:hover {
    background: #234B43;
    color: #ffffff;
    border-color: #234B43;
    transform: translateY(-1px);
}

        .back-shop {
            text-align: center;
            margin-top: 22px;
        }

        .back-shop a {
            color: #234B43;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

    </style>

</head>

<body>

<!--======================================
              LOGIN PAGE
======================================-->

<div class="login-container">

    <div class="login-logo">

        <h1>Glamour Gems</h1>

        <p>Welcome back to luxury</p>

    </div>


    <?php if ($error !== ""): ?>

        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>

    <?php if (isset($_GET['registered'])): ?>
        <div class="success-message">
            Account created successfully. Login with your registered email or name.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['switch'])): ?>
        <div class="success-message">
            Enter your customer account details to continue.
        </div>
    <?php endif; ?>


    <form method="POST">

        <div class="form-group">

            <label>Email or Name</label>

            <input
                type="text"
                name="identifier"
                placeholder="Enter your email or name"
                required
            >

        </div>


        <div class="form-group">

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Enter your password"
                required
            >

        </div>


        <button type="submit" class="login-btn">
            Login
        </button>

    </form>
       
         <!--======================================
        NEW CUSTOMER REGISTER
======================================-->

<div class="register-option">

    <p>Don't have an account?</p>

    <a href="register.php">
        Create New Account
    </a>

</div>
        

    <div class="back-shop">

        <a href="shop.php">
            <i class="fa-solid fa-arrow-left"></i>
            Continue Shopping
        </a>

    </div>

</div>

</body>

</html>