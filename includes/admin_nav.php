<?php
if (!isset($activePage)) {
    $activePage = '';
}

function adminNavActive($page, $activePage) {
    return $activePage === $page ? 'active' : '';
}
?>
<div class="sidebar">
    <h2>ADMIN SMS</h2>
    <a href="dashboard.php" class="<?php echo adminNavActive('dashboard', $activePage); ?>">📊 Overview</a>
    <a href="student_list.php" class="<?php echo adminNavActive('student_list', $activePage); ?>">🎓 Student List</a>
    <a href="add_faculty.php" class="<?php echo adminNavActive('add_faculty', $activePage); ?>" style="background: #0ea5e9; color: white; margin-top: 10px;">➕ Add Faculty</a>
    <a href="faculty_list.php" class="<?php echo adminNavActive('faculty_list', $activePage); ?>">👨‍🏫 Faculty List</a>
    <a href="assign_subject.php" class="<?php echo adminNavActive('assign_subject', $activePage); ?>">📚 Faculty Assignments</a>
    <a href="send_alerts.php" class="<?php echo adminNavActive('send_alerts', $activePage); ?>" onclick="return confirm('Send emails to all students with low attendance?')" style="background: #f59e0b; color: white; margin-top: 10px;">📢 Send Low Attendance Alerts</a>
    <a href="#">🏫 Departments</a>
    <a href="#">📑 Reports</a>
    <div style="margin-top: auto; padding-top: 20px;">
        <a href="../auth/logout.php" style="color: #fca5a5;">🚪 Logout</a>
    </div>
</div>
