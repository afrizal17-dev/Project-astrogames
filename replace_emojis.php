<?php
$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));

$replacements = [
    '<i class="fas fa-star text-warning"></i>' => '<i class="fas fa-star text-warning"></i>',
    '<i class="fas fa-fire text-danger"></i>' => '<i class="fas fa-fire text-danger"></i>',
    '<i class="fas fa-rocket text-primary"></i>' => '<i class="fas fa-rocket text-primary"></i>',
    '<i class="fas fa-laptop text-info"></i>' => '<i class="fas fa-laptop text-info"></i>',
    '<i class="fas fa-check-circle text-success"></i>' => '<i class="fas fa-check-circle text-success"></i>',
    '<i class="fas fa-times-circle text-danger"></i>' => '<i class="fas fa-times-circle text-danger"></i>',
    '<i class="fas fa-exclamation-triangle text-warning"></i>' => '<i class="fas fa-exclamation-triangle text-warning"></i>',
    '<i class="fas fa-gamepad"></i>' => '<i class="fas fa-gamepad"></i>',
    '<i class="fas fa-shopping-cart"></i>' => '<i class="fas fa-shopping-cart"></i>',
    '<i class="fas fa-ghost"></i>' => '<i class="fas fa-ghost"></i>',
    '<i class="fas fa-credit-card"></i>' => '<i class="fas fa-credit-card"></i>',
    '<i class="fas fa-receipt"></i>' => '<i class="fas fa-receipt"></i>',
    '<i class="fas fa-lightbulb text-warning"></i>' => '<i class="fas fa-lightbulb text-warning"></i>',
    '<i class="fas fa-gift text-danger"></i>' => '<i class="fas fa-gift text-danger"></i>',
    '<i class="fas fa-search"></i>' => '<i class="fas fa-search"></i>',
    '<i class="fas fa-cog"></i>' => '<i class="fas fa-cog"></i>',
    '<i class="fas fa-crown text-warning"></i>' => '<i class="fas fa-crown text-warning"></i>',
    '<i class="fas fa-check-double text-success"></i>' => '<i class="fas fa-check-double text-success"></i>',
    '<i class="fas fa-arrow-down"></i>' => '<i class="fas fa-arrow-down"></i>',
    '<i class="fas fa-glass-cheers text-warning"></i>' => '<i class="fas fa-glass-cheers text-warning"></i>'
];

$regex = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{2B50}]/u';

foreach ($dir as $file) {
    if ($file->isFile() && in_array($file->getExtension(), ['php', 'js', 'html'])) {
        $content = file_get_contents($file->getPathname());
        $changed = false;
        
        foreach ($replacements as $emoji => $icon) {
            if (strpos($content, $emoji) !== false) {
                $content = str_replace($emoji, $icon, $content);
                $changed = true;
            }
        }
        
        if (preg_match($regex, $content)) {
            $content = preg_replace($regex, '', $content);
            $changed = true;
        }
        
        if ($changed) {
            file_put_contents($file->getPathname(), $content);
            echo "Updated " . $file->getPathname() . "\n";
        }
    }
}
?>
