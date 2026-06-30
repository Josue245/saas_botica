<?php
$path = 'bootstrap/app.php';
$c = file_get_contents($path);

if (strpos($c, 'webhook/culqi') !== false) {
    echo "Webhook ya excluido de CSRF\n";
} else {
    $c = str_replace(
        '$middleware->appendToGroup("web", \App\Http\Middleware\ResolveTenant::class);',
        '$middleware->appendToGroup("web", \App\Http\Middleware\ResolveTenant::class);
        $middleware->validateCsrfTokens(except: ["webhook/culqi"]);',
        $c
    );
    file_put_contents($path, $c);
    echo "Webhook excluido de CSRF OK\n";
}
