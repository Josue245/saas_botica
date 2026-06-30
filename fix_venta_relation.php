<?php
$path = 'app/Http/Controllers/VentaController.php';
$c = file_get_contents($path);
// Corregir with(['cliente', 'usuario', 'comprobanteSunat']) 
$c = str_replace(
    "->with(['cliente', 'usuario', 'comprobanteSunat'])",
    "->with(['cliente', 'user', 'comprobanteSunat'])",
    $c
);
// Si usa usuario en otro lugar
$c = str_replace("'usuario'", "'user'", $c);
file_put_contents($path, $c);
echo "VentaController: relacion corregida a user\n";
