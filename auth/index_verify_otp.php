<?php
require_once '../includes/role_session.php';
include('../config/db_connect.php');

start_role_session();
$temp_role = $_SESSION['temp_role'] ?? 'student';
start_role_session($temp_role);

if (!isset($_SESSION['temp_user_id'])) {
    header("Location: ../index1.php");
    exit();
}

$error = "";
$temp_role = $_SESSION['temp_role'] ?? 'student';
$role_label = ($temp_role === 'faculty') ? 'Faculty' : 'Student';
$role_icon = ($temp_role === 'faculty') ? '👩‍🏫' : '🎓';
$role_title = ($temp_role === 'faculty') ? 'Faculty OTP Verification' : 'Student OTP Verification';
$role_subtitle = ($temp_role === 'faculty') ? 'Enter the secure code sent to your faculty email to continue.' : 'Enter the secure code sent to your registered email to continue.';
$accent_color = ($temp_role === 'faculty') ? '#7c3aed' : '#0284c7';
$accent_light = ($temp_role === 'faculty') ? '#ede9fe' : '#e0f2fe';

if (isset($_POST['verify_otp'])) {
    $entered_otp = mysqli_real_escape_string($conn, $_POST['otp']);
    $temp_id = $_SESSION['temp_user_id'];
    $temp_role = $_SESSION['temp_role'];

    $table = ($temp_role == 'student') ? 'users' : 'faculty_details';
    
    $query = "SELECT * FROM $table WHERE id = '$temp_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if ($user['verification_code'] == $entered_otp) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $temp_role;
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['login_success'] = "Welcome back, " . $user['name'] . "!";

            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_role']);

            if ($temp_role == 'student') {
                header("Location: ../student/dashboard.php");
            } else {
                header("Location: ../faculty/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid OTP. Please check your email.";
        }
    } else {
        $error = "Session error: User record not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP | SMS</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            padding: 20px;
        }
        .otp-card {
            background: white;
            padding: 32px 28px;
            border-radius: 18px;
            box-shadow: 0 15px 40px rgba(15, 23, 42, 0.12);
            width: 100%;
            max-width: 420px;
            text-align: center;
            border-top: 5px solid <?php echo $accent_color; ?>;
        }
        .role-badge {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 999px;
            background: <?php echo $accent_light; ?>;
            color: <?php echo $accent_color; ?>;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 14px;
        }
        h2 {
            margin: 0 0 8px;
            color: #0f172a;
            font-size: 24px;
        }
        .subtitle {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 18px;
            line-height: 1.5;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 16px 0;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            text-align: center;
            font-size: 20px;
            letter-spacing: 5px;
            outline: none;
        }
        input:focus {
            border-color: <?php echo $accent_color; ?>;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
        }
        button {
            width: 100%;
            padding: 12px;
            background: <?php echo $accent_color; ?>;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        button:hover { transform: translateY(-1px); }
        .error-msg {
            color: #ef4444;
            font-size: 13px;
            margin-bottom: 10px;
            background: #fef2f2;
            padding: 8px 10px;
            border-radius: 8px;
        }
        .link-row {
            margin-top: 16px;
            font-size: 13px;
        }
        .link-row a {
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="otp-card">
        <div class="role-badge"><?php echo $role_icon; ?> <?php echo $role_label; ?> Login</div>
        <h2><?php echo $role_title; ?></h2>
        <p class="subtitle"><?php echo $role_subtitle; ?></p>

        <?php if($error) echo "<p class='error-msg'>$error</p>"; ?>

        <form method="POST">
            <input type="text" name="otp" placeholder="000000" maxlength="6" required autofocus>
            <button type="submit" name="verify_otp">Verify & Continue</button>
        </form>
        <p class="link-row"><a href="../index1.php">Back to Login</a></p>
    </div>
</body>
</html>