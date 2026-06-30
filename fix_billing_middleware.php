<?php
$path = 'routes/web.php';
$c = file_get_contents($path);

$c = str_replace(
    "Route::middleware(['auth', 'tenant.active'])->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/checkout/{plan}', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('/billing/pagar/{plan}', [BillingController::class, 'pagar'])->name('billing.pagar');
    Route::get('/billing/historial', [BillingController::class, 'historial'])->name('billing.historial');
});",
    "Route::middleware(['auth'])->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/checkout/{plan}', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('/billing/pagar/{plan}', [BillingController::class, 'pagar'])->name('billing.pagar');
    Route::get('/billing/historial', [BillingController::class, 'historial'])->name('billing.historial');
});",
    $c
);

file_put_contents($path, $c);
echo "tenant.active removido de billing OK\n";
