<?php
$activePage = 'mark_attendance';
require_once '../includes/role_session.php';
start_role_session('faculty');
include('../config/db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../index1.php"); exit();
}

$sub_id = $_GET['sub_id'];
$f_id = $_SESSION['user_id'];
$date = date('Y-m-d'); // Aaj ki date

// --- 💾 SAVE ATTENDANCE LOGIC ---
if (isset($_POST['save_attendance'])) {
    $status_array = $_POST['status']; 

    foreach ($status_array as $student_db_id => $status_val) {
        // Aapke table mein Enum 'P' aur 'A' hai
        $status = ($status_val == 'Present') ? 'P' : 'A';

        // Check if already exists for today
        $check = mysqli_query($conn, "SELECT id FROM attendance WHERE student_id = '$student_db_id' AND subject_id = '$sub_id' AND date = '$date'");
        
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($conn, "UPDATE attendance SET status = '$status' WHERE student_id = '$student_db_id' AND subject_id = '$sub_id' AND date = '$date'");
        } else {
            // Aapke table mein faculty_id column nahi hai, isliye sirf student_id, subject_id, date aur status insert karenge
            mysqli_query($conn, "INSERT INTO attendance (student_id, subject_id, date, status) 
                                 VALUES ('$student_db_id', '$sub_id', '$date', '$status')");
        }
    }
    echo "<script>alert('Attendance Updated successfully for $date!'); window.location.href='view_students.php?sub_id=$sub_id';</script>";
}

// Fetch Students logic
$sub_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM subjects WHERE id = '$sub_id'"));
$students = mysqli_query($conn, "SELECT * FROM users WHERE role = 'student' AND semester = '{$sub_info['semester']}' AND dept_id = '{$sub_info['dept_id']}' ORDER BY enrollment_or_id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance | <?php echo $sub_info['sub_name']; ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; display: flex; margin: 0; }
        .sidebar { width: 260px; background: #1e293b; color: white; height: 100vh; position: fixed; padding: 20px; }
        .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 12px; border-radius: 8px; margin-bottom: 5px; transition: 0.3s; font-size: 14px; }
        .sidebar a:hover, .sidebar a.active { background: #334155; color: white; }
        .main { margin-left: 260px; flex: 1; padding: 40px; }
        .container { max-width: 800px; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #f1f5f9; text-align: left; }
        .btn-submit { background: #0284c7; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; float: right; margin-top: 20px; font-weight: 600; }
        .radio-box { cursor: pointer; margin-right: 10px; }
    </style>
</head>
<body>
    <?php include('../includes/faculty_nav.php'); ?>

    <div class="main">
        <div class="container">
    <a href="dashboard.php" style="text-decoration: none; color: #0284c7;">← Back to Dashboard</a>
    <h2 style="margin-top: 15px;"><?php echo $sub_info['sub_name']; ?></h2>
    <p style="color: #64748b;">Marking attendance for: <strong><?php echo date('d M, Y'); ?></strong></p>

    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th>Enrollment</th>
                    <th>Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($students)): ?>
                <tr>
                    <td><?php echo $row['enrollment_or_id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td>
                        <input type="radio" name="status[<?php echo $row['id']; ?>]" value="Present" checked class="radio-box"> P
                        <input type="radio" name="status[<?php echo $row['id']; ?>]" value="Absent" class="radio-box" style="margin-left: 15px;"> A
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <button type="submit" name="save_attendance" class="btn-submit">Save Attendance</button>
    </form>
        </div>
    </div>

</body>
</html>