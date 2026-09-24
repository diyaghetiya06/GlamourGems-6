<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirm_password === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $check = mysqli_prepare($conn, 'SELECT id FROM admins WHERE email = ? LIMIT 1');
        mysqli_stmt_bind_param($check, 's', $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = 'An admin account already exists with this email.';
            mysqli_stmt_close($check);
        } else {
            mysqli_stmt_close($check);
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, 'admin')");
            mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $hash);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: login.php?registered=1');
                exit;
            }

            $error = 'Unable to create the admin account. Please try again.';
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account | Glamour Gems</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root { --green: #173b34; --green-light: #28594e; --gold: #c8a165; --paper: #fbfaf7; --muted: #697570; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; font-family: Poppins, sans-serif; color: var(--green); background: linear-gradient(135deg, #edf3ef, var(--paper)); }
        .admin-register { width: min(940px, 100%); display: grid; grid-template-columns: .9fr 1.1fr; overflow: hidden; border-radius: 24px; background: rgba(255,255,255,.94); box-shadow: 0 28px 80px rgba(23,59,52,.16); border: 1px solid rgba(200,161,101,.3); }
        .intro { padding: 54px 46px; color: white; background: linear-gradient(150deg, var(--green), var(--green-light)); }
        .brand { font: 600 20px Cinzel, serif; letter-spacing: 2px; }
        .brand i { color: #e3c487; margin-right: 10px; }
        .intro h1 { margin: 110px 0 16px; font: 600 40px/1.15 Cinzel, serif; }
        .intro p { color: rgba(255,255,255,.75); line-height: 1.8; font-size: 14px; }
        .form-panel { padding: 48px clamp(28px, 6vw, 72px); }
        .eyebrow { color: var(--gold); letter-spacing: 2px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        h2 { margin: 10px 0 8px; font: 600 30px Cinzel, serif; }
        .subtitle { margin: 0 0 24px; color: var(--muted); font-size: 13px; }
        .error { margin-bottom: 18px; padding: 11px 13px; border-radius: 9px; color: #9d2b2b; background: #fff0f0; border: 1px solid #f1caca; font-size: 13px; }
        .field { margin-bottom: 15px; }
        label { display: block; margin-bottom: 7px; color: #38504a; font-size: 13px; font-weight: 600; }
        input { width: 100%; height: 46px; padding: 0 14px; border: 1px solid #dfe5e1; border-radius: 9px; outline: none; color: var(--green); font: inherit; background: #fcfdfc; }
        input:focus { border-color: var(--gold); box-shadow: 0 0 0 4px rgba(200,161,101,.12); }
        button { width: 100%; height: 48px; margin-top: 8px; border: 0; border-radius: 9px; color: white; background: var(--green); font: 600 14px Poppins, sans-serif; cursor: pointer; }
        button:hover { background: #0f2d27; }
        .links { display: flex; justify-content: space-between; gap: 12px; margin-top: 20px; font-size: 12px; }
        .links a { color: var(--green); text-decoration: none; font-weight: 600; }
        @media (max-width: 700px) { .admin-register { grid-template-columns: 1fr; } .intro { padding: 32px 28px; } .intro h1 { margin-top: 45px; font-size: 32px; } .form-panel { padding: 36px 28px; } }
    </style>
</head>
<body>
    <main class="admin-register">
        <section class="intro">
            <div class="brand"><i class="fa-solid fa-gem"></i> GLAMOUR GEMS</div>
            <h1>Build your store team.</h1>
            <p>Create a secure administrator account to manage the Glamour Gems experience.</p>
        </section>
        <section class="form-panel">
            <span class="eyebrow">Administrator access</span>
            <h2>Create account</h2>
            <p class="subtitle">Your new account will be saved securely in the admin database.</p>
            <?php if ($error !== ''): ?>
                <div class="error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="field"><label for="name">Full name</label><input id="name" name="name" type="text" placeholder="Admin name" autocomplete="name" required></div>
                <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" placeholder="admin@glamourgems.com" autocomplete="email" required></div>
                <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" placeholder="Minimum 6 characters" autocomplete="new-password" required></div>
                <div class="field"><label for="confirm_password">Confirm password</label><input id="confirm_password" name="confirm_password" type="password" placeholder="Repeat your password" autocomplete="new-password" required></div>
                <button type="submit"><i class="fa-solid fa-user-plus"></i> Create admin account</button>
            </form>
            <div class="links"><a href="login.php"><i class="fa-solid fa-arrow-left"></i> Admin login</a><a href="<?php echo BASE_URL; ?>welcome.php">Account selection</a></div>
        </section>
    </main>
</body>
</html>
