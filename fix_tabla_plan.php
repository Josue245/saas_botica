<?php
// Fix 1: nombre de tabla en modelo Plan
$path = 'app/Models/Plan.php';
$c = file_get_contents($path);
if (strpos($c, 'protected $table') === false) {
    $c = str_replace(
        'protected $fillable',
        "protected \$table = 'planes';\n\n    protected \$fillable",
        $c
    );
    file_put_contents($path, $c);
    echo "Plan: table=planes OK\n";
} else {
    echo "Plan: ya tiene table definida\n";
}

// Fix 2: proteger View Composer en AppServiceProvider
$path = 'app/Providers/AppServiceProvider.php';
$c = file_get_contents($path);
if (strpos($c, 'if (!app()->bound') !== false) {
    echo "ViewComposer: ya protegido\n";
} else {
    $old = "View::composer(['partials.sidebar', 'partials.topbar'], function (\$view) {";
    $new = "View::composer(['partials.sidebar', 'partials.topbar'], function (\$view) {\n            if (!app()->bound('tenant') || app('tenant') === null) return;";
    $c = str_replace($old, $new, $c);
    file_put_contents($path, $c);
    echo "ViewComposer: protegido OK\n";
}
