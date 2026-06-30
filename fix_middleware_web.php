<?php
$path = 'bootstrap/app.php';
$c = file_get_contents($path);

// Quitar ResolveTenant del stack global
$c = str_replace(
    '$middleware->append(\App\Http\Middleware\ResolveTenant::class);' . "\n        ",
    '',
    $c
);

// Agregarlo como middleware del grupo web (después de sesión)
$c = str_replace(
    '$middleware->alias([
            \'tenant.active\' => \App\Http\Middleware\EnsureTenantActive::class,
        ]);',
    '$middleware->alias([
            \'tenant.active\' => \App\Http\Middleware\EnsureTenantActive::class,
        ]);
        $middleware->appendToGroup("web", \App\Http\Middleware\ResolveTenant::class);',
    $c
);

file_put_contents($path, $c);
echo "ResolveTenant movido a grupo web OK\n";
