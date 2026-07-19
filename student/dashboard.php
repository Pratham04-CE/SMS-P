<?php
require_once '../includes/role_session.php';
start_role_session('student');
include('../config/db_connect.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index1.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch User Profile
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'"));
$profile_pic = !empty($user['profile_pic']) ? 'data:image/jpeg;base64,' . base64_encode($user['profile_pic']) : 'https://via.placeholder.com/150';

// Attendance Logic
$attendance_stats = [];
$student_id = (int)$user_id;
$student_dept_id = (int)$user['dept_id'];
$student_semester = (int)$user['semester'];
$att_query = "SELECT s.sub_name,
                     COALESCE(att.total, 0) AS total,
                     COALESCE(att.present, 0) AS present
              FROM (
                  SELECT MIN(id) AS subject_id, sub_name
                  FROM subjects
                  WHERE semester = '$student_semester' AND dept_id = '$student_dept_id'
                  GROUP BY sub_name
              ) s
              LEFT JOIN (
                  SELECT subject_id,
                         COUNT(*) AS total,
                         SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) AS present
                  FROM attendance
                  WHERE student_id = '$student_id'
                  GROUP BY subject_id
              ) att ON att.subject_id = s.subject_id
              ORDER BY s.sub_name";
$att_res = mysqli_query($conn, $att_query);
while($row = mysqli_fetch_assoc($att_res)) { $attendance_stats[] = $row; }
?>

<?php
$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard | SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; font-family: 'Inter', sans-serif; display: flex; }
        .sidebar { width: 260px; background: #1e293b; color: white; min-height: 100vh; position: fixed; padding: 20px; }
        .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 12px; border-radius: 8px; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background: #334155; color: white; }
        .main-content { margin-left: 260px; padding: 30px; width: 100%; }
        .info-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <?php include('../includes/student_nav.php'); ?>

    <div class="main-content">
        <div class="info-card d-flex align-items-center gap-4 mb-5">
            <img src="<?php echo $profile_pic; ?>" style="width:100px; height:100px; border-radius:50%; object-fit:cover;">
            <div>
                <h2>Welcome, <?php echo $user['name']; ?> 👋</h2>
                <p class="text-muted"><?php echo $user['enrollment_or_id']; ?> | Sem <?php echo $user['semester']; ?></p>
            </div>
        </div>

        <h4 class="mb-3">📊 My Attendance Overview</h4>
        <div class="row">
            <?php foreach($attendance_stats as $s): 
                $p = ($s['total'] > 0) ? round(($s['present']/$s['total'])*100) : 0;
                $c = ($p >= 75) ? '#22c55e' : '#ef4444';
            ?>
            <div class="col-md-4 mb-3">
                <div class="bg-white p-3 rounded shadow-sm border-top" style="border-color: <?php echo $c; ?> !important; border-top-width: 4px !important;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold small"><?php echo $s['sub_name']; ?></span>
                        <span class="fw-bold small" style="color:<?php echo $c; ?>"><?php echo $p; ?>%</span>
                    </div>
                    <div class="progress" style="height: 7px;">
                        <div class="progress-bar" style="width:<?php echo $p; ?>%; background:<?php echo $c; ?>"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <br><br>
</body>
</html>