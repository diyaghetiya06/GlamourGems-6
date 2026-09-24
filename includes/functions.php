<?php

// Sanitize user input
function clean($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Redirect to another page
function redirect($url)
{
    header("Location: " . $url);
    exit;
}

// Check whether user is logged in
function isUserLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Check whether admin is logged in
function isAdminLoggedIn()
{
    return isset($_SESSION['admin_id']);
}

// Get logged-in user ID
function getUserId()
{
    return $_SESSION['user_id'] ?? null;
}

// Get logged-in admin ID
function getAdminId()
{
    return $_SESSION['admin_id'] ?? null;
}

?>