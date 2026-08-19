<?php
$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
$regex = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{2B50}]/u';
$results = [];
foreach ($dir as $file) {
    if ($file->isFile() && in_array($file->getExtension(), ['php', 'js', 'html'])) {
        $content = file_get_contents($file->getPathname());
        if (preg_match_all($regex, $content, $matches)) {
            $relPath = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $results[$relPath] = array_unique($matches[0]);
        }
    }
}
echo json_encode($results, JSON_PRETTY_PRINT);
?>
