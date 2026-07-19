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

// Filters (from GET)
$semester = isset($_GET['semester']) && $_GET['semester'] !== '' ? (int)$_GET['semester'] : '';
$dept_id = isset($_GET['dept_id']) && $_GET['dept_id'] !== '' ? (int)$_GET['dept_id'] : '';
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Build query
$where = "WHERE role = 'student'";
if ($semester !== '') {
    $where .= " AND semester = '" . mysqli_real_escape_string($conn, $semester) . "'";
}
if ($dept_id !== '') {
    $where .= " AND dept_id = '" . mysqli_real_escape_string($conn, $dept_id) . "'";
}
if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $where .= " AND (name LIKE '%$s%' OR enrollment_or_id LIKE '%$s%' OR email LIKE '%$s%')";
}

$query = "SELECT id, enrollment_or_id, name, email, dept_id, semester FROM users " . $where . " ORDER BY enrollment_or_id ASC";
$students_res = false;
$depts_res = false;

try {
    $students_res = mysqli_query($conn, $query);
    $depts_res = mysqli_query($conn, "SELECT id, dept_name FROM departments ORDER BY dept_name ASC");
} catch (Throwable $e) {
    $students_res = false;
    $depts_res = false;
}

?>

<?php
$activePage = 'student_list';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Student List | Admin</title>
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
        .filters .form-control { min-width: 160px; }
    </style>
</head>
<body>
    <?php include('../includes/admin_nav.php'); ?>

    <div class="main-content">
        <div class="card p-4 mb-4">
            <h4 class="mb-3">View Students (All Semesters)</h4>
            <form method="GET" class="row g-2 align-items-end filters">
                <div class="col-auto">
                    <label class="form-label">Semester</label>
                    <select name="semester" class="form-select">
                        <option value="">All</option>
                        <?php for ($i=1; $i<=8; $i++): ?>
                            <option value="<?php echo $i;?>" <?php if($semester===$i) echo 'selected'; ?>>Semester <?php echo $i;?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-auto">
                    <label class="form-label">Department</label>
                    <select name="dept_id" class="form-select">
                        <option value="">All</option>
                        <?php while($d = mysqli_fetch_assoc($depts_res)): ?>
                            <option value="<?php echo $d['id']; ?>" <?php if($dept_id===$d['id']) echo 'selected'; ?>><?php echo htmlspecialchars($d['dept_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-auto flex-fill">
                    <label class="form-label">Search</label>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" class="form-control" placeholder="Name, Enrollment or Email">
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="student_list.php" class="btn btn-outline-secondary ms-1">Reset</a>
                </div>
            </form>
        </div>

        <div class="card p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Enrollment</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Semester</th>
                            <th>Department</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($students_res && mysqli_num_rows($students_res) > 0):
                            $cnt = 1;
                            while ($row = mysqli_fetch_assoc($students_res)):
                                // Resolve dept name
                                $dept_name = '';
                                if (!empty($row['dept_id'])) {
                                    $dr = mysqli_query($conn, "SELECT dept_name FROM departments WHERE id='".mysqli_real_escape_string($conn, $row['dept_id'])."'");
                                    $dinfo = mysqli_fetch_assoc($dr);
                                    $dept_name = $dinfo['dept_name'] ?? '';
                                }
                        ?>
                        <tr>
                            <td><?php echo $cnt++; ?></td>
                            <td><?php echo htmlspecialchars($row['enrollment_or_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['semester']); ?></td>
                            <td><?php echo htmlspecialchars($dept_name); ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="6" class="text-center text-muted">No students found for selected filters.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
