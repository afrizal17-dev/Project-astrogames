<?php
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ASTROGAMES' : 'ASTROGAMES - Temukan Game yang Cocok untuk Laptopmu'; ?></title>
    <meta name="description" content="ASTROGAMES adalah marketplace game digital profesional dengan fitur Game Compatibility Checker untuk laptop & PC Anda.">
    <meta name="keywords" content="ASTROGAMES, Digital Game Marketplace, Laptop Game Compatibility Checker, Game PC Murah">
    
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <!-- Custom Style Sheet (Root-relative with BASE_URL to prevent 404) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    
    <!-- Inline CSS to Force Blur (Bypass Cache) -->
    <style>
        .modal-backdrop.show {
            opacity: 1 !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            background-color: rgba(0, 0, 0, 0.6) !important;
        }
    </style>
</head>
<body>
