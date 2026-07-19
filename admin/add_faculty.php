<?php
require_once '../includes/role_session.php';
start_role_session('admin');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index1.php");
    exit();
}
include('../config/db_connect.php');

$alert_script = "";

if (isset($_POST['register_faculty'])) {
    $f_id = mysqli_real_escape_string($conn, $_POST['faculty_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $dept = $_POST['dept_id'];
    
    // Auto Password Generation
    $plain_password = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
    $hashed_pass = password_hash($plain_password, PASSWORD_DEFAULT);

    // Check Duplicate
    $check = mysqli_query($conn, "SELECT * FROM faculty_details WHERE faculty_id = '$f_id' OR email = '$email'");

    if (mysqli_num_rows($check) > 0) {
        $alert_script = "<script>alert('Error: Faculty ID or Email already exists!');</script>";
    } else {
        // Insert into faculty_details
        $sql = "INSERT INTO faculty_details (faculty_id, name, email, dept_id, password, role) 
                VALUES ('$f_id', '$name', '$email', '$dept', '$hashed_pass', 'faculty')";
        
        if (mysqli_query($conn, $sql)) {
            // --- MAIL LOGIC START ---
            include('add_faculty_mail_logic.php');
            if (sendFacultyWelcomeMail($email, $name, $f_id, $plain_password)) {
                $mail_status = "and credentials sent to email!";
            } else {
                $mail_status = "but email sending failed. Please check SMTP.";
            }
            // --- MAIL LOGIC END ---

            $alert_script = "<script>alert('Success! Faculty Added $mail_status\\nID: $f_id\\nPass: $plain_password');</script>";
        } else {
            $error = mysqli_error($conn);
            $alert_script = "<script>alert('Database Error: $error');</script>";
        }
    }
}
?>

<?php
$activePage = 'add_faculty';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Faculty | Admin SMS</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f8fafc; margin: 0; display: flex; }
        
        .sidebar { width: 260px; background: #0f172a; color: white; height: 100vh; position: fixed; padding: 20px; }
        .sidebar h2 { font-size: 22px; color: #38bdf8; text-align: center; margin-bottom: 30px; border-bottom: 1px solid #1e293b; padding-bottom: 15px; }
        .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 12px; border-radius: 8px; margin-bottom: 5px; font-size: 14px; }
        .sidebar a:hover { background: #1e293b; color: #38bdf8; }

        .main { margin-left: 260px; flex: 1; padding: 40px; display: flex; justify-content: center; }
        .form-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); width: 100%; max-width: 500px; border: 1px solid #e2e8f0; }
        h2 { color: #1e293b; font-size: 20px; margin-bottom: 25px; }
        
        .input-group { margin-bottom: 18px; }
        label { display: block; font-size: 13px; color: #64748b; margin-bottom: 8px; font-weight: 600; }
        input, select { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; background: #f9fbff; }

        .btn-submit { width: 100%; padding: 14px; background: #0284c7; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px; }
        .btn-submit:hover { background: #0369a1; }
    </style>
</head>
<body>

<?php echo $alert_script; ?>

<?php include('../includes/admin_nav.php'); ?>

<div class="main">
    <div class="form-card">
        <h2>Register New Faculty</h2>
        <form method="POST">
            <div class="input-group">
                <label>Faculty ID (Enrollment ID)</label>
                <input type="text" name="faculty_id" placeholder="FAC101" required>
            </div>
            
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Prof. Name" required>
            </div>

            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="faculty@college.edu" required>
            </div>

            <div class="input-group">
                <label>Department</label>
                <select name="dept_id" required>
                    <option value="1">Computer Engineering (CE)</option>
                    <option value="2">Information Technology (IT)</option>
                    <option value="3">Civil Engineering (Civil)</option>
                </select>
            </div>

            <button type="submit" name="register_faculty" class="btn-submit">Register Faculty</button>
        </form>
    </div>
</div>

</body>
</html>