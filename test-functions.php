<?php

require_once "config/config.php";
require_once "includes/functions.php";

echo "<h2>Functions File Working Successfully!</h2>";

echo "<p>Clean Test: " . clean("  GlamourGems  ") . "</p>";

echo "<p>User Logged In: ";
echo isUserLoggedIn() ? "YES" : "NO";
echo "</p>";

echo "<p>Admin Logged In: ";
echo isAdminLoggedIn() ? "YES" : "NO";
echo "</p>";

?>