<?php
$path = 'bootstrap/app.php';
$c = file_get_contents($path);

// Agregar ResolveTenant y EnsureTenantActive al middleware stack
$middlewareAlias = "->withMiddleware(function (Middleware \$middleware) {";
$middlewareReplace = "->withMiddleware(function (Middleware \$middleware) {
        \$middleware->append(\App\Http\Middleware\ResolveTenant::class);
        \$middleware->alias([
            'tenant.active' => \App\Http\Middleware\EnsureTenantActive::class,
        ]);";

if (strpos($c, 'ResolveTenant') !== false) {
    echo "Middlewares ya registrados.\n";
} else {
    $c = str_replace($middlewareAlias, $middlewareReplace, $c);
    file_put_contents($path, $c);
    echo "Middlewares registrados OK\n";
}
