<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('../config/db_connect.php');
// Mail logic include karein - Make sure file path is correct
include('../auth/index_mail_logic.php'); 

if (!isset($_SESSION['user_id'])) { header("Location: ../index1.php"); exit(); }

$f_id = $_SESSION['user_id']; // Ye 'id' column hai
$step = 1; 

// 1. Fetch current faculty record only (prepared)
$f_id = (int)$f_id;
$u_stmt = mysqli_prepare($conn, "SELECT name, email, password FROM faculty_details WHERE id = ?");
mysqli_stmt_bind_param($u_stmt, "i", $f_id);
mysqli_stmt_execute($u_stmt);
$res = mysqli_stmt_get_result($u_stmt);
$user = mysqli_fetch_assoc($res);
mysqli_stmt_close($u_stmt);

if (isset($_POST['send_otp'])) {
    $email_input = mysqli_real_escape_string($conn, $_POST['email']);
    $old_pass = $_POST['old_pass'];
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    // Error Handling: Check if $user is null
    if (!$user) {
        echo "<script>alert('Error: Faculty data not found!');</script>";
    } elseif ($email_input !== $user['email']) {
        echo "<script>alert('Error: Email does not match our records!');</script>";
    } elseif (!password_verify($old_pass, $user['password'])) {
        echo "<script>alert('Error: Current password is incorrect!');</script>";
    } elseif ($new_pass !== $confirm_pass) {
        echo "<script>alert('Error: New passwords do not match!');</script>";
    } else {
        // Generate OTP
        $otp = rand(100000, 999999);
        
        // Mailer check: Aapke index_mail_logic mein function ka naam 'sendOTP' hai ya 'sendVerificationMail'?
        // Maine yahan dono handle karne ke liye condition lagayi hai.
        $mailSent = false;
        if (function_exists('sendVerificationMail')) {
            $mailSent = sendVerificationMail($email_input, $user['name'], $otp);
        } elseif (function_exists('sendOTP')) {
            $mailSent = sendOTP($email_input, $otp);
        }

        if ($mailSent) {
            $_SESSION['change_pass_otp'] = $otp;
            $_SESSION['new_pass_temp'] = password_hash($new_pass, PASSWORD_DEFAULT);
            $step = 2;
        } else {
            echo "<script>alert('Error: Could not send OTP. Check Mailer settings.');</script>";
        }
    }
}

if (isset($_POST['verify_and_update'])) {
    if ($_POST['otp'] == $_SESSION['change_pass_otp']) {
        $hashed = $_SESSION['new_pass_temp'];
        // Update password in faculty_details table (prepared)
        $upd_stmt = mysqli_prepare($conn, "UPDATE faculty_details SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($upd_stmt, "si", $hashed, $f_id);
        $exec = mysqli_stmt_execute($upd_stmt);
        mysqli_stmt_close($upd_stmt);

        if($exec) {
            unset($_SESSION['change_pass_otp'], $_SESSION['new_pass_temp']);
            echo "<script>alert('Success: Your password has been changed!'); window.location='profile.php';</script>";
        } else {
            echo "<script>alert('Database Error!');</script>";
        }
    } else {
        $step = 2;
        echo "<script>alert('Error: Invalid OTP!');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Secure Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; display: flex; margin: 0; }
        .sidebar { width: 260px; background: #1e293b; color: white; height: 100vh; position: fixed; padding: 20px; }
        .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 12px; border-radius: 8px; margin-bottom: 5px; }
        .sidebar a.active, .sidebar a:hover { background: #334155; color: white; }
        .main { margin-left: 260px; flex: 1; padding: 40px; display: flex; justify-content: center; align-items: flex-start; }
        .box { background: white; padding: 30px; border-radius: 12px; width: 420px; box-shadow: 0 10px 25px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
        input, button { width: 100%; padding: 12px; margin-top: 10px; border-radius: 8px; border: 1px solid #cbd5e1; box-sizing: border-box; }
        button { background: #0284c7; color: white; border: none; font-weight: bold; cursor: pointer; transition: 0.3s; }
        button:hover { background: #0369a1; }
        h2 { font-size: 20px; color: #1e293b; margin-top: 0; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2 class="text-info text-center mb-4">SMS PANEL</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="profile.php">👤 Profile</a>
        <a href="recheck_request.php">🔄 Recheck Requests</a>
        <a href="enter_marks.php">📝 Internal Marks</a>
        <a href="mark_attendance.php">📅 Attendance</a>
        <a href="upload_notes.php">📚 Assignments</a>
        <a href="../auth/logout.php" class="text-danger mt-5">🚪 Logout</a>
    </div>

    <div class="main">
        <div class="box">
            <?php if ($step == 1): ?>
                <h2>Change Password</h2>
                <form method="POST">
                    <input type="email" name="email" placeholder="Registered Email" required>
                    <input type="password" name="old_pass" placeholder="Current Password" required>
                    <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;">
                    <input type="password" name="new_pass" placeholder="New Password" required>
                    <input type="password" name="confirm_pass" placeholder="Confirm New Password" required>
                    <button type="submit" name="send_otp">Send OTP to Mail</button>
                </form>
            <?php else: ?>
                <h2>Verify OTP</h2>
                <p style="font-size: 13px; color: #64748b;">Enter the 6-digit code sent to your email.</p>
                <form method="POST">
                    <input type="text" name="otp" maxlength="6" placeholder="000000" required style="text-align: center; font-size: 20px; letter-spacing: 5px;">
                    <button type="submit" name="verify_and_update">Verify & Update</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>