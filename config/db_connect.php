<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
echo "Script started...";

$conn = mysqli_connect("bexfkwadpmmxuduevla3-mysql.services.clever-cloud.com", "u4t59ngvwo6ye93r", "Im9MZghH3EFepvQtVW8h", "bexfkwadpmmxuduevla3", 3306);

if (!$conn) {
    echo "<br>Connection failed: " . mysqli_connect_error();
} else {
    echo "<br>Connected successfully!";
}
?>