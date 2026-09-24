<?php

session_start();

/*======================================
        LOGOUT CUSTOMER
======================================*/

$_SESSION = array();

session_destroy();

/*======================================
        REDIRECT TO HOME
======================================*/

header("Location: index.php");
exit;

?>