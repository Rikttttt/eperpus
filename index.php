<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];

// Redirect berdasarkan role
if ($role == 'admin') {
    header("Location: admin/index.php");
    exit();
} else {
    header("Location: user/index.php");
    exit();
}
?>