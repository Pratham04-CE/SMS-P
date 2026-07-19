<?php
require_once '../includes/role_session.php';
start_role_session('faculty');
include('../config/db_connect.php'); 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../index1.php");
    exit();
}

$faculty_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Faculty";
$faculty_id = $_SESSION['user_id'];
$faculty_code = '';
// Try to fetch faculty code (faculty_id) from faculty_details to limit subjects shown
$fqr = mysqli_query($conn, "SELECT faculty_id FROM faculty_details WHERE id = '".mysqli_real_escape_string($conn, $faculty_id)."' LIMIT 1");
if ($fqr && mysqli_num_rows($fqr) > 0) {
    $frow = mysqli_fetch_assoc($fqr);
    $faculty_code = $frow['faculty_id'] ?? '';
}
$students = [];
$selected_sub = $selected_sem = $selected_dept = "";

// Attendance Submit Logic
if (isset($_POST['submit_attendance'])) {
    $sub_id = $_POST['final_subject_id'];
    $att_date = $_POST['attendance_date'];
    $att_data = $_POST['status'];

    foreach ($att_data as $std_id => $status) {
        // Pehle student ki details fetch karo (Sahi data ke liye)
        $info_q = "SELECT u.name, u.enrollment_or_id, d.dept_name 
                   FROM users u 
                   JOIN departments d ON u.dept_id = d.id 
                   WHERE u.id = '$std_id'";
        $info_res = mysqli_query($conn, $info_q);
        $user_info = mysqli_fetch_assoc($info_res);

        $std_name = $user_info['name'];
        $std_enroll = $user_info['enrollment_or_id'];
        $std_dept = $user_info['dept_name'];

        // Check if record already exists
        $check = mysqli_query($conn, "SELECT id FROM attendance WHERE student_id = '$std_id' AND subject_id = '$sub_id' AND date = '$att_date'");
        
        if (mysqli_num_rows($check) > 0) {
            // Agar pehle se hai, toh sirf status badlo (details wahi rahengi)
            mysqli_query($conn, "UPDATE attendance SET status = '$status' WHERE student_id = '$std_id' AND subject_id = '$sub_id' AND date = '$att_date'");
        } else {
            // Naya record saari details ke saath
            $insert_query = "INSERT INTO attendance (student_id, name, enrollment_or_id, dept_name, subject_id, date, status) 
                             VALUES ('$std_id', '$std_name', '$std_enroll', '$std_dept', '$sub_id', '$att_date', '$status')";
            
            if (!mysqli_query($conn, $insert_query)) {
                die("Insert Error: " . mysqli_error($conn));
            }
        }
    }
    echo "<script>alert('Attendance Updated Successfully!'); window.location.href='mark_attendance.php';</script>";
}

// Student Filtering Logic
if (isset($_POST['filter_students'])) {
    $selected_sub = $_POST['subject_id'];
    $selected_sem = $_POST['semester'];
    $selected_dept = $_POST['department'];

    $query = "SELECT id, name, enrollment_or_id FROM users 
              WHERE semester = '$selected_sem' AND dept_id = '$selected_dept' AND role = 'student'";
    $students = mysqli_query($conn, $query);
}
?>

<?php
$activePage = 'mark_attendance';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mark Attendance | SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; display: flex; margin: 0; }
        .sidebar { width: 260px; background: #1e293b; color: white; height: 100vh; position: fixed; padding: 20px; }
        .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 12px; border-radius: 8px; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background: #334155; color: white; }
        .main { margin-left: 260px; flex: 1; padding: 40px; }
        .card { border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <?php include('../includes/faculty_nav.php'); ?>

    <div class="main">
    <div class="card shadow p-4 mb-4 border-0" style="border-left: 5px solid #0284c7 !important;">
        <h4 class="mb-3 text-primary">Filters</h4>
        <form method="POST" class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-bold">Subject</label>
                <select name="subject_id" class="form-select" required>
                    <option value="">-- Select Subject --</option>
                    <?php 
                    // Show unique subjects assigned to this faculty (group by sub_code). If faculty_code empty, show distinct subjects globally.
                    if (!empty($faculty_code)) {
                        $subs_q = "SELECT MIN(id) as id, sub_code, sub_name FROM subjects WHERE faculty_id = '".mysqli_real_escape_string($conn, $faculty_code)."' GROUP BY sub_code, sub_name ORDER BY sub_name ASC";
                    } else {
                        $subs_q = "SELECT MIN(id) as id, sub_code, sub_name FROM subjects GROUP BY sub_code, sub_name ORDER BY sub_name ASC";
                    }
                    $subs = mysqli_query($conn, $subs_q);
                    while($row = mysqli_fetch_assoc($subs)) {
                        $sel = ($selected_sub == $row['id']) ? "selected" : "";
                        $label = (!empty($row['sub_code'])) ? $row['sub_code'].' - '.$row['sub_name'] : $row['sub_name'];
                        echo "<option value='".$row['id']."' $sel>".htmlspecialchars($label)."</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Semester</label>
                <select name="semester" class="form-select" required>
                    <?php for($i=1; $i<=8; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php if($selected_sem == $i) echo "selected"; ?>>Semester <?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Department</label>
                <select name="department" class="form-select" required>
                    <option value="">-- Select Dept --</option>
                    <?php 
                    $depts = mysqli_query($conn, "SELECT * FROM departments");
                    while($d = mysqli_fetch_assoc($depts)) {
                        $sel = ($selected_dept == $d['id']) ? "selected" : "";
                        echo "<option value='".$d['id']."' $sel>".$d['dept_name']."</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" name="filter_students" class="btn btn-dark w-100">Show Students</button>
            </div>
        </form>
    </div>

    <?php if (isset($_POST['filter_students'])): ?>
        <?php if ($students && mysqli_num_rows($students) > 0): ?>
        <form method="POST">
            <input type="hidden" name="final_subject_id" value="<?php echo $selected_sub; ?>">
            <div class="card shadow p-4 border-0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Attendance Sheet: <?php echo date('d-M-Y'); ?></h5>
                    <input type="date" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" class="form-control w-25" required>
                </div>
                <table class="table border">
                    <thead class="table-primary">
                        <tr>
                            <th>Enrollment</th>
                            <th>Name</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($s = mysqli_fetch_assoc($students)): ?>
                        <tr>
                            <td><?php echo $s['enrollment_or_id']; ?></td>
                            <td><?php echo $s['name']; ?></td>
                            <td class="text-center">
                                <input type="radio" name="status[<?php echo $s['id']; ?>]" value="P" checked> P
                                <input type="radio" name="status[<?php echo $s['id']; ?>]" value="A" class="ms-2"> A
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <button type="submit" name="submit_attendance" class="btn btn-success w-100 mt-2">Submit Attendance</button>
            </div>
        </form>
        <?php else: ?>
            <div class="alert alert-warning">No students found.</div>
        <?php endif; ?>
    <?php endif; ?>
    </div>

</body>
</html>