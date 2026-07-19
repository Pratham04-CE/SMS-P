<?php
// Database configuration
$host = "bexfkwadpmmxuduevla3-mysql.services.clever-cloud.com";
$user = "u4t59ngvwo6ye93r";
$pass = "Im9MZghH3EFepvQtVW8h";
$dbname = "bexfkwadpmmxuduevla3";
$port = 3306;

// Connection error report karne ke liye
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect($host, $user, $pass, $dbname, $port);
} catch (mysqli_sql_exception $e) {
    die("Database Connection Error: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>