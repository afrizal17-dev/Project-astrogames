<?php
// logout.php - Script Logout Member / Admin
require_once __DIR__ . '/config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
session_start();
$_SESSION['flash_success'] = "Anda berhasil logout dari GameCheck.";
header("Location: index.php");
exit();
