<?php

// Website name
define("SITE_NAME", "GlamourGems");

// Website URL
define("SITE_URL", "http://localhost/GlamourGems%206");
define("BASE_URL", SITE_URL . "/");

// Admin URL
define("ADMIN_URL", SITE_URL . "/admin/");

// Product upload folder
define("UPLOAD_DIR", __DIR__ . "/../uploads/products/");
define("PRODUCT_UPLOAD_DIR", UPLOAD_DIR);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
