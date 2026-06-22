<?php
$path = 'config/app.php';
$c = file_get_contents($path);

if (strpos($c, 'base_domain') !== false) {
    echo "base_domain ya existe.\n";
} else {
    $c = str_replace(
        "'name' => env('APP_NAME', 'Laravel'),",
        "'name' => env('APP_NAME', 'Laravel'),\n    'base_domain' => env('APP_BASE_DOMAIN', 'mibotica.pe'),",
        $c
    );
    file_put_contents($path, $c);
    echo "base_domain agregado OK\n";
}
