<?php
// includes/auth.php
// Security guard untuk memastikan user sudah login

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = "Silakan login terlebih dahulu untuk mengakses halaman ini.";
    header("Location: " . BASE_URL . "/login.php");
    exit();
}
