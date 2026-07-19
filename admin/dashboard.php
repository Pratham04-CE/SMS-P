<?php
require_once '../includes/role_session.php';
start_role_session('admin');
include('../config/db_connect.php');

mysqli_report(MYSQLI_REPORT_OFF);

// Security Check: Enforce admin access
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: ../index1.php"); exit(); }

// 1. Semester Promote Logic
if(isset($_GET['promote'])) {
    $id = (int)($_GET['promote'] ?? 0);
    try {
        mysqli_query($conn, "UPDATE users SET semester = semester + 1 WHERE id = '$id' AND semester < 8");
    } catch (Throwable $e) {
        // Ignore DB errors for this action and continue to redirect
    }
    header("Location: dashboard.php?msg=Promoted");
}

// 2. Delete Student Logic
if(isset($_GET['delete'])) {
    $id = (int)($_GET['delete'] ?? 0);
    try {
        mysqli_query($conn, "DELETE FROM users WHERE id = '$id'");
    } catch (Throwable $e) {
        // Ignore DB errors for this action and continue to redirect
    }
    header("Location: dashboard.php?msg=Deleted");
}

// Statistics Fetch
$total_students = 0;
$ce_students = 0;
$db_error = false;

try {
    $total_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='student'");
    if ($total_result !== false) {
        $total_row = mysqli_fetch_assoc($total_result);
        $total_students = (int)($total_row['count'] ?? 0);
    } else {
        $db_error = true;
    }

    $ce_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE dept_id='1'");
    if ($ce_result !== false) {
        $ce_row = mysqli_fetch_assoc($ce_result);
        $ce_students = (int)($ce_row['count'] ?? 0);
    } else {
        $db_error = true;
    }
} catch (Throwable $e) {
    $db_error = true;
}

// Fetch All Students
try {
    $students = mysqli_query($conn, "SELECT * FROM users WHERE role='student' ORDER BY enrollment_or_id ASC");
    if ($students === false) {
        $db_error = true;
    }
} catch (Throwable $e) {
    $students = false;
    $db_error = true;
}
?>

<?php
$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        * { box-sizing: border-box; font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
        body { background-color: #f8fafc; display: flex; min-height: 100vh; }

        .sidebar { width: 260px; background: #0f172a; color: white; padding: 20px; position: fixed; height: 100%; }
        .sidebar h2 { font-size: 22px; font-weight: 800; margin-bottom: 30px; color: #38bdf8; text-align: center; border-bottom: 1px solid #1e293b; padding-bottom: 15px; }
        .sidebar a { color: #94a3b8; text-decoration: none; padding: 12px 15px; border-radius: 8px; display: block; margin-bottom: 8px; font-size: 14px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #1e293b; color: #38bdf8; }

        .main-content { margin-left: 260px; flex: 1; padding: 30px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .stat-card h3 { font-size: 12px; color: #64748b; text-transform: uppercase; margin-bottom: 5px; }
        .stat-card p { font-size: 28px; font-weight: 700; color: #1e293b; }

        .btn-custom-alert { background: #f59e0b; color: white; font-weight: 600; border: none; padding: 10px 20px; border-radius: 8px; transition: 0.3s; }
        .btn-custom-alert:hover { background: #d97706; }

        .table-container { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; padding: 15px; font-size: 12px; color: #475569; }
        td { padding: 15px; border-top: 1px solid #f1f5f9; font-size: 14px; }
        .student-img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; }
        
        .btn-promote { background: #dcfce7; color: #166534; font-size: 12px; padding: 5px 10px; border-radius: 4px; text-decoration: none; }
        .btn-delete { background: #fee2e2; color: #991b1b; font-size: 12px; padding: 5px 10px; border-radius: 4px; text-decoration: none; }
    </style>
</head>
<body>

    <?php include('../includes/admin_nav.php'); ?>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 style="font-size: 24px; color: #1e293b;">Admin Dashboard</h1>
            <span class="badge bg-success">System Online</span>
        </div>

        <?php if ($db_error): ?>
        <div class="alert alert-warning mb-4" role="alert">
            Database access is currently unavailable. The dashboard is showing fallback values.
        </div>
        <?php endif; ?>

        <div class="card p-4 mb-4 shadow-sm border-0 bg-white" style="border-left: 5px solid #f59e0b !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Smart Notifications</h5>
                    <p class="text-muted small mb-0">Notify all students whose attendance is currently below 75%.</p>
                </div>
                <form action="send_alerts.php" method="POST">
                    <button type="submit" name="send_alerts" class="btn btn-custom-alert" onclick="return confirm('Send emails to all students with low attendance?')">
                        📢 Send Low Attendance Emails
                    </button>
                </form>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card text-center">
                <h3>Total Students</h3>
                <p><?php echo $total_students; ?></p>
            </div>
            <div class="stat-card text-center">
                <h3>CE Students</h3>
                <p><?php echo $ce_students; ?></p>
            </div>
        </div>

        <div class="table-container shadow-sm">
            <div style="padding: 15px; background: #fff; border-bottom: 1px solid #f1f5f9;">
                <h6 class="mb-0 fw-bold">Recent Registrations</h6>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Enrollment</th>
                        <th>Name</th>
                        <th>Dept</th>
                        <th>Sem</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students && mysqli_num_rows($students) > 0): while($row = mysqli_fetch_assoc($students)): 
                        $pic = (!empty($row['profile_pic'])) ? 'data:image/jpeg;base64,' . base64_encode($row['profile_pic']) : 'https://via.placeholder.com/35';
                    ?>
                    <tr>
                        <td><img src="<?php echo $pic; ?>" class="student-img"></td>
                        <td><strong><?php echo htmlspecialchars($row['enrollment_or_id']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo ($row['dept_id'] == 1) ? 'CE' : 'IT'; ?></td>
                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['semester']); ?></span></td>
                        <td>
                            <a href="dashboard.php?promote=<?php echo $row['id']; ?>" class="btn-promote" onclick="return confirm('Promote student?')">Promote</a>
                            <a href="dashboard.php?delete=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Delete student?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No student data available right now.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>