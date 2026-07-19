<?php
require_once '../includes/role_session.php';
start_role_session('admin');
include('../config/db_connect.php');

mysqli_report(MYSQLI_REPORT_OFF);

// Admin check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index1.php');
    exit();
}

$fac_res = false;
try {
    $fac_res = mysqli_query($conn, "SELECT faculty_id, name, email, dept_id FROM faculty_details ORDER BY name ASC");
} catch (Throwable $e) {
    $fac_res = false;
}

?>

<?php
$activePage = 'faculty_list';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Faculty List | Admin</title>
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
        .card { border-radius: 12px; }
    </style>
</head>
<body>

<?php include('../includes/admin_nav.php'); ?>

    <div class="main-content">
        <div class="card p-4 mb-4">
            <h4 class="mb-3">All Faculty</h4>
            <p class="text-muted small">List of registered faculty members in the system.</p>
        </div>

        <div class="card p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Faculty ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($fac_res && mysqli_num_rows($fac_res) > 0): $i=1; while($r = mysqli_fetch_assoc($fac_res)):
                            $dept_name = '';
                            if (!empty($r['dept_id'])) {
                                $dr = mysqli_query($conn, "SELECT dept_name FROM departments WHERE id='".mysqli_real_escape_string($conn,$r['dept_id'])."'");
                                $dinfo = mysqli_fetch_assoc($dr);
                                $dept_name = $dinfo['dept_name'] ?? '';
                            }
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($r['faculty_id']); ?></td>
                            <td><?php echo htmlspecialchars($r['name']); ?></td>
                            <td><?php echo htmlspecialchars($r['email']); ?></td>
                            <td><?php echo htmlspecialchars($dept_name); ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5" class="text-center text-muted">No faculty records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
