<?php
require_once '../includes/role_session.php';
start_role_session('admin');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index1.php");
    exit();
}
include('../config/db_connect.php');

mysqli_report(MYSQLI_REPORT_OFF);

$alert_script = "";

if (isset($_POST['assign_only'])) {
    $subject_id = $_POST['subject_id'] ?? '';
    $f_id = $_POST['faculty_id'] ?? '';

    // Only update the faculty_id in the existing subject
    $update_sql = "UPDATE subjects SET faculty_id = '$f_id' WHERE id = '$subject_id'";
    
    try {
        if (mysqli_query($conn, $update_sql)) {
            $alert_script = "<script>alert('Success: Subject Assigned to Faculty!'); window.location='assign_subject.php';</script>";
        } else {
            $alert_script = "<script>alert('Error updating record.');</script>";
        }
    } catch (Throwable $e) {
        $alert_script = "<script>alert('Database error while assigning subject.');</script>";
    }
}

$faculties = false;
$depts = false;
try {
    $faculties = mysqli_query($conn, "SELECT * FROM faculty_details ORDER BY name ASC");
    $depts = mysqli_query($conn, "SELECT * FROM departments");
} catch (Throwable $e) {
    $faculties = false;
    $depts = false;
}
?>

<?php
$activePage = 'assign_subject';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Subject | Admin</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f8fafc; margin: 0; display: flex; }
        
        /* Sidebar Styling (Matching your Dashboard) */
        .sidebar { width: 260px; background: #0f172a; color: white; height: 100vh; position: fixed; padding: 20px; }
        .sidebar h2 { font-size: 22px; color: #38bdf8; text-align: center; margin-bottom: 30px; border-bottom: 1px solid #1e293b; padding-bottom: 15px; }
        .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 12px; border-radius: 8px; margin-bottom: 5px; font-size: 14px; transition: 0.3s; }
        .sidebar a:hover { background: #1e293b; color: #38bdf8; }
        .sidebar a.active { background: #1e293b; color: #38bdf8; border-left: 4px solid #38bdf8; }

        /* Main Content Styling */
        .main { margin-left: 260px; flex: 1; padding: 40px; display: flex; justify-content: center; align-items: flex-start; }
        .form-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); width: 100%; max-width: 500px; border: 1px solid #e2e8f0; }
        h2 { color: #1e293b; margin-bottom: 20px; font-size: 20px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
        
        label { display: block; font-size: 12px; color: #64748b; margin-top: 15px; margin-bottom: 5px; font-weight: 600; text-transform: uppercase; }
        select, input { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; background: #f9fbff; box-sizing: border-box; }
        input[readonly] { background: #f1f5f9; cursor: not-allowed; }
        
        .btn-submit { width: 100%; padding: 14px; background: #0284c7; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 25px; font-size: 15px; transition: 0.3s; }
        .btn-submit:hover { background: #0369a1; }
    </style>
</head>
<body>

<?php echo $alert_script; ?>

<?php include('../includes/admin_nav.php'); ?>

<div class="main">
    <div class="form-card">
        <h2>Assign Subject to Faculty</h2>
        <form method="POST">
            <label>Department</label>
            <select name="dept_id" id="dept_id" required>
                <option value="">-- Select Dept --</option>
                <?php while($d = mysqli_fetch_assoc($depts)) { echo "<option value='".$d['id']."'>".$d['dept_name']."</option>"; } ?>
            </select>

            <label>Semester</label>
            <select name="semester" id="semester" required>
                <option value="">-- Select Sem --</option>
                <?php for($i=1; $i<=8; $i++) echo "<option value='$i'>Sem $i</option>"; ?>
            </select>

            <label>Select Subject</label>
            <select name="subject_id" id="subject_dropdown" required>
                <option value="">-- First Select Sem & Dept --</option>
            </select>

            <label>Subject Code (Auto-filled)</label>
            <input type="text" id="sub_code_display" readonly placeholder="Subject Code will appear here">

            <label>Assign to Faculty</label>
            <select name="faculty_id" required>
                <option value="">-- Select Professor --</option>
                <?php while($f = mysqli_fetch_assoc($faculties)): ?>
                    <option value="<?php echo $f['faculty_id']; ?>"><?php echo $f['name']; ?></option>
                <?php endwhile; ?>
            </select>

            <button type="submit" name="assign_only" class="btn-submit">Assign Subject</button>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Fetch subjects when department or semester changes
    $('#semester, #dept_id').change(function() {
        var sem = $('#semester').val();
        var dept = $('#dept_id').val();
        if(sem != "" && dept != "") {
            $.ajax({
                url: 'get_subjects.php',
                type: 'POST',
                data: {semester: sem, dept_id: dept},
                success: function(data) {
                    $('#subject_dropdown').html(data);
                    $('#sub_code_display').val(''); 
                }
            });
        }
    });

    // Display the subject code when a subject is selected
    $('#subject_dropdown').change(function() {
        var selected = $(this).find('option:selected');
        var code = selected.data('code');
        $('#sub_code_display').val(code ? code : '');
    });
});
</script>
</body>
</html>