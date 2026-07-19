<?php
require_once '../includes/role_session.php';
start_role_session('faculty');
include('../config/db_connect.php');

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../index1.php");
    exit();
}

// Extract user ID from session
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_SESSION['temp_user_id']) ? $_SESSION['temp_user_id'] : null); 

if (!$user_id) { die("Session Expired. Please login again."); }

// 🛠️ STEP 1: Fetch faculty details (prepared statement)
$user_id = (int)$user_id;
$u_stmt = mysqli_prepare($conn, "SELECT name, faculty_id FROM faculty_details WHERE id = ?");
mysqli_stmt_bind_param($u_stmt, "i", $user_id);
mysqli_stmt_execute($u_stmt);
$u_res = mysqli_stmt_get_result($u_stmt);
if ($u_res && mysqli_num_rows($u_res) > 0) {
    $u_data = mysqli_fetch_assoc($u_res);
    $faculty_code = $u_data['faculty_id']; 
    $faculty_name = $u_data['name'];
} else {
    die("<div class='alert alert-danger'>Faculty details not found.</div>");
}
mysqli_stmt_close($u_stmt);

// 🛠️ STEP 2: Semester filter
$selected_sem = isset($_GET['semester']) ? mysqli_real_escape_string($conn, $_GET['semester']) : '';

// 🛠️ STEP 3: Subjects Fetch Logic (prepared)
$selected_sem = isset($selected_sem) && $selected_sem !== '' ? (int)$selected_sem : null;
if (!empty($selected_sem)) {
    $sub_stmt = mysqli_prepare($conn, "SELECT * FROM subjects WHERE faculty_id = ? AND semester = ?");
    mysqli_stmt_bind_param($sub_stmt, "si", $faculty_code, $selected_sem);
} else {
    $sub_stmt = mysqli_prepare($conn, "SELECT * FROM subjects WHERE faculty_id = ?");
    mysqli_stmt_bind_param($sub_stmt, "s", $faculty_code);
}
mysqli_stmt_execute($sub_stmt);
$subjects_result = mysqli_stmt_get_result($sub_stmt);
mysqli_stmt_close($sub_stmt);

// 🛠️ STEP 4: Save Marks Logic (Using results table)
if (isset($_POST['save_marks'])) {
    $sub_id = isset($_POST['sub_id']) ? $_POST['sub_id'] : '';
    $sub_name = isset($_POST['sub_name']) ? $_POST['sub_name'] : '';
    $sem = isset($_POST['sem']) ? (int)$_POST['sem'] : 0;
    $marks_data = isset($_POST['marks']) ? $_POST['marks'] : [];

    // Prepare statements once
    $check_stmt = mysqli_prepare($conn, "SELECT id FROM results WHERE student_id = ? AND subject_code = ?");
    $update_stmt = mysqli_prepare($conn, "UPDATE results SET mid_sem_marks = ?, internal_marks = ? WHERE student_id = ? AND subject_code = ?");
    $insert_stmt = mysqli_prepare($conn, "INSERT INTO results (student_id, subject_code, subject_name, semester, mid_sem_marks, internal_marks) VALUES (?, ?, ?, ?, ?, ?)");

    foreach ($marks_data as $student_id => $values) {
        $sid = (int)$student_id;
        $mid = isset($values['mid']) ? (int)$values['mid'] : 0;
        $internal = isset($values['internal']) ? (int)$values['internal'] : 0;

        // Check existence
        mysqli_stmt_bind_param($check_stmt, "is", $sid, $sub_id);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            mysqli_stmt_bind_param($update_stmt, "iiis", $mid, $internal, $sid, $sub_id);
            mysqli_stmt_execute($update_stmt);
        } else {
            mysqli_stmt_bind_param($insert_stmt, "issiii", $sid, $sub_id, $sub_name, $sem, $mid, $internal);
            mysqli_stmt_execute($insert_stmt);
        }
        mysqli_stmt_free_result($check_stmt);
    }

    mysqli_stmt_close($check_stmt);
    mysqli_stmt_close($update_stmt);
    mysqli_stmt_close($insert_stmt);

    echo "<script>alert('Marks updated successfully in Results table!'); window.location='enter_marks.php';</script>";
}

