<?php
$path = 'config/services.php';
$c = file_get_contents($path);

if (strpos($c, 'nubefact') !== false) {
    echo "Nubefact ya configurado\n";
    exit;
}

$c = str_replace(
    "    'culqi' => [",
    "    'nubefact' => [
        'token' => env('NUBEFACT_TOKEN', ''),
        'ruc'   => env('NUBEFACT_RUC', ''),
        'url'   => env('NUBEFACT_URL', 'https://demo-facturacion.nubefact.com/api/v1'),
    ],
    'culqi' => [",
    $c
);
file_put_contents($path, $c);
echo "Nubefact agregado a services.php OK\n";
