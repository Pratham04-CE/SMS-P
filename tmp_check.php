<?php
$conn = mysqli_connect('localhost:3306','root','','student_db');
if (!$conn) { die('DB_CONN_FAIL '.mysqli_connect_error().PHP_EOL); }
$queries = [
  "SELECT COUNT(*) as count FROM users WHERE role='student'",
  "SELECT COUNT(*) as count FROM users WHERE dept_id='1'",
  "SELECT * FROM users WHERE role='student' ORDER BY enrollment_or_id ASC LIMIT 5",
];
foreach ($queries as $q) {
    echo "QUERY: $q\n";
    $res = mysqli_query($conn, $q);
    if ($res === false) {
        echo 'ERR: '.mysqli_error($conn)."\n";
    } else {
        echo 'ROWS: '.mysqli_num_rows($res)."\n";
        while ($row = mysqli_fetch_assoc($res)) {
            print_r($row);
        }
    }
    echo "---\n";
}
mysqli_close($conn);
?>
