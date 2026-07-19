<?php
$activePage = 'upload_notes';
session_start();
include('../config/db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../index1.php");
    exit();
}

if (isset($_POST['upload_btn'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $sub_id = (int)$_POST['subject_id'];
    
    // Ensure the session contains the ID of the faculty member
    $faculty_id = (int)$_SESSION['user_id']; 
    $faculty_name = $_SESSION['user_name']; // Send faculty name to store in uploaded_by field
    
    $type = mysqli_real_escape_string($conn, $_POST['material_type']); 
    $final_content = "";

    if ($type == 'file') {
        if (!empty($_FILES['material_file']['name'])) {
            $file_name = $_FILES['material_file']['name'];
            $tmp_name = $_FILES['material_file']['tmp_name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $new_file_name = "note_" . time() . "_" . rand(1000, 9999) . "." . $ext;

            $notes_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "notes";
            if (!is_dir($notes_dir)) {
                mkdir($notes_dir, 0777, true);
            }

            $target_path = $notes_dir . DIRECTORY_SEPARATOR . $new_file_name;
            if (move_uploaded_file($tmp_name, $target_path)) {
                $final_content = $new_file_name;
            } else {
                echo "<script>alert('Upload failed. Please check folder permissions.');</script>";
            }
        }
    } else {
        $final_content = mysqli_real_escape_string($conn, $_POST['material_link']);
    }

    if (!empty($final_content)) {
        // Ab hum uploaded_by mein asali naam bhej rahe hain kyunki constraint hata diya hai
        $query = "INSERT INTO materials (subject_id, teacher_id, title, file, file_type, uploaded_by) 
                  VALUES ('$sub_id', '$faculty_id', '$title', '$final_content', '$type', '$faculty_name')";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Material Uploaded Successfully!'); window.location.href='upload_notes.php';</script>";
        } else {
            echo "SQL Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Upload Materials | Faculty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function toggleInput() {
            var type = document.getElementById('typeSelect').value;
            document.getElementById('fileInputDiv').style.display = (type === 'file') ? 'block' : 'none';
            document.getElementById('linkInputDiv').style.display = (type === 'link') ? 'block' : 'none';
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; display: flex; margin: 0; }
        .sidebar { width: 260px; background: #1e293b; color: white; height: 100vh; position: fixed; padding: 20px; }
        .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 12px; border-radius: 8px; margin-bottom: 5px; transition: 0.3s; font-size: 14px; }
        .sidebar a:hover, .sidebar a.active { background: #334155; color: white; }
        .main { margin-left: 260px; flex: 1; padding: 40px; }
        .card { border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <?php include('../includes/faculty_nav.php'); ?>
    <div class="main">
        <div class="container mt-5">
        <div class="card shadow p-4 mx-auto" style="max-width: 650px; border-radius: 15px;">
            <h4 class="text-primary mb-4 text-center">📤 Share Resources</h4>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Unit 3 PDF" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Subject</label>
                        <select name="subject_id" class="form-select" required>
                            <?php 
                            $subs = mysqli_query($conn, "SELECT id, sub_name FROM subjects");
                            while($s = mysqli_fetch_assoc($subs)) {
                                echo "<option value='".$s['id']."'>".$s['sub_name']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Type</label>
                        <select name="material_type" id="typeSelect" class="form-select" onchange="toggleInput()">
                            <option value="file">📄 File</option>
                            <option value="link">🔗 Link</option>
                        </select>
                    </div>
                </div>
                <div id="fileInputDiv" class="mb-3">
                    <label class="form-label fw-bold">Select File</label>
                    <input type="file" name="material_file" class="form-control">
                </div>
                <div id="linkInputDiv" class="mb-3" style="display:none;">
                    <label class="form-label fw-bold">Paste URL</label>
                    <input type="url" name="material_link" class="form-control" placeholder="https://...">
                </div>
                <button type="submit" name="upload_btn" class="btn btn-primary w-100 py-2 fw-bold">Upload Resource</button>
            </form>
        </div>
        </div>
    </div>
</body>
</html>