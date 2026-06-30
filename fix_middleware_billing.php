<?php
$path = 'bootstrap/app.php';
$c = file_get_contents($path);

if (strpos($c, 'plan.limit') !== false) {
    echo "CheckPlanLimits ya registrado.\n";
} else {
    $c = str_replace(
        "'tenant.active' => \App\Http\Middleware\EnsureTenantActive::class,",
        "'tenant.active' => \App\Http\Middleware\EnsureTenantActive::class,
            'plan.limit'   => \App\Http\Middleware\CheckPlanLimits::class,",
        $c
    );
    file_put_contents($path, $c);
    echo "CheckPlanLimits registrado OK\n";
}
