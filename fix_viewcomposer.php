<?php
$path = 'app/Providers/AppServiceProvider.php';
$c = file_get_contents($path);

// Envolver el View Composer para que solo corra si hay tenant activo
$old = "View::composer(['partials.sidebar', 'partials.topbar'], function (\$view) {";
$new = "View::composer(['partials.sidebar', 'partials.topbar'], function (\$view) {
            if (!app()->bound('tenant') || app('tenant') === null) return;";

if (strpos($c, 'if (!app()->bound') !== false) {
    echo "Fix ya aplicado.\n";
} else {
    $c = str_replace($old, $new, $c);
    file_put_contents($path, $c);
    echo "View Composer protegido OK\n";
}
