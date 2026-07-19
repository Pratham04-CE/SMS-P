<?php
require_once '../includes/role_session.php';
start_role_session('admin');

// Simple server-side admin login using same hardcoded credentials
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminUser = $_POST['adminUser'] ?? '';
    $adminPass = $_POST['adminPass'] ?? '';
    if ($adminUser === 'admin01' && $adminPass === 'admin123') {
        $_SESSION['role'] = 'admin';
        $_SESSION['user_id'] = 0;
        $_SESSION['user_name'] = 'Administrator';
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid credentials';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Portal | SMS</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { background: #0f172a; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; font-family: 'Inter', sans-serif; }
        .admin-card { background: #1e293b; padding: 40px; border-radius: 16px; width: 100%; max-width: 380px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); border: 1px solid #334155; text-align: center; }
        h2 { color: #f8fafc; margin-bottom: 30px; }
        .input-group { margin-bottom: 20px; text-align: left; }
        label { color: #94a3b8; font-size: 13px; margin-bottom: 8px; display: block; }
        input { width: 100%; padding: 14px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; color: white; box-sizing: border-box; }
        .btn-admin { width: 100%; padding: 14px; background: #0284c7; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .shake { animation: shake 0.5s; }
        @keyframes shake { 0%, 100% {transform: translateX(0);} 25% {transform: translateX(-10px);} 75% {transform: translateX(10px);} }
    </style>
</head>
<body>

<div class="admin-card" id="loginCard">
    <h2>Admin Access</h2>
    <?php if (!empty($error)): ?>
        <div style="color:#ffdddd; background:#4b2121; padding:10px; border-radius:6px; margin-bottom:12px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST" novalidate>
        <div class="input-group">
            <label>Master ID</label>
            <input type="text" id="adminUser" name="adminUser" placeholder="admin01" required>
        </div>
        <div class="input-group">
            <label>Security Password</label>
            <input type="password" id="adminPass" name="adminPass" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-admin" id="loginBtn">Initialize Login</button>
    </form>
</div>

<script>
    const secretUser = "admin01";
    const secretPass = "admin123";

    function validateAdmin() {
        const userField = document.getElementById('adminUser').value;
        const passField = document.getElementById('adminPass').value;
        const btn = document.getElementById('loginBtn');
        const card = document.getElementById('loginCard');

        if (userField === secretUser && passField === secretPass) {
            btn.innerHTML = "Access Granted...";
            btn.style.background = "#22c55e"; 
            
            setTimeout(() => {
                // FIXED: Located in the same folder, so redirect directly
                window.location.href = "dashboard.php"; 
            }, 800);
        } else {
            card.classList.add('shake');
            btn.innerHTML = "Invalid Credentials!";
            btn.style.background = "#ef4444"; 
            setTimeout(() => {
                card.classList.remove('shake');
                btn.innerHTML = "Initialize Login";
                btn.style.background = "#0284c7";
            }, 2000);
        }
    }
</script>
</body>
</html>