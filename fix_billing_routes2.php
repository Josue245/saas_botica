<?php
$path = 'routes/web.php';
$c = file_get_contents($path);

if (strpos($c, 'BillingController') !== false) {
    echo "Rutas de billing ya existen.\n";
    exit;
}

$import = "use App\Http\Controllers\BillingController;";
$rutas = "
// === BILLING ===
Route::middleware(['auth', 'tenant.active'])->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/checkout/{plan}', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('/billing/pagar/{plan}', [BillingController::class, 'pagar'])->name('billing.pagar');
    Route::get('/billing/historial', [BillingController::class, 'historial'])->name('billing.historial');
});
Route::post('/webhook/culqi', [BillingController::class, 'webhook'])->name('billing.webhook');
";

$c = str_replace(
    "use App\Http\Controllers\Auth\TenantRegistrationController;",
    "use App\Http\Controllers\Auth\TenantRegistrationController;\n" . $import,
    $c
);
$c = rtrim($c) . "\n" . $rutas;
file_put_contents($path, $c);
echo "Rutas de billing agregadas OK\n";
