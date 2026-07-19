<?php
require_once '../includes/role_session.php';
start_role_session('faculty');

include('../config/db_connect.php'); 

// 2. Debugging: Check karein session mein ID hai ya nahi
if (!isset($_SESSION['temp_user_id'])) {
    // Agar session khali hai toh wapas login pe bhej do
    header("Location: faculty_login.php?error=session_lost");
    exit();
}

$error = "";
if (isset($_POST['verify'])) {
    $entered_otp = mysqli_real_escape_string($conn, $_POST['otp']);
    $uid = $_SESSION['temp_user_id'];

    // 3. Exact match check karein (faculty_details table se OTP verify karein)
    $query = "SELECT * FROM faculty_details WHERE id = '$uid'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // 4. OTP Comparison
        if ($user['verification_code'] == $entered_otp) {
            // Sahi OTP: Session finalize karein
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'faculty';
            $_SESSION['user_name'] = $user['name'];
            
            // Temporary session hata dein
            unset($_SESSION['temp_user_id']);
            
            header("Location: dashboard.php"); 
            exit();
        } else {
            $error = "Galat OTP! Database mein '" . $user['verification_code'] . "' hai aur aapne '$entered_otp' dala."; 
        }
    } else {
        $error = "Database mein aapka record nahi mila (ID: $uid).";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Verify OTP | Faculty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light" style="height: 100vh; display: flex; align-items: center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4 card p-4 shadow border-0">
                <h3 class="text-center mb-4">OTP Verification</h3>
                
                <?php if($error): ?>
                    <div class="alert alert-danger small"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted">Enter 6-Digit OTP</label>
                        <input type="text" name="otp" class="form-control text-center fw-bold fs-4" placeholder="000000" maxlength="6" required>
                    </div>
                    <button type="submit" name="verify" class="btn btn-primary w-100">Verify & Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>