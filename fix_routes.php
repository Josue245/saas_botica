<?php
$path = 'routes/web.php';
$c = file_get_contents($path);

$import = "use App\Http\Controllers\Auth\TenantRegistrationController;";
$rutas = "
// === REGISTRO DE NUEVO TENANT (onboarding) ===
Route::get('/register', [TenantRegistrationController::class, 'create'])->name('register.tenant.form');
Route::post('/register', [TenantRegistrationController::class, 'store'])->name('register.tenant');
Route::get('/suspendido', fn() => view('billing.suspendido'))->name('billing.suspendido');
";

if (strpos($c, 'TenantRegistrationController') !== false) {
    echo "Rutas de tenant ya existen.\n";
} else {
    // Agregar import al inicio junto a los otros use
    $c = str_replace("<?php", "<?php\n" . $import, $c);
    // Agregar rutas al final del archivo
    $c = rtrim($c) . "\n" . $rutas;
    file_put_contents($path, $c);
    echo "Rutas agregadas OK\n";
}
