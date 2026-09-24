<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: admin/dashboard.php');
    exit;
}

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Glamour Gems</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --ink: #173b34; --gold: #c8a165; --paper: #fbfaf7; --muted: #68716d; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; color: var(--ink); background: linear-gradient(135deg, #f5efe4, #fbfaf7 48%, #e8f0eb); font-family: Poppins, sans-serif; display: grid; place-items: center; padding: 28px; }
        .welcome { width: min(980px, 100%); display: grid; grid-template-columns: 1fr 1.1fr; background: rgba(255,255,255,.9); border: 1px solid rgba(200,161,101,.35); box-shadow: 0 24px 70px rgba(23,59,52,.14); border-radius: 22px; overflow: hidden; }
        .intro { padding: 64px 48px; background: linear-gradient(155deg, #173b34, #28584d); color: white; position: relative; }
        .intro:after { content: ''; position: absolute; width: 220px; height: 220px; border: 1px solid rgba(255,255,255,.2); border-radius: 50%; right: -80px; bottom: -70px; }
        .mark { color: #e3c487; font-size: 28px; letter-spacing: 4px; }
        h1 { font: 600 clamp(32px, 5vw, 52px)/1.1 Cinzel, serif; margin: 25px 0 16px; }
        .intro p { max-width: 360px; color: rgba(255,255,255,.78); line-height: 1.8; }
        .panel { padding: 48px; display: flex; flex-direction: column; justify-content: center; }
        .panel h2 { font: 600 28px Cinzel, serif; margin: 0 0 8px; }
        .panel > p { color: var(--muted); margin: 0 0 28px; }
        .choice { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .choice a { text-decoration: none; color: var(--ink); border: 1px solid #d9ded9; border-radius: 12px; padding: 20px; transition: .25s ease; background: white; }
        .choice a:hover { transform: translateY(-3px); border-color: var(--gold); box-shadow: 0 10px 24px rgba(23,59,52,.1); }
        .choice strong { display: block; font-size: 17px; margin-bottom: 6px; }
        .choice span { color: var(--muted); font-size: 12px; line-height: 1.5; }
        .create-account { grid-column: 1 / -1; text-align: center; }
        .create-account strong, .create-account span { display: inline; }
        .create-account strong { margin-right: 6px; }
        @media (max-width: 700px) { .welcome { grid-template-columns: 1fr; } .intro, .panel { padding: 36px 28px; } .choice { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <main class="welcome">
        <section class="intro">
            <div class="mark">✦ GLAMOUR GEMS</div>
            <h1>Welcome to your jewellery journey.</h1>
            <p>Sign in to discover your collection, manage your orders and save the pieces you love.</p>
        </section>
        <section class="panel">
            <h2>Choose your account</h2>
            <p>Continue as a customer or open the store dashboard.</p>
            <div class="choice">
                <a href="login.php"><strong>User Login</strong><span>Already have a Glamour Gems account?</span></a>
                <a href="admin/login.php"><strong>Admin Login</strong><span>Manage the Glamour Gems store.</span></a>
                <a class="create-account" href="register.php"><strong>Create Account</strong><span>New here? Register your customer account.</span></a>
            </div>
        </section>
    </main>
</body>
</html>
