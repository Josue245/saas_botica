<?php
$path = 'app/Providers/AppServiceProvider.php';
$c = file_get_contents($path);
if (strpos($c, 'TenantManager') === false) {
    $c = str_replace(
        'use Illuminate\Support\ServiceProvider;',
        "use App\Services\TenantManager;\nuse Illuminate\Support\ServiceProvider;",
        $c
    );
    $c = str_replace(
        "public function register(): void\n    {",
        "public function register(): void\n    {\n        \$this->app->singleton(TenantManager::class, fn() => new TenantManager());",
        $c
    );
    file_put_contents($path, $c);
    echo "TenantManager registrado OK\n";
} else {
    echo "Ya estaba registrado\n";
}
