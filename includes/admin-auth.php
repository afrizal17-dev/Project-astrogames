<?php
// includes/admin-auth.php
// Security guard untuk memproteksi halaman khusus Administrator

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = "Silakan login sebagai Admin terlebih dahulu.";
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash_error'] = "Akses ditolak. Anda tidak memiliki wewenang untuk mengakses halaman Admin.";
    header("Location: " . BASE_URL . "/index.php");
    exit();
}
