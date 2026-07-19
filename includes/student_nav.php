<?php
if (!isset($activePage)) {
    $activePage = '';
}

function studentNavActive($page, $activePage) {
    return $activePage === $page ? 'active' : '';
}
?>
<div class="sidebar">
    <h3 class="text-info mb-4">GTU | SMS</h3>
    <a href="dashboard.php" class="<?php echo studentNavActive('dashboard', $activePage); ?>">🏠 Dashboard</a>
    <a href="view_results.php" class="<?php echo studentNavActive('view_results', $activePage); ?>">📊 Exam Results</a>
    <a href="check_recheck.php" class="<?php echo studentNavActive('check_recheck', $activePage); ?>">🔄 Recheck Requests</a>
    <a href="resources.php" class="<?php echo studentNavActive('resources', $activePage); ?>">📚 Learning Resources</a>
    <a href="view_profile.php" class="<?php echo studentNavActive('view_profile', $activePage); ?>">👤 View Profile</a>
    <a href="../auth/logout.php" class="text-danger mt-5 d-block text-decoration-none">🚪 Logout</a>
</div>
