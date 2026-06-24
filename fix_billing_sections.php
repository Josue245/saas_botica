<?php
$path = 'resources/views/billing/index.blade.php';
$c = file_get_contents($path);
$c = str_replace('@section(\'content\')', '@section(\'contenido\')', $c);
$c = str_replace('@endsection', '@endsection', $c);
file_put_contents($path, $c);
echo "billing/index: section corregido OK\n";

// Corregir tambien checkout e historial
foreach (['checkout', 'historial'] as $vista) {
    $p = "resources/views/billing/{$vista}.blade.php";
    $c = file_get_contents($p);
    $c = str_replace('@section(\'content\')', '@section(\'contenido\')', $c);
    file_put_contents($p, $c);
    echo "billing/{$vista}: section corregido OK\n";
}
