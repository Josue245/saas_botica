<?php
$path = 'resources/views/billing/index.blade.php';
$c = file_get_contents($path);

// Ver si tiene @extends
if (strpos($c, '@extends') !== false) {
    echo "Vista tiene @extends: " . substr($c, 0, 50) . "\n";
} else {
    echo "Vista NO tiene @extends\n";
}
echo "Primeras 3 lineas:\n";
echo implode("\n", array_slice(explode("\n", $c), 0, 3)) . "\n";
