<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../config/config.php";
require_once "includes/functions.php";

/*======================================
            ADMIN LOGIN
======================================*/

$error = "";

if (isset($_POST['login'])) {

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, email, password, role 
         FROM admins 
         WHERE email = ? 
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($admin = mysqli_fetch_assoc($result)) {

        if (password_verify($password, $admin['password'])) {

            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_role'] = $admin['role'];

            header("Location: " . ADMIN_URL . "dashboard.php");
            exit;

        } else {

            $error = "Invalid email or password.";

        }

    } else {

        $error = "Invalid email or password.";

    }

    mysqli_stmt_close($stmt);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Glamour Gems</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root { --green: #173b34; --green-light: #28594e; --gold: #c8a165; --paper: #fbfaf7; --muted: #697570; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; font-family: Poppins, sans-serif; color: var(--green); background: radial-gradient(circle at 10% 15%, rgba(200,161,101,.16), transparent 30%), linear-gradient(135deg, #edf3ef, var(--paper)); }
        .admin-login { width: min(960px, 100%); min-height: 570px; display: grid; grid-template-columns: 1fr 1.05fr; overflow: hidden; border-radius: 24px; background: rgba(255,255,255,.92); box-shadow: 0 28px 80px rgba(23,59,52,.16); border: 1px solid rgba(200,161,101,.3); }
        .brand-panel { position: relative; padding: 54px 48px; color: white; background: linear-gradient(150deg, var(--green), var(--green-light)); overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; }
        .brand-panel:before, .brand-panel:after { content: ''; position: absolute; border: 1px solid rgba(255,255,255,.16); border-radius: 50%; pointer-events: none; }
        .brand-panel:before { width: 360px; height: 360px; right: -170px; top: -110px; }
        .brand-panel:after { width: 240px; height: 240px; left: -135px; bottom: -90px; }
        .brand { position: relative; z-index: 1; display: flex; align-items: center; gap: 12px; font: 600 20px Cinzel, serif; letter-spacing: 2px; }
        .brand i { color: #e3c487; font-size: 26px; }
        .brand-copy { position: relative; z-index: 1; }
        .brand-copy h1 { margin: 0 0 16px; max-width: 330px; font: 600 clamp(30px, 4vw, 47px)/1.15 Cinzel, serif; }
        .brand-copy p { max-width: 330px; margin: 0; color: rgba(255,255,255,.76); line-height: 1.8; font-size: 14px; }
        .secure-note { position: relative; z-index: 1; color: rgba(255,255,255,.72); font-size: 12px; }
        .form-panel { padding: 58px clamp(30px, 6vw, 76px); display: flex; flex-direction: column; justify-content: center; }
        .eyebrow { color: var(--gold); letter-spacing: 2px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .form-panel h2 { margin: 10px 0 8px; font: 600 32px Cinzel, serif; }
        .subtitle { margin: 0 0 30px; color: var(--muted); font-size: 13px; }
        .error { margin-bottom: 20px; padding: 12px 14px; border-radius: 9px; color: #9d2b2b; background: #fff0f0; border: 1px solid #f1caca; font-size: 13px; }
        .success { margin-bottom: 20px; padding: 12px 14px; border-radius: 9px; color: #21633d; background: #e8f5ed; border: 1px solid #c5e3d0; font-size: 13px; }
        .field { margin-bottom: 18px; }
        .field label { display: block; margin-bottom: 8px; color: #38504a; font-size: 13px; font-weight: 600; }
        .input-wrap { position: relative; }
        .input-wrap > i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--gold); }
        .input-wrap input { width: 100%; height: 50px; border: 1px solid #dfe5e1; border-radius: 10px; outline: none; padding: 0 44px; color: var(--green); font: inherit; background: #fcfdfc; }
        .input-wrap input:focus { border-color: var(--gold); box-shadow: 0 0 0 4px rgba(200,161,101,.12); }
        .toggle-password { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); border: 0; padding: 5px; color: var(--muted); background: transparent; cursor: pointer; }
        .login-btn { width: 100%; height: 50px; margin-top: 8px; border: 0; border-radius: 10px; color: white; background: var(--green); font: 600 14px Poppins, sans-serif; cursor: pointer; transition: .25s ease; }
        .login-btn:hover { background: #0f2d27; transform: translateY(-1px); box-shadow: 0 10px 20px rgba(23,59,52,.16); }
        .back-link { display: block; margin-top: 24px; color: var(--muted); text-align: center; text-decoration: none; font-size: 12px; }
        .back-link:hover { color: var(--green); }
        .create-admin { margin: 20px 0 0; color: var(--muted); text-align: center; font-size: 12px; }
        .create-admin a { color: var(--green); font-weight: 600; text-decoration: none; }
        .create-admin a:hover { color: var(--gold); }
        @media (max-width: 700px) { body { padding: 14px; } .admin-login { grid-template-columns: 1fr; } .brand-panel { min-height: 275px; padding: 32px 28px; } .secure-note { margin-top: 30px; } .form-panel { padding: 38px 28px; } }
    </style>
</head>

<body>

    <main class="admin-login">
        <section class="brand-panel">
            <div class="brand"><i class="fa-solid fa-gem"></i> GLAMOUR GEMS</div>
            <div class="brand-copy">
                <h1>Run your store with confidence.</h1>
                <p>Manage products, collections, orders and customer experiences from one calm workspace.</p>
            </div>
            <div class="secure-note"><i class="fa-solid fa-shield-halved"></i> Secure administrator access</div>
        </section>

        <section class="form-panel">
            <span class="eyebrow">Store administration</span>
            <h2>Welcome back</h2>
            <p class="subtitle">Sign in to continue to your admin dashboard.</p>

            <?php if ($error !== ''): ?>
                <div class="error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['registered'])): ?>
                <div class="success"><i class="fa-solid fa-circle-check"></i> Admin account created. You can sign in now.</div>
            <?php endif; ?>
            <?php if (isset($_GET['switch'])): ?>
                <div class="success"><i class="fa-solid fa-circle-check"></i> Enter your admin account details to continue.</div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="field">
                    <label for="admin-email">Admin email</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-envelope"></i>
                        <input id="admin-email" type="email" name="email" placeholder="admin@glamourgems.com" autocomplete="username" required>
                    </div>
                </div>
                <div class="field">
                    <label for="admin-password">Password</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input id="admin-password" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                        <button class="toggle-password" type="button" aria-label="Show password"><i class="fa-regular fa-eye"></i></button>
                    </div>
                </div>
                <button class="login-btn" type="submit" name="login"><i class="fa-solid fa-arrow-right-to-bracket"></i> Sign in to dashboard</button>
            </form>
            <p class="create-admin">New administrator? <a href="register.php">Create admin account</a></p>
            <a class="back-link" href="<?php echo BASE_URL; ?>welcome.php"><i class="fa-solid fa-arrow-left"></i> Back to account selection</a>
        </section>
    </main>

    <script>
        const toggle = document.querySelector('.toggle-password');
        const password = document.querySelector('#admin-password');
        toggle.addEventListener('click', () => {
            const visible = password.type === 'text';
            password.type = visible ? 'password' : 'text';
            toggle.innerHTML = visible ? '<i class="fa-regular fa-eye"></i>' : '<i class="fa-regular fa-eye-slash"></i>';
            toggle.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
        });
    </script>

</body>
</html>