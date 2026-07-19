<?php
$activePage = 'check_recheck';
require_once '../includes/role_session.php';
start_role_session('student');
// Prevent caching so student sees latest status after faculty action
header('Cache-Control: no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
include('../config/db_connect.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index1.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT rr.id as rr_id, rr.result_id, rr.reason, rr.status, s.sub_name, s.sub_code, r.mid_sem_marks, r.internal_marks, r.semester
          FROM recheck_requests rr
          LEFT JOIN subjects s ON s.id = rr.subject_id
          LEFT JOIN results r ON r.id = rr.result_id
          WHERE rr.student_id = '".mysqli_real_escape_string($conn, $user_id)."' ORDER BY rr.id DESC";
$res = mysqli_query($conn, $query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Recheck Requests | Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Inter, sans-serif; background: #f8fafc; }
        .sidebar { width: 260px; background: #1e293b; color: white; min-height: 100vh; position: fixed; padding: 20px; top: 0; left: 0; z-index: 1000; }
        .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 12px; margin-bottom: 10px; border-radius: 8px; }
        .sidebar a.active { background: #334155; color: white; }
        /* Main content stays to the right of the fixed sidebar and prevents horizontal overlap */
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); min-width: 0; overflow-x: auto; }
        .status-pending { color: #b45309; font-weight: 700; }
        .status-in-progress { color: #0284c7; font-weight: 700; }
        .status-resolved { color: #166534; font-weight: 700; }
        .status-rejected { color: #991b1b; font-weight: 700; }
        /* For very small viewports, allow the main content to scroll horizontally but keep sidebar visible */
        @media (max-width: 640px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content { margin-left: 0; width: 100%; padding: 20px; }
        }
    </style>
</head>
<body>
    <?php include('../includes/student_nav.php'); ?>

    <div class="main-content">
        <h3>My Recheck Requests</h3>
        <p class="text-muted">Status of your submitted recheck requests.</p>

        <div class="card p-3 mb-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>Semester</th>
                            <th>Reason</th>
                            <th>Current Marks</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res && mysqli_num_rows($res) > 0): $i=1; while($row = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><b><?php echo htmlspecialchars($row['sub_name'] ?? $row['sub_code']); ?></b><br><small class="text-muted"><?php echo htmlspecialchars($row['sub_code']); ?></small></td>
                            <td><?php echo htmlspecialchars($row['semester']); ?></td>
                            <td><?php echo htmlspecialchars($row['reason']); ?></td>
                                <td>
                                    <b>Mid:</b> <?php echo htmlspecialchars($row['mid_sem_marks'] ?? 'N/A'); ?><br>
                                    <b>Int:</b> <?php echo htmlspecialchars($row['internal_marks'] ?? 'N/A'); ?>
                                </td>
                                <td>
                                    <?php $status = trim(strval($row['status'] ?? ''));
                                    $s = strtolower($status);
                                    if ($s === 'pending'): ?>
                                        <span class="status-pending">Pending</span>
                                    <?php elseif ($s === 'in progress'): ?>
                                        <span class="status-in-progress">In Progress</span>
                                    <?php elseif ($s === 'resolved'): ?>
                                        <span class="status-resolved">Resolved</span>
                                    <?php elseif ($s === 'rejected'): ?>
                                        <span class="status-rejected">Rejected</span>
                                    <?php else: ?>
                                        <span class="text-muted"><?php echo ($status !== '') ? htmlspecialchars($status) : 'Unknown'; ?></span>
                                    <?php endif; ?>
                                </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="6" class="text-center text-muted">You have not submitted any recheck requests yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
