<?php
echo "<h1>Composer Configuration</h1>";

if (file_exists('composer.json')) {
    $composer = json_decode(file_get_contents('composer.json'), true);
    echo "<pre>" . print_r($composer, true) . "</pre>";
} else {
    echo "❌ composer.json not found";
}

echo "<h2>Vendor Directory Structure</h2>";
function listDirectory($path, $depth = 0) {
    if (!is_dir($path)) {
        echo "❌ Directory not found: $path<br>";
        return;
    }
    
    $items = scandir($path);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $fullPath = $path . '/' . $item;
        $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $depth);
        
        if (is_dir($fullPath)) {
            echo $indent . "📁 $item<br>";
            if ($depth < 3) { // Limit depth to avoid too much output
                listDirectory($fullPath, $depth + 1);
            }
        } else {
            echo $indent . "📄 $item<br>";
        }
    }
}

listDirectory('vendor');
?>