// 🛠️ STEP 5: Students Filter Logic
$students = [];
if (isset($_GET['filter']) && !empty($_GET['subject_data'])) {
    $selected_data = explode('|', $_GET['subject_data']);
    $sel_sub_id = $selected_data[0];
    $sel_sub_name = $selected_data[1];
    $sel_sem = $_GET['semester'];
    
    // Fetch students based on their semester
    $sel_sem = (int)$sel_sem;
    $student_stmt = mysqli_prepare($conn, "SELECT id, name, enrollment_or_id FROM users WHERE role = 'student' AND semester = ?");
    mysqli_stmt_bind_param($student_stmt, "i", $sel_sem);
    mysqli_stmt_execute($student_stmt);
    $students = mysqli_stmt_get_result($student_stmt);
    mysqli_stmt_close($student_stmt);
}
?>

<?php
$activePage = 'enter_marks';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enter Marks | SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; display: flex; margin: 0; }
        .sidebar { width: 260px; background: #1e293b; color: white; height: 100vh; position: fixed; padding: 20px; }
        .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 12px; border-radius: 8px; margin-bottom: 5px; }
        .sidebar a.active { background: #334155; color: white; }
        .main { margin-left: 260px; flex: 1; padding: 40px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <?php include('../includes/faculty_nav.php'); ?>

    <div class="main">
        <h2 class="fw-bold mb-4">📝 Mark Entry System</h2>
        
        <div class="card p-4 mb-4">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Semester</label>
                    <select name="semester" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Select Semester --</option>
                        <?php for($i=1; $i<=8; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php if($selected_sem == $i) echo 'selected'; ?>>Semester <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Subject</label>
                    <select name="subject_data" class="form-select" required>
                        <option value="">-- Choose Subject --</option>
                        <?php if($subjects_result && mysqli_num_rows($subjects_result) > 0): ?>
                            <?php while($s = mysqli_fetch_assoc($subjects_result)): ?>
                                <option value="<?php echo $s['sub_code'].'|'.$s['sub_name']; ?>" 
                                    <?php if(isset($sel_sub_id) && $sel_sub_id == $s['sub_code']) echo 'selected'; ?>>
                                    <?php echo $s['sub_code']." - ".$s['sub_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <option disabled>No subjects assigned to you for Sem <?php echo $selected_sem; ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" name="filter" class="btn btn-primary w-100">Fetch Students</button>
                </div>
            </form>
        </div>

        <?php if(!empty($students) && mysqli_num_rows($students) > 0): ?>
        <form method="POST">
            <input type="hidden" name="sub_id" value="<?php echo $sel_sub_id; ?>">
            <input type="hidden" name="sub_name" value="<?php echo $sel_sub_name; ?>">
            <input type="hidden" name="sem" value="<?php echo $sel_sem; ?>">

            <div class="card p-4">
                <div class="mb-3">
                    <span class="badge bg-info">Subject: <?php echo $sel_sub_id . " - " . $sel_sub_name; ?></span>
                    <span class="badge bg-secondary">Semester: <?php echo $sel_sem; ?></span>
                </div>
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Enrollment</th>
                            <th>Student Name</th>
                            <th width="150">Mid (30)</th>
                            <th width="150">Internal (20)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($stu = mysqli_fetch_assoc($students)): ?>
                        <tr>
                            <td><?php echo $stu['enrollment_or_id']; ?></td>
                            <td><?php echo $stu['name']; ?></td>
                            <td><input type="number" name="marks[<?php echo $stu['id']; ?>][mid]" class="form-control" max="30" min="0" required></td>
                            <td><input type="number" name="marks[<?php echo $stu['id']; ?>][internal]" class="form-control" max="20" min="0" required></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="text-end mt-3">
                    <button type="submit" name="save_marks" class="btn btn-success px-5">Save to Results Table</button>
                </div>
            </div>
        </form>
        <?php elseif(isset($_GET['filter'])): ?>
            <div class="alert alert-warning">No students found in Semester <?php echo $sel_sem; ?>.</div>
        <?php endif; ?>
    </div>
</body>
</html>