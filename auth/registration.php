<?php 
// Sahi path set kiya hai: root se config folder mein
require_once '../config/db_connect.php'; 
require_once '../includes/role_session.php';
start_role_session('student');

// Math Captcha Verification Logic
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['captcha_answer'])) {
    $num1 = rand(1, 20); $num2 = rand(1, 20);
    $_SESSION['captcha_answer'] = $num1 + $num2;
    $_SESSION['captcha_text'] = "$num1 + $num2 = ?";
}

if(isset($_POST['register'])) {
    $captcha_input = $_POST['captcha'];
    if($captcha_input != $_SESSION['captcha_answer']) {
        echo "<script>alert('Wrong Calculation!');</script>";
    } else {
        $enrollment = mysqli_real_escape_string($conn, $_POST['enrollment']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $otp = rand(100000, 999999);

        try {
            // Duplicate Check
            $check_user = mysqli_query($conn, "SELECT * FROM users WHERE enrollment_or_id = '$enrollment' OR email = '$email'");

            if($check_user !== false && mysqli_num_rows($check_user) > 0) {
                echo "<script>alert('Error: Enrollment or Email already exists!');</script>";
            } else {
                $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $img_data = addslashes(file_get_contents($_FILES['profile_pic']['tmp_name']));

                $sql = "INSERT INTO users (enrollment_or_id, name, dob, email, phone_no, address, password, role, dept_id, semester, profile_pic, verification_code) 
                        VALUES ('$enrollment', '$name', '{$_POST['dob']}', '$email', '{$_POST['phone']}', '{$_POST['address']}', '$pass', 'student', '{$_POST['dept']}', '{$_POST['sem']}', '$img_data', '$otp')";

                if(mysqli_query($conn, $sql)) {
                    // Yahan mail logic ka path bhi sahi rakhein (agar ye file 'auth/' mein hai)
                    include('register_mail_logic.php'); 
                    if(sendRegistrationMail($email, $name, $otp)) {
                        $_SESSION['temp_email'] = $email;
                        echo "<script>alert('OTP sent to your mail!'); window.location='register_verify_otp.php';</script>";
                    } else {
                        echo "<script>alert('Mail sending failed!');</script>";
                    }
                } else {
                    throw new Exception(mysqli_error($conn));
                }
            }
        } catch (Throwable $e) {
            echo "<script>alert('Database Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php include('../includes/header_pwa.php'); ?> 
    <title>Registration | SMS</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        * { box-sizing: border-box; font-family: 'Inter', sans-serif; margin: 0; padding: 0; outline: none; }
        body { background-color: #f8fafc; background-image: radial-gradient(#e2e8f0 1px, transparent 1px); background-size: 20px 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .auth-container { background: #ffffff; border: 1px solid #e2e8f0; padding: 30px; border-radius: 12px; width: 100%; max-width: 680px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05); }
        h2 { text-align: left; margin-bottom: 25px; font-size: 24px; font-weight: 700; color: #1e293b; border-left: 5px solid #0284c7; padding-left: 15px; }
        .reg-form { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .full-width { grid-column: span 2; }
        .field-group { display: flex; flex-direction: column; gap: 6px; }
        label { color: #475569; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        input, select, textarea { width: 100%; padding: 10px 14px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; color: #1e293b; font-size: 14px; transition: 0.2s; }
        input:focus, select:focus, textarea:focus { border-color: #0ea5e9; box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1); }
        .captcha-container { background: #f1f5f9; padding: 12px 18px; border-radius: 10px; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; }
        .captcha-text { font-family: 'Courier New', monospace; font-size: 20px; font-weight: 700; color: #0284c7; }
        .captcha-input { width: 80px !important; text-align: center; height: 35px; background: #fff; }
        .file-box { border: 1px dashed #cbd5e1; padding: 15px; border-radius: 8px; text-align: center; color: #64748b; font-size: 13px; cursor: pointer; transition: 0.3s; position: relative; background: #f8fafc; }
        .file-box:hover { border-color: #0284c7; background: #f0f9ff; color: #0284c7; }
        .submit-btn { padding: 14px; background: #0284c7; color: #ffffff; border: none; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .submit-btn:hover { background: #0369a1; transform: translateY(-1px); }
        .auth-footer { text-align: center; margin-top: 20px; color: #64748b; font-size: 13px; }
        .auth-footer a { color: #0284c7; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="auth-container">
        <h2>Student Registration</h2>
        <form action="" method="POST" enctype="multipart/form-data" class="reg-form">
            <div class="field-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Enter Full Name" required>
            </div>
            <div class="field-group">
                <label>Enrollment No</label>
                <input type="text" name="enrollment" placeholder="Enrollment No" required>
            </div>
            <div class="field-group">
                <label>Date of Birth</label>
                <input type="date" name="dob" required>
            </div>
            <div class="field-group">
                <label>Email ID</label>
                <input type="email" name="email" placeholder="example@mail.com" required>
            </div>
            <div class="field-group">
                <label>Phone Number</label>
                <input type="text" name="phone" placeholder="+91..." required>
            </div>
            <div class="field-group">
                <label>Department</label>
                <select name="dept" required>
                    <option value="1">CE</option>
                    <option value="2">IT</option>
                    <option value="3">Civil</option>
                </select>
            </div>
            <div class="field-group">
                <label>Semester</label>
                <select name="sem" required>
                    <?php for($i=1;$i<=8;$i++) echo "<option value='$i'>Sem $i</option>"; ?>
                </select>
            </div>
            <div class="field-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <div class="field-group full-width">
                <label>Residential Address</label>
                <textarea name="address" rows="2" placeholder="Your Address..." required></textarea>
            </div>
            <div class="full-width captcha-container">
                <div>
                    <span style="color: #64748b; font-size: 10px; display: block; font-weight: 600;">SECURITY CHECK</span>
                    <span class="captcha-text"><?php echo $_SESSION['captcha_text']; ?></span>
                </div>
                <input type="number" name="captcha" class="captcha-input" placeholder="?" required>
            </div>
            <div class="field-group full-width">
                <label>Profile Picture</label>
                <div class="file-box" id="file-box">
                    <span id="file-label">📁 Click to Select Image</span>
                    <input type="file" name="profile_pic" id="profile_pic" accept="image/*" style="position:absolute; opacity:0; left:0; top:0; width:100%; height:100%; cursor:pointer;" required>
                </div>
            </div>
            <button type="submit" name="register" class="submit-btn full-width">Create Account</button>
        </form>
        <div class="auth-footer">Already have an account? <a href="../index1.php">Login here</a></div>
    </div>
    <script>
        document.getElementById('profile_pic').onchange = function () {
            var fileName = this.value.split('\\').pop();
            document.getElementById('file-label').innerHTML = "✅ Selected: " + fileName;
            document.getElementById('file-box').style.borderColor = "#0284c7";
            document.getElementById('file-box').style.background = "#f0f9ff";
        };
    </script>
</body>
</html>