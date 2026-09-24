<?php

require_once(__DIR__ . '/../config/config.php');


/*======================================
            ADMIN LOGOUT
======================================*/

unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_role']);


/*======================================
            REDIRECT
======================================*/

header('Location: ' . ADMIN_URL . 'login.php');
exit;

?>
