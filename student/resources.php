<?php
require_once '../includes/role_session.php';
start_role_session('student');
include('../config/db_connect.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index1.php");
    exit();
}
?>

<?php
$activePage = 'resources';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resources | SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; font-family: 'Inter', sans-serif; display: flex; }
        .sidebar { width: 260px; background: #1e293b; color: white; min-height: 100vh; position: fixed; padding: 20px; }
        .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 12px; border-radius: 8px; margin-bottom: 5px; }
        .sidebar a.active { background: #334155; color: white; }
        .main-content { margin-left: 260px; padding: 30px; width: 100%; }
    </style>
</head>
<body>
    <?php include('../includes/student_nav.php'); ?>

    <div class="main-content">
        <h2 class="mb-4">📚 Study Materials & Video Links</h2>
        <div class="bg-white rounded shadow-sm overflow-hidden">
            <table class="table m-0 table-hover">
                <thead class="table-light">
                    <tr>
                        <th class="p-3">Resource Title</th>
                        <th>Subject</th>
                        <th class="text-end p-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $m_res = mysqli_query($conn, "SELECT m.*, s.sub_name FROM materials m 
                                                  JOIN subjects s ON m.subject_id = s.id 
                                                  ORDER BY m.upload_date DESC");
                    while($m = mysqli_fetch_assoc($m_res)) {
                        $target = ($m['file_type'] == 'link') ? $m['file'] : "../uploads/notes/".$m['file'];
                    ?>
                    <tr>
                        <td class="p-3"><strong><?php echo $m['title']; ?></strong></td>
                        <td><?php echo $m['sub_name']; ?></td>
                        <td class="text-end p-3">
                            <a href="<?php echo $target; ?>" target="_blank" class="btn btn-sm btn-primary">
                                <?php echo ($m['file_type'] == 'link') ? 'Watch Video' : 'Download File'; ?>
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>