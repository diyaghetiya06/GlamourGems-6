<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "glamourgems";
$sql_file = __DIR__ . "/database/glamourgems.sql";

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_connect($host, $username, $password);

if (!$conn) {
    http_response_code(500);
    exit("Database server connection failed: " . mysqli_connect_error());
}

if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `glamourgems` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
    http_response_code(500);
    exit("Unable to create database: " . mysqli_error($conn));
}

if (!is_readable($sql_file)) {
    http_response_code(500);
    exit("SQL schema file was not found.");
}

mysqli_select_db($conn, $database);
$sql = file_get_contents($sql_file);

if ($sql === false || !mysqli_multi_query($conn, $sql)) {
    http_response_code(500);
    exit("Database setup failed: " . mysqli_error($conn));
}

while (mysqli_more_results($conn)) {
    mysqli_next_result($conn);
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup | Glamour Gems</title>
</head>
<body>
    <h1>Database setup completed</h1>
    <p>The <strong>glamourgems</strong> database and website tables are ready.</p>
    <p>Delete or protect <code>setup-database.php</code> after setup.</p>
</body>
</html>
