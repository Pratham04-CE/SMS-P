<?php
require_once '../includes/role_session.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_GET['role'] ?? (isset($_SESSION['role']) ? $_SESSION['role'] : 'student');
logout_role_session($role);

header("Location: ../index1.php");
exit();
?>