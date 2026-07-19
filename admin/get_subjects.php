<?php
include('../config/db_connect.php');

if(isset($_POST['semester']) && isset($_POST['dept_id'])) {
    $sem = $_POST['semester'];
    $dept = $_POST['dept_id'];
    
    $query = "SELECT id, sub_name, sub_code FROM subjects WHERE semester = '$sem' AND dept_id = '$dept'";
    $result = mysqli_query($conn, $query);

    echo '<option value="">-- Select Subject --</option>';
    while($row = mysqli_fetch_assoc($result)) {
        echo '<option value="'.$row['id'].'" data-code="'.$row['sub_code'].'">'.$row['sub_name'].'</option>';
    }
}
?>