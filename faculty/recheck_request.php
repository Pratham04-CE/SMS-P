<?php
$activePage = 'recheck_request';
require_once '../includes/role_session.php';
start_role_session('faculty');
// Prevent caching so updates appear immediately
header('Cache-Control: no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
include('../config/db_connect.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../index1.php");
    exit();
}

$faculty_numeric_id = $_SESSION['user_id'];
// Get faculty identifier (faculty_id) stored in faculty_details
$fqq = mysqli_query($conn, "SELECT faculty_id, name FROM faculty_details WHERE id = '".mysqli_real_escape_string($conn, $faculty_numeric_id)."' LIMIT 1");
$frow = mysqli_fetch_assoc($fqq);
$faculty_code = $frow['faculty_id'] ?? '';

// Handle POST actions for rechecking requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['rr_id'])) {
    $action = $_POST['action'];
    $rr_id = (int)$_POST['rr_id'];
    
    if ($action === 'start_review') {
        // Set request status to 'In Progress' to start rechecking
        mysqli_query($conn, "UPDATE recheck_requests SET status = 'In Progress' WHERE id = '$rr_id'");
        $ok = mysqli_affected_rows($conn) > 0;
        $_SESSION['recheck_flash'] = $ok ? 'Request status set to In Progress.' : 'Failed to update request status.';
    } elseif ($action === 'reject') {
        // Set request status to 'Rejected'
        mysqli_query($conn, "UPDATE recheck_requests SET status = 'Rejected' WHERE id = '$rr_id'");
        $ok = mysqli_affected_rows($conn) > 0;
        $_SESSION['recheck_flash'] = $ok ? 'Request rejected.' : 'Rejection may have failed.';
    } elseif ($action === 'resolve') {
        // Get updated marks from form submission
        $new_mid = isset($_POST['new_mid_sem']) ? (int)$_POST['new_mid_sem'] : 0;
        $new_internal = isset($_POST['new_internal']) ? (int)$_POST['new_internal'] : 0;
        
        // Find associated result_id
        $rq = mysqli_query($conn, "SELECT result_id FROM recheck_requests WHERE id = '$rr_id' LIMIT 1");
        if ($rq && mysqli_num_rows($rq) > 0) {
            $rrow = mysqli_fetch_assoc($rq);
            $result_id = $rrow['result_id'];
            
            // Update marks in results table
            mysqli_query($conn, "UPDATE results SET mid_sem_marks = '$new_mid', internal_marks = '$new_internal' WHERE id = '$result_id'");
            
            // Set request status to 'Resolved'
            mysqli_query($conn, "UPDATE recheck_requests SET status = 'Resolved' WHERE id = '$rr_id'");
            
            $_SESSION['recheck_flash'] = 'Marks updated successfully and request marked as Resolved.';
        } else {
            $_SESSION['recheck_flash'] = 'Error: Associated result record not found.';
        }
    }
    header('Location: recheck_request.php'); 
    exit();
}

// Fetch requests for subjects assigned to this faculty (subjects.faculty_id stores faculty code)
$q = "SELECT rr.id as rr_id, rr.result_id, rr.student_id, rr.reason, rr.status, s.sub_name, s.sub_code, r.mid_sem_marks, r.internal_marks, u.name as student_name, u.enrollment_or_id
      FROM recheck_requests rr
      LEFT JOIN subjects s ON s.id = rr.subject_id
      LEFT JOIN results r ON r.id = rr.result_id
      LEFT JOIN users u ON u.id = rr.student_id
      WHERE s.faculty_id = '".mysqli_real_escape_string($conn, $faculty_code)."' ORDER BY rr.id DESC";
$res = mysqli_query($conn, $q);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Recheck Requests | Faculty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
        body { font-family: Inter, sans-serif; background: #f1f5f9; display: flex; margin: 0; }
        .sidebar { width: 260px; background: #1e293b; color: white; height: 100vh; position: fixed; padding: 20px; }
        .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 12px; border-radius: 8px; margin-bottom: 5px; }
        .sidebar a.active, .sidebar a:hover { background: #334155; color: white; }
        .main { margin-left: 260px; padding: 30px; }
        .btn-approve { background: #16a34a; color: white; }
        .btn-reject { background: #dc2626; color: white; }
    </style>
</head>
<body>
    <?php include('../includes/faculty_nav.php'); ?>

    <div class="main">
        <h2>Recheck Requests Assigned to You</h2>
        <p class="text-muted">Verify and update student marks for re-evaluation.</p>

        <?php if (isset($_SESSION['recheck_flash'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['recheck_flash']; 
                unset($_SESSION['recheck_flash']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card p-3">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>Reason</th>
                            <th>Current Marks</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res && mysqli_num_rows($res) > 0): $i=1; while($row = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['student_name'] ?? $row['enrollment_or_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['sub_name'] ?? $row['sub_code']); ?><br><small class="text-muted"><?php echo htmlspecialchars($row['sub_code']); ?></small></td>
                            <td><?php echo htmlspecialchars($row['reason']); ?></td>
                            <td>
                                <b>Mid:</b> <?php echo htmlspecialchars($row['mid_sem_marks'] ?? 'N/A'); ?><br>
                                <b>Int:</b> <?php echo htmlspecialchars($row['internal_marks'] ?? 'N/A'); ?>
                            </td>
                            <td>
                                <?php
                                $s = $row['status'];
                                if ($s === 'Pending') {
                                    echo '<span class="badge bg-warning text-dark">Pending</span>';
                                } elseif ($s === 'In Progress') {
                                    echo '<span class="badge bg-info">In Progress</span>';
                                } elseif ($s === 'Resolved') {
                                    echo '<span class="badge bg-success">Resolved</span>';
                                } elseif ($s === 'Rejected') {
                                    echo '<span class="badge bg-danger">Rejected</span>';
                                } else {
                                    echo '<span class="badge bg-secondary">' . htmlspecialchars($s) . '</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($row['status'] === 'Pending'): ?>
                                    <form method="POST" style="display:inline-block;">
                                        <input type="hidden" name="rr_id" value="<?php echo $row['rr_id']; ?>">
                                        <button type="submit" name="action" value="start_review" class="btn btn-sm btn-info text-white">Start Review</button>
                                    </form>
                                    <form method="POST" style="display:inline-block; margin-left:6px;">
                                        <input type="hidden" name="rr_id" value="<?php echo $row['rr_id']; ?>">
                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-reject">Reject</button>
                                    </form>
                                <?php elseif ($row['status'] === 'In Progress'): ?>
                                    <form method="POST" class="d-flex align-items-end gap-2">
                                        <input type="hidden" name="rr_id" value="<?php echo $row['rr_id']; ?>">
                                        <input type="hidden" name="action" value="resolve">
                                        <div style="width: 75px;">
                                            <label class="small text-muted mb-0">Mid (30)</label>
                                            <input type="number" name="new_mid_sem" class="form-control form-control-sm" value="<?php echo $row['mid_sem_marks']; ?>" max="30" min="0" required>
                                        </div>
                                        <div style="width: 75px;">
                                            <label class="small text-muted mb-0">Int (20)</label>
                                            <input type="number" name="new_internal" class="form-control form-control-sm" value="<?php echo $row['internal_marks']; ?>" max="20" min="0" required>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-success">Save</button>
                                    </form>
                                <?php else: ?>
                                    <small class="text-muted">No actions available</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="7" class="text-center text-muted">No recheck requests assigned to you.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
