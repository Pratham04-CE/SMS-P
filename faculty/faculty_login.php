<?php
require_once '../includes/role_session.php';
start_role_session('faculty');
// '../' ka matlab hai ek folder piche jaana jahan aapka config folder hai
include('../config/db_connect.php'); 
include('faculty_mail_logic.php'); // Agar ye file bhi faculty folder mein hi hai toh sahi hai
if (isset($_POST['login'])) {
    $userid = mysqli_real_escape_string($conn, $_POST['userid']);
    $password = $_POST['password'];

    // Aapke student_db.sql ke mutabik users table hi use hoga
    $query = "SELECT * FROM faculty_details WHERE faculty_id = '$userid' AND role = 'faculty'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $otp = rand(100000, 999999);
            
            // OTP database mein save karo
            mysqli_query($conn, "UPDATE faculty_details SET verification_code = '$otp' WHERE id = '".$user['id']."'");
            
            $_SESSION['temp_user_id'] = $user['id'];
            $_SESSION['temp_role'] = 'faculty';
            
            if (sendFacultyOTP($user['email'], $user['name'], $otp)) {
                header("Location: faculty_verify_otp.php");
                exit();
            } else {
                $error = "OTP bhejne mein error aaya.";
            }
        } else {
            $error = "Invalid Password!";
        }
    } else {
        $error = "Faculty ID nahi mili!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Login | SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4 card p-4 shadow">
                <h2 class="text-center">Faculty Login</h2>
                <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Faculty ID / Enrollment</label>
                        <input type="text" name="userid" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100">Login & Send OTP</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>