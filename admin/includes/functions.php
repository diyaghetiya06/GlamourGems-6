<?php

require_once __DIR__ . '/../../config/database.php';

/*======================================
        FLASH MESSAGE
======================================*/

function set_flash($type, $message)
{
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

function get_flash()
{
    if (isset($_SESSION['flash_message'])) {

        $message = $_SESSION['flash_message'];

        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_message']);

        return $message;
    }

    return null;
}

/*======================================
        SANITIZE INPUT
======================================*/

function sanitize($value)
{
    return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
}

/*======================================
        CREATE SLUG
======================================*/

function create_slug($text)
{
    $text = strtolower(trim($text));

    $text = preg_replace('/[^a-z0-9]+/', '-', $text);

    $text = trim($text, '-');

    return $text;
}

/*======================================
        FORMAT PRICE
======================================*/

function format_price($price)
{
    return '₹' . number_format((float)$price, 2);
}

/*======================================
        DISPLAY FLASH MESSAGE
======================================*/

function display_flash()
{
    if (isset($_SESSION['flash_message'])) {

        $type = $_SESSION['flash_type'] ?? 'info';
        $message = $_SESSION['flash_message'];

        echo '<div class="alert alert-' . htmlspecialchars($type) . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';

        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_message']);
    }
}

/*======================================
        INDIAN PRICE FORMAT
======================================*/

function formatIndianPrice($price)
{
    $price = number_format((float)$price, 0, '.', '');

    $lastThree = substr($price, -3);
    $remaining = substr($price, 0, -3);

    if ($remaining != '') {

        $remaining = preg_replace(
            '/\B(?=(\d{2})+(?!\d))/',
            ',',
            $remaining
        );

        return $remaining . ',' . $lastThree;
    }

    return $lastThree;
}

?>