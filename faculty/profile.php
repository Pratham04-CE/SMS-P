<?php
require_once '../includes/role_session.php';
start_role_session('faculty');
include('../config/db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../index1.php"); exit();
}

$f_id = (int)$_SESSION['user_id'];

// Current faculty record for display and safe updates (prepared)
$cur_stmt = mysqli_prepare($conn, "SELECT * FROM faculty_details WHERE id = ?");
mysqli_stmt_bind_param($cur_stmt, "i", $f_id);
mysqli_stmt_execute($cur_stmt);
$cur_res = mysqli_stmt_get_result($cur_stmt);
$current_user = mysqli_fetch_assoc($cur_res);
mysqli_stmt_close($cur_stmt);

// --- UPDATE LOGIC ---
if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $new_faculty_id = mysqli_real_escape_string($conn, $_POST['faculty_id']); // Username update logic
    $old_faculty_id = isset($current_user['faculty_id']) ? $current_user['faculty_id'] : '';
    
    // Check if profile pic is uploaded
    if (!empty($_FILES['profile_pic']['tmp_name'])) {
        $img_data = addslashes(file_get_contents($_FILES['profile_pic']['tmp_name']));
        $upd_stmt = mysqli_prepare($conn, "UPDATE faculty_details SET name = ?, faculty_id = ?, profile_pic = ? WHERE id = ?");
        mysqli_stmt_bind_param($upd_stmt, "sssi", $name, $new_faculty_id, $img_data, $f_id);
    } else {
        $upd_stmt = mysqli_prepare($conn, "UPDATE faculty_details SET name = ?, faculty_id = ? WHERE id = ?");
        mysqli_stmt_bind_param($upd_stmt, "ssi", $name, $new_faculty_id, $f_id);
    }

    $exec = mysqli_stmt_execute($upd_stmt);
    mysqli_stmt_close($upd_stmt);

    if ($exec) {
        if ($old_faculty_id !== '' && $old_faculty_id !== $new_faculty_id) {
            $sub_upd = mysqli_prepare($conn, "UPDATE subjects SET faculty_id = ? WHERE faculty_id = ?");
            mysqli_stmt_bind_param($sub_upd, "ss", $new_faculty_id, $old_faculty_id);
            mysqli_stmt_execute($sub_upd);
            mysqli_stmt_close($sub_upd);
        }
        $_SESSION['user_name'] = $name;
        // Agar faculty_id change hui hai toh subjects table mein bhi handle karna pad sakta hai 
        // par yahan hum simple update kar rahe hain.
        echo "<script>alert('Profile Updated Successfully!'); window.location='profile.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

// Fetch faculty data directly from the faculty_details table
$user = $current_user;

    if(!$user) {
    $user = ['name' => $_SESSION['user_name'], 'email' => 'Not Set', 'faculty_id' => 'Not Set'];
}

// Image Display Fix: Base64 check
$pic = 'https://via.placeholder.com/150';
if (!empty($user['profile_pic'])) {
    $pic = 'data:image/jpeg;base64,' . base64_encode($user['profile_pic']);
}
?>

<?php
$activePage = 'profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php include('../includes/header_pwa.php'); ?>
    <title>My Profile | Faculty SMS</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; display: flex; margin: 0; }
        .sidebar { width: 260px; background: #0f172a; color: white; height: 100vh; position: fixed; padding: 20px; z-index: 1000; }
        .sidebar h2 { color: #38bdf8; font-size: 22px; text-align: center; margin-bottom: 30px; border-bottom: 1px solid #1e293b; padding-bottom: 15px; }
        .main { margin-left: 260px; flex: 1; padding: 20px; display: flex; flex-direction: column; align-items: center; min-height: 100vh; }
        .profile-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 450px; text-align: center; margin-top: 20px; border: 1px solid #e2e8f0; }
        .profile-img { width: 130px; height: 130px; border-radius: 50%; object-fit: cover; border: 4px solid #38bdf8; margin-bottom: 15px; background: #f8fafc; }
        .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 12px; border-radius: 8px; margin-bottom: 5px; transition: 0.3s; font-size: 14px; }
        .sidebar a:hover, .active-link { background: #1e293b; color: #38bdf8; }
        
        label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-top: 15px; margin-bottom: 5px; text-align: left; }
        input, .btn-update { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; box-sizing: border-box; }
        input:focus { outline: none; border-color: #38bdf8; ring: 2px solid #38bdf8; }
        .btn-update { background: #0284c7; color: white; border: none; font-weight: bold; cursor: pointer; transition: 0.2s; margin-top: 25px; font-size: 15px; }
        .btn-update:hover { background: #0369a1; }
    </style>
</head>
<body>
    <?php include('../includes/faculty_nav.php'); ?>

    <div class="main">
        <h2 style="color: #1e293b; width: 100%; max-width: 450px; text-align: left;">Profile Settings</h2>
        
        <div class="profile-card">
            <img src="<?php echo $pic; ?>" class="profile-img" onerror="this.src='https://via.placeholder.com/150'">
            <h3 style="margin:0; color: #0f172a;"><?php echo htmlspecialchars($user['name']); ?></h3>
            <p style="color: #64748b; margin-bottom: 20px; font-size: 14px;"><?php echo htmlspecialchars($user['email']); ?></p>
            
            <form method="POST" enctype="multipart/form-data">
                <label>Display Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

                <label>Username / Faculty ID</label>
                <input type="text" name="faculty_id" value="<?php echo htmlspecialchars($user['faculty_id']); ?>" required>
                
                <label>Change Profile Picture</label>
                <input type="file" name="profile_pic" accept="image/*">
                
                <button type="submit" name="update_profile" class="btn-update">Save All Changes</button>
            </form>
            <a href="change_password.php" style="text-decoration:none; color:#0284c7; font-size:13px; display:block; margin-top:20px; font-weight:600;">🔐 Change Account Password</a>
        </div>
    </div>
</body>
</html>