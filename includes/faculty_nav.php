<?php
if (!isset($activePage)) {
    $activePage = '';
}

function facultyNavActive($page, $activePage) {
    return $activePage === $page ? 'active' : '';
}
?>
<div class="sidebar">
    <h2 class="text-info text-center mb-4">SMS PANEL</h2>
    <a href="dashboard.php" class="<?php echo facultyNavActive('dashboard', $activePage); ?>">🏠 Dashboard</a>
    <a href="profile.php" class="<?php echo facultyNavActive('profile', $activePage); ?>">👤 Profile</a>
    <a href="recheck_request.php" class="<?php echo facultyNavActive('recheck_request', $activePage); ?>">🔄 Recheck Requests</a>
    <a href="enter_marks.php" class="<?php echo facultyNavActive('enter_marks', $activePage); ?>">📝 Internal Marks</a>
    <a href="mark_attendance.php" class="<?php echo facultyNavActive('mark_attendance', $activePage); ?>">📅 Attendance</a>
    <a href="upload_notes.php" class="<?php echo facultyNavActive('upload_notes', $activePage); ?>">📚 Assignments</a>
    <a href="../auth/logout.php" class="text-danger mt-5">🚪 Logout</a>
</div>
