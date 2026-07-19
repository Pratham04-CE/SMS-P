<?php
require_once '../includes/role_session.php';
start_role_session('student');
include('../config/db_connect.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index1.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all results for the student
$query = "SELECT * FROM results WHERE student_id = '$user_id' ORDER BY semester DESC";
$result_set = mysqli_query($conn, $query);
?>

<?php
$activePage = 'view_results';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Results & Performance | SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fe; font-family: 'Segoe UI', sans-serif; display: flex; }
        .sidebar { width: 260px; background: #1e293b; color: white; min-height: 100vh; position: fixed; padding: 20px; }
        .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 12px; margin-bottom: 10px; border-radius: 8px; }
        .sidebar a.active { background: #334155; color: white; }
        .main-content { margin-left: 260px; padding: 40px; width: 100%; }
        .nav-tabs .nav-link { color: #64748b; font-weight: 600; border: none; }
        .nav-tabs .nav-link.active { color: #0ea5e9; border-bottom: 3px solid #0ea5e9; background: transparent; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        @media print { .sidebar, .nav-tabs, .btn-report, .btn-recheck { display: none !important; } .main-content { margin: 0; padding: 0; width: 100%; } }
    </style>
</head>
<body>
    <?php include('../includes/student_nav.php'); ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Academic Performance</h2>
            <button onclick="window.print()" class="btn btn-success px-4">📄 Generate Report</button>
        </div>

        <ul class="nav nav-tabs mb-4" id="resultTabs">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#mid">1. Mid-Sem Result</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#internal">2. Internal Grade</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#final">3. Final Exam (GTU)</button></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="mid">
                <div class="card p-4">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Subject</th>
                                <th>Mid-Sem (30)</th>
                                <th class="btn-recheck">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result_set)) { ?>
                            <tr>
                                <td><b><?php echo $row['subject_name']; ?></b><br><small class="text-muted"><?php echo $row['subject_code']; ?></small></td>
                                <td><?php echo $row['mid_sem_marks'] ?? '<span class="text-muted">N/A</span>'; ?></td>
                                <td class="btn-recheck">
                                    <?php if($row['mid_sem_marks'] !== null) { ?>
                                        <button class="btn btn-sm btn-outline-primary" onclick="openRecheck(<?php echo $row['id']; ?>, '<?php echo $row['subject_name']; ?>')">🔄 Recheck</button>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } mysqli_data_seek($result_set, 0); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="internal">
                <div class="card p-4">
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th>Subject Name</th>
                                <th>Internal/Submission (20)</th>
                                <th>Overall Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result_set)) { ?>
                            <tr>
                                <td><?php echo $row['subject_name']; ?></td>
                                <td><?php echo $row['internal_marks'] ?? 'N/A'; ?></td>
                                <td>
                                    <?php 
                                    $total = ($row['mid_sem_marks'] ?? 0) + ($row['internal_marks'] ?? 0);
                                    $percent = ($total / 50) * 100;
                                    echo "<div class='progress' style='height: 8px;'><div class='progress-bar bg-info' style='width: $percent%'></div></div>";
                                    ?>
                                </td>
                            </tr>
                            <?php } mysqli_data_seek($result_set, 0); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="final">
                <div class="card p-5 text-center">
                    <h4>University External Grades</h4>
                    <p class="text-muted">Final exam grades are integrated from the GTU portal.</p>
                    <a href="https://www.gturesults.in/" target="_blank" class="btn btn-danger px-4 mt-2">🔗 Go to GTU Result Website</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="recheckModal" tabindex="-1">
        <div class="modal-dialog shadow-lg">
            <div class="modal-content">
                <form action="submit_recheck.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Request Rechecking</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="result_id" id="modal_result_id">
                        <p>Requesting recheck for: <b id="modal_subject_name"></b></p>
                        <div class="mb-3">
                            <label class="form-label">Reason/Remark</label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="Describe why you want a recheck..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100">Submit Recheck Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openRecheck(id, name) {
            document.getElementById('modal_result_id').value = id;
            document.getElementById('modal_subject_name').innerText = name;
            new bootstrap.Modal(document.getElementById('recheckModal')).show();
        }
    </script>
</body>
</html>