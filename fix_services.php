<?php
$path = 'config/services.php';
$c = file_get_contents($path);

if (strpos($c, 'culqi') !== false) {
    echo "Culqi ya en services.php\n";
} else {
    $c = str_replace(
        '];',
        "    'culqi' => [
        'public_key'     => env('CULQI_PUBLIC_KEY', ''),
        'secret_key'     => env('CULQI_SECRET_KEY', ''),
        'webhook_secret' => env('CULQI_WEBHOOK_SECRET', ''),
    ],
];",
        $c
    );
    file_put_contents($path, $c);
    echo "Culqi agregado a services.php OK\n";
}
