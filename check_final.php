<?php
$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
$count = 0;
foreach ($dir as $file) {
    if ($file->isFile() && in_array($file->getExtension(), ['php', 'js', 'html', 'css'])) {
        $content = file_get_contents($file->getPathname());
        // Match any character outside basic ASCII, Latin-1, and common punctuation
        if (preg_match_all('/[^\x00-\x7F\xC0-\xFF\x{2013}-\x{2014}\x{2018}-\x{201D}\x{00A9}\x{00AE}]/u', $content, $matches)) {
            $unique = array_unique($matches[0]);
            foreach ($unique as $c) {
                if (mb_strlen($c) > 0 && !preg_match('/\p{L}|\p{M}|\p{P}|\p{Z}/u', $c)) {
                    echo $file->getPathname() . ' : ' . $c . "\n";
                    $count++;
                }
            }
        }
    }
}
if ($count == 0) echo 'No emojis found!';
?>
