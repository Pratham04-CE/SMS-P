<?php
require_once '../includes/role_session.php';
start_role_session('admin');
include('../config/db_connect.php');
include('../auth/index_mail_logic.php');

mysqli_report(MYSQLI_REPORT_OFF);

// Security Check: Enforce admin access
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../index1.php"); 
    exit(); 
}

$alert_sent = 0;
$students_res = false;

try {
    // Step 1: Fetch all students
    $students_query = "SELECT id, name, email FROM users WHERE role = 'student'";
    $students_res = mysqli_query($conn, $students_query);

    if ($students_res !== false) {
        while ($student = mysqli_fetch_assoc($students_res)) {
            $sid = $student['id'];
            $sname = $student['name'];
            $semail = $student['email'];

            // Step 2: Calculate attendance
            $calc_q = "SELECT 
                        COUNT(*) as total_lectures,
                        SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as present_count
                       FROM attendance WHERE student_id = '$sid'";
            
            try {
                $calc_res = mysqli_query($conn, $calc_q);
                if ($calc_res !== false) {
                    $data = mysqli_fetch_assoc($calc_res);
                    if (!empty($data['total_lectures']) && $data['total_lectures'] > 0) {
                        $percentage = ($data['present_count'] / $data['total_lectures']) * 100;

                        // Step 3: If attendance < 75%, send email warning
                        if ($percentage < 75) {
                            if (sendLowAttendanceMail($semail, $sname, round($percentage, 2))) {
                                $alert_sent++;
                            }
                        }
                    }
                }
            } catch (Throwable $e) {
                // Ignore per-student query errors and continue
            }
        }
    }
} catch (Throwable $e) {
    $alert_sent = 0;
}

// After completion, redirect back to dashboard
echo "<script>
    alert('Automation Complete! Total $alert_sent emails sent to students with < 75% attendance.');
    window.location.href='dashboard.php';
</script>";
?>