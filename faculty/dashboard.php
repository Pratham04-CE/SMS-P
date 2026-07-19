<?php
require_once '../includes/role_session.php';
start_role_session('faculty');
include('../config/db_connect.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../index1.php");
    exit();
}

$faculty_id = $_SESSION['user_id'];
$faculty_name = $_SESSION['user_name'];

// Fetch Department ID safely for the faculty member
$dept_res = mysqli_query($conn, "SELECT dept_id FROM users WHERE id = '$faculty_id'");
$f_data = mysqli_fetch_assoc($dept_res);
$dept_id = $f_data['dept_id'] ?? 1;

$student_count_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='student' AND dept_id='$dept_id'");
$student_count = mysqli_fetch_assoc($student_count_res)['count'] ?? 0;
?>

<?php
$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Dashboard | SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; display: flex; margin: 0; }
        .sidebar { width: 260px; background: #1e293b; color: white; height: 100vh; position: fixed; padding: 20px; }
        .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 12px; border-radius: 8px; margin-bottom: 5px; }
        .sidebar a.active, .sidebar a:hover { background: #334155; color: white; }
        .main { margin-left: 260px; flex: 1; padding: 40px; }
        .welcome-card { background: white; padding: 25px; border-radius: 12px; border-left: 5px solid #38bdf8; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <?php include('../includes/faculty_nav.php'); ?>

    <div class="main">
        <div class="welcome-card mb-4">
            <h2>Hello, Prof. <?php echo htmlspecialchars($faculty_name); ?>!</h2>
            <p class="text-muted">Manage your department students and academic records.</p>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card p-4 text-center border-0 shadow-sm">
                    <h3><?php echo $student_count; ?></h3>
                    <p class="text-muted">Students in your Dept</p>
                    <a href="enter_marks.php" class="btn btn-primary btn-sm">Manage Marks</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>