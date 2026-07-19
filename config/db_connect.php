<?php
// Database configuration
$host = "bexfkwadpmmxuduevla3-mysql.services.clever-cloud.com";
$user = "u4t59ngvwo6ye93r";       
$pass = "Im9MZghH3EFepvQtVW8h";           
$dbname = "bexfkwadpmmxuduevla3"; 
$port = 3306; 

// Establish database connection
$conn = mysqli_connect($host, $user, $pass, $dbname, $port);

// Verify database connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

@ini_set('session.gc_maxlifetime', 86400); 
if (session_status() === PHP_SESSION_ACTIVE) {
    setcookie(session_name(), session_id(), time() + 86400, '/');
}
?>