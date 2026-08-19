<?php
require_once __DIR__ . '/config/database.php';

$regex = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{2B50}]/u';

// Check database
$found_in_db = [];
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    $rows = $pdo->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        foreach ($row as $col => $val) {
            if (is_string($val) && preg_match($regex, $val)) {
                $found_in_db[] = "Table $table, Col $col, ID " . ($row['id'] ?? '?') . " contains emoji: $val";
            }
        }
    }
}

// Check files again using a broader approach
$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
$found_in_files = [];
foreach ($dir as $file) {
    if ($file->isFile() && in_array($file->getExtension(), ['php', 'js', 'html', 'css'])) {
        $content = file_get_contents($file->getPathname());
        if (preg_match($regex, $content, $matches)) {
            $found_in_files[] = $file->getPathname() . " contains emoji: " . implode(" ", $matches[0]);
        }
    }
}

echo json_encode([
    'db' => $found_in_db,
    'files' => $found_in_files
], JSON_PRETTY_PRINT);
?>

