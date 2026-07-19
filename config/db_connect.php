<?php
// Debugging ON
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "bexfkwadpmmxuduevla3-mysql.services.clever-cloud.com";
$user = "u4t59ngvwo6ye93r";
$pass = "Im9MZghH3EFepvQtVW8h";
$dbname = "bexfkwadpmmxuduevla3";
$port = 3306;

$conn = mysqli_connect($host, $user, $pass, $dbname, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} else {
    echo "Database Connected Successfully!";
}
?>