<?php
$path = 'app/Providers/AppServiceProvider.php';
$c = file_get_contents($path);

// Cachear las queries de alertas por 60 segundos por tenant
$old = "View::composer(['partials.sidebar', 'partials.topbar'], function (\$view) {
            if (!app()->bound('tenant') || app('tenant') === null) return;";

$new = "View::composer(['partials.sidebar', 'partials.topbar'], function (\$view) {
            if (!app()->bound('tenant') || app('tenant') === null) return;
            \$tenantId = app('tenant')->id;
            \$cacheKey = 'alertas_sidebar_' . \$tenantId;
            \$cached = \Illuminate\Support\Facades\Cache::remember(\$cacheKey, 60, function () {";

// Buscar el final del View Composer para envolver en cache
if (strpos($c, "Cache::remember(\$cacheKey") !== false) {
    echo "Cache ya aplicado\n";
    exit;
}

// Agregar cache wrapping simple: solo cachear los counts
$search = "View::composer(['partials.sidebar', 'partials.topbar'], function (\$view) {\n            if (!app()->bound('tenant') || app('tenant') === null) return;";

// Verificar que existe el patron
if (strpos($c, $search) === false) {
    echo "Patron no encontrado, buscando alternativa...\n";
    // Buscar sin el if
    $search2 = "View::composer(['partials.sidebar', 'partials.topbar'], function (\$view) {";
    if (strpos($c, $search2) !== false) {
        // Agregar cache a los conteos individualmente
        $c = str_replace(
            '$alertasCount = ',
            '$alertasCount = \Illuminate\Support\Facades\Cache::remember("alertas_count_" . (app()->bound("tenant") ? app("tenant")->id : "0"), 60, fn() => ',
            $c
        );
        file_put_contents($path, $c);
        echo "Cache aplicado en alertasCount\n";
    }
} else {
    file_put_contents($path, $c);
    echo "Fix aplicado\n";
}
