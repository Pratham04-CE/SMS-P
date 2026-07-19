<?php
require_once '../includes/role_session.php';
start_role_session('student');
include('../config/db_connect.php');

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $result_id = mysqli_real_escape_string($conn, $_POST['result_id']);
    $student_id = $_SESSION['user_id'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    
    // Get subject_code from the results table
    $query = "SELECT subject_code FROM results WHERE id = '".mysqli_real_escape_string($conn, $result_id)."'";
    $res = mysqli_query($conn, $query);
    if (!$res || mysqli_num_rows($res) == 0) {
        echo "<script>alert('Invalid result selected.'); window.location='view_results.php';</script>";
        exit();
    }
    $row = mysqli_fetch_assoc($res);
    $subject_code = $row['subject_code'];

    // Map subject_code to subjects.id (foreign key expects numeric id)
    $sq = "SELECT id FROM subjects WHERE sub_code = '".mysqli_real_escape_string($conn, $subject_code)."' LIMIT 1";
    $sres = mysqli_query($conn, $sq);
    if (!$sres || mysqli_num_rows($sres) == 0) {
        echo "<script>alert('Subject record not found for this result. Contact admin.'); window.location='view_results.php';</script>";
        exit();
    }
    $srow = mysqli_fetch_assoc($sres);
    $subject_id = $srow['id'];

    // Insert into recheck_requests
    $sql = "INSERT INTO recheck_requests (result_id, student_id, subject_id, reason, status) 
            VALUES ('".mysqli_real_escape_string($conn, $result_id)."', '".mysqli_real_escape_string($conn, $student_id)."', '".mysqli_real_escape_string($conn, $subject_id)."', '".mysqli_real_escape_string($conn, $reason)."', 'Pending')";

    if(mysqli_query($conn, $sql)) {
        echo "<script>alert('Your recheck request has been submitted to the faculty.'); window.location='view_results.php';</script>";
    } else {
        echo "<script>alert('Error: Could not submit request.'); window.location='view_results.php';</script>";
    }
}
?>