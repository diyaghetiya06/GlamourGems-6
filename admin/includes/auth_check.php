<?php
require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/functions.php');
// Check if admin session exists
if (!isset($_SESSION['admin_id'])) {
    set_flash('danger', 'Please login to access the admin dashboard.');
    header('Location: ' . ADMIN_URL . 'login.php');
    exit;
}
?>
