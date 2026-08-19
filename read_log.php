<?php
$logFiles = [
    'C:\laragon\tmp\php_errors.log',
    'C:\laragon\usr\local\php\logs\php_error.log',
    __DIR__ . '/error_log',
    __DIR__ . '/error.log'
];

foreach ($logFiles as $file) {
    if (file_exists($file)) {
        echo "LOG FILE FOUND: $file\n";
        $content = file_get_contents($file);
        $lines = explode("\n", $content);
        $lastLines = array_slice($lines, -30);
        echo implode("\n", $lastLines);
        echo "\n----------------------\n";
    }
}
