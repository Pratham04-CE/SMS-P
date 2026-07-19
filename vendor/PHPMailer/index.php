<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('config/db_connect.php');

if (isset($_POST['login'])) {
    $userid = mysqli_real_escape_string($conn, $_POST['userid']);
    $password = $_POST['password']; 
    $role = $_POST['role'];

    // --- STEP 1: DYNAMIC TABLE SELECTION ---
    // Student ke liye users table aur Faculty ke liye faculty_details table
    if ($role == 'student') {
        $query = "SELECT * FROM users WHERE enrollment_or_id = '$userid' AND role = 'student'";
    } else if ($role == 'faculty') {
        $query = "SELECT * FROM faculty_details WHERE faculty_id = '$userid' AND role = 'faculty'";
    } else {
        // Admin redirect logic pehle se hi HTML onclick mein handle hai
        $query = ""; 
    }

    if (!empty($query)) {
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            
            // --- STEP 2: PASSWORD VERIFY ---
            if (password_verify($password, $user['password'])) {
                
                // --- STEP 3: OTP GENERATION ---
                $login_otp = rand(100000, 999999);
                $email = $user['email'];
                $db_id = $user['id'];

                // OTP update query based on role
                $table_name = ($role == 'student') ? 'users' : 'faculty_details';
                mysqli_query($conn, "UPDATE $table_name SET verification_code = '$login_otp' WHERE id = $db_id");

                // --- STEP 4: SEND MAIL & REDIRECT ---
                include('auth/index_mail_logic.php'); 
                if (sendVerificationMail($email, $user['name'], $login_otp)) {
                    $_SESSION['temp_user_id'] = $db_id;
                    $_SESSION['temp_role'] = $role;

                    echo "<script>alert('Login OTP sent to your email!'); window.location='auth/index_verify_otp.php';</script>";
                    exit();
                } else {
                    echo "<script>alert('Mail Error! Check SMTP settings.');</script>";
                }
            } else {
                echo "<script>alert('Invalid Password!');</script>";
            }
        } else {
            echo "<script>alert('User not found in $role records!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | SMS Portal</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        * { box-sizing: border-box; font-family: 'Inter', sans-serif; margin: 0; padding: 0; outline: none; }
        body {
            background-color: #f8fafc;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 20px 20px;
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh;
        }
        .login-card {
            background: #ffffff; border: 1px solid #e2e8f0;
            padding: 40px; border-radius: 12px; width: 100%; max-width: 400px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }
        h2 { text-align: center; margin-bottom: 25px; font-size: 24px; color: #1e293b; }
        .role-switch {
            display: grid; grid-template-columns: 1fr 1fr 1fr;
            background: #f1f5f9; padding: 4px; border-radius: 8px; margin-bottom: 25px;
        }
        .role-switch label { cursor: pointer; text-align: center; padding: 8px; font-size: 13px; color: #64748b; border-radius: 6px; }
        .role-switch input { display: none; }
        .role-switch input:checked + span {
            background: #ffffff; color: #0284c7; box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: block; margin: -8px; padding: 8px; border-radius: 6px;
        }
        .input-group { margin-bottom: 18px; }
        label.hint { color: #475569; font-size: 12px; font-weight: 600; margin-bottom: 6px; display: block; }
        input {
            width: 100%; padding: 12px; background: #ffffff;
            border: 1px solid #cbd5e1; border-radius: 8px; color: #1e293b; font-size: 14px;
        }
        input:focus { border-color: #0ea5e9; box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1); }
        .btn-login {
            width: 100%; padding: 14px; background: #0284c7; color: #ffffff;
            border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;
        }
        .footer { text-align: center; margin-top: 25px; font-size: 13px; color: #64748b; }
        .footer a { color: #0284c7; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
<div class="login-card">
    <h2>SMS Login</h2>
    <form action="" method="POST">
        <div class="role-switch">
            <label><input type="radio" name="role" value="student" checked><span>Student</span></label>
            <label><input type="radio" name="role" value="faculty"><span>Faculty</span></label>
            <label>
                <input type="radio" name="role" value="admin" onclick="window.location.href='admin/admin_login.php'">
                <span>Admin</span>
            </label>
        </div>
        <div class="input-group">
            <label class="hint">User ID / Enrollment</label>
            <input type="text" name="userid" placeholder="Enter ID" required>
        </div>
        <div class="input-group">
            <label class="hint">Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" name="login" class="btn-login">Sign In</button>
    </form>
    <div class="footer">Don't have an account? <a href="auth/registration.php">Register</a></div>
</div>
</body>
</html>