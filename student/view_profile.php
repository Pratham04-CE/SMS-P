<?php
require_once '../includes/role_session.php';
start_role_session('student');
include('../config/db_connect.php');
include('../auth/profile_update_mailer.php'); 

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index1.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// --- 📝 UPDATE LOGIC WITH OTP ---
if(isset($_POST['update_field'])) {
    $field = $_POST['field_name'];
    $new_val = mysqli_real_escape_string($conn, $_POST['new_value']);
    
    $curr_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT email, name FROM users WHERE id = '$user_id'"));

    // Logic for Email, Phone, and Password (All need OTP)
    if($field == 'email' || $field == 'phone_no' || $field == 'password') {
        $otp = rand(100000, 999999);
        $_SESSION['pending_field'] = $field;
        $_SESSION['pending_value'] = ($field == 'password') ? password_hash($new_val, PASSWORD_DEFAULT) : $new_val;
        $_SESSION['profile_otp'] = $otp;

        // Important: Password change OTP always goes to the OLD registered email
        $recipient = ($field == 'email') ? $new_val : $curr_user['email'];
        $subject_text = ($field == 'password') ? "Password Reset" : ucfirst($field);

        if(sendProfileOTP($recipient, $curr_user['name'], $otp, $subject_text)) { 
            echo "<script>alert('Verification OTP sent to: $recipient'); window.location='verify_profile_otp.php';</script>";
            exit();
        } else {
            echo "<script>alert('Mailer Error: Could not send OTP.');</script>";
        }
    } else {
        // Normal fields update
        $protected = ['semester', 'enrollment_or_id', 'dept_id'];
        if(!in_array($field, $protected)) {
            $update_sql = "UPDATE users SET $field = '$new_val' WHERE id = '$user_id'";
            if(mysqli_query($conn, $update_sql)) {
                echo "<script>alert('Details Updated Successfully!'); window.location='view_profile.php';</script>";
            }
        }
    }
}

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'"));
$profile_pic = !empty($user['profile_pic']) ? 'data:image/jpeg;base64,' . base64_encode($user['profile_pic']) : 'https://via.placeholder.com/150';
?>

<?php
$activePage = 'view_profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account | SMS Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; font-family: 'Segoe UI', sans-serif; display: flex; }
        .sidebar { width: 260px; background: #1e293b; color: white; min-height: 100vh; position: fixed; padding: 20px; }
        .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 12px; border-radius: 8px; margin-bottom: 5px; }
        .sidebar a.active { background: #334155; color: white; }
        .main-content { margin-left: 260px; padding: 30px; width: 100%; }
        .profile-card { background: white; border-radius: 20px; padding: 35px; box-shadow: 0 10px 15px rgba(0,0,0,0.05); }
        .info-box { background: #f8fafc; padding: 18px; border-radius: 12px; border: 1px solid #e2e8f0; position: relative; margin-bottom: 15px; }
        .label-text { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; }
        .value-text { font-size: 15px; color: #1e293b; font-weight: 600; margin-top: 4px; }
        .edit-link { position: absolute; top: 15px; right: 20px; font-size: 11px; color: #0ea5e9; cursor: pointer; font-weight: bold; }
        .lock-icon { position: absolute; top: 15px; right: 20px; font-size: 12px; color: #cbd5e1; }
        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000; }
        .modal-content { background:white; padding:25px; border-radius:12px; width:380px; }
    </style>
</head>
<body>
    <?php include('../includes/student_nav.php'); ?>

    <div class="main-content">
        <h2 class="mb-4 fw-bold">👤 My Account Settings</h2>
        <div class="profile-card">
            <div class="text-center mb-4">
                <img src="<?php echo $profile_pic; ?>" style="width:140px; height:140px; border-radius:50%; object-fit:cover; border:5px solid #f1f5f9;">
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="label-text">Full Name</span>
                        <p class="value-text"><?php echo $user['name']; ?></p>
                        <span class="edit-link" onclick="openEdit('name', '<?php echo $user['name']; ?>')">EDIT</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="label-text">Enrollment No</span>
                        <p class="value-text text-muted"><?php echo $user['enrollment_or_id']; ?></p>
                        <span class="lock-icon">🔒 LOCKED</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="label-text">Department</span>
                        <p class="value-text text-muted"><?php echo ($user['dept_id'] == 1) ? 'Computer Engineering' : 'Information Technology'; ?></p>
                        <span class="lock-icon">🔒 LOCKED</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="label-text">Semester</span>
                        <p class="value-text text-muted">Semester <?php echo $user['semester']; ?></p>
                        <span class="lock-icon">🔒 LOCKED</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="label-text">Email Address</span>
                        <p class="value-text"><?php echo $user['email']; ?></p>
                        <span class="edit-link" onclick="openEdit('email', '<?php echo $user['email']; ?>')">UPDATE</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="label-text">Mobile Number</span>
                        <p class="value-text"><?php echo $user['phone_no']; ?></p>
                        <span class="edit-link" onclick="openEdit('phone_no', '<?php echo $user['phone_no']; ?>')">UPDATE</span>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="info-box">
                        <span class="label-text">Residential Address</span>
                        <p class="value-text"><?php echo !empty($user['address']) ? $user['address'] : 'Address not set'; ?></p>
                        <span class="edit-link" onclick="openEdit('address', '<?php echo $user['address']; ?>')">EDIT</span>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="info-box border-warning" style="background: #fffbeb;">
                        <span class="label-text text-warning">Account Security</span>
                        <p class="value-text">••••••••••••</p>
                        <span class="edit-link text-warning" onclick="openPasswordEdit()">CHANGE PASSWORD</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content shadow">
            <h5 id="modalTitle" class="fw-bold mb-3">Update Info</h5>
            <form method="POST">
                <input type="hidden" name="field_name" id="field_name">
                <div class="mb-4">
                    <label class="small fw-bold text-muted mb-1" id="inputLabel">Enter New Value</label>
                    <input type="text" name="new_value" id="new_value" class="form-control" required>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" onclick="closeEdit()" class="btn btn-light">Cancel</button>
                    <button type="submit" name="update_field" class="btn btn-primary px-4">Verify & Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEdit(field, value) {
            document.getElementById('field_name').value = field;
            document.getElementById('new_value').value = value;
            document.getElementById('new_value').type = "text";
            document.getElementById('modalTitle').innerText = "Update " + field.replace('_', ' ').toUpperCase();
            document.getElementById('inputLabel').innerText = "Enter New Value";
            document.getElementById('editModal').style.display = 'flex';
        }

        function openPasswordEdit() {
            document.getElementById('field_name').value = 'password';
            document.getElementById('new_value').value = "";
            document.getElementById('new_value').type = "password";
            document.getElementById('modalTitle').innerText = "Change Account Password";
            document.getElementById('inputLabel').innerText = "Enter New Password";
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEdit() { document.getElementById('editModal').style.display = 'none'; }
    </script>
</body>
</html>