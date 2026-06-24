<?php
$path = 'routes/web.php';
$c = file_get_contents($path);

if (strpos($c, 'SucursalController') !== false) {
    echo "Rutas ya existen\n";
    exit;
}

$import = "use App\Http\Controllers\SucursalController;\nuse App\Http\Controllers\StockSucursalController;";
$rutas = "
// === SUCURSALES ===
Route::middleware(['auth'])->group(function () {
    Route::get('/sucursales', [SucursalController::class, 'index'])->name('sucursales.index');
    Route::post('/sucursales', [SucursalController::class, 'store'])->name('sucursales.store');
    Route::patch('/sucursales/{sucursal}', [SucursalController::class, 'update'])->name('sucursales.update');
    Route::delete('/sucursales/{sucursal}', [SucursalController::class, 'destroy'])->name('sucursales.destroy');
    Route::patch('/sucursales/{sucursal}/cambiar', [SucursalController::class, 'cambiar'])->name('sucursales.cambiar');

    // Stock por sucursal
    Route::get('/stock-sucursales', [StockSucursalController::class, 'index'])->name('stock.sucursales.index');
    Route::post('/stock-sucursales/transferir', [StockSucursalController::class, 'transferir'])->name('stock.sucursales.transferir');
    Route::post('/stock-sucursales/poblar', [StockSucursalController::class, 'poblarDesdeProductos'])->name('stock.sucursales.poblar');
});
";

$c = str_replace(
    "use App\Http\Controllers\BillingController;",
    "use App\Http\Controllers\BillingController;\n" . $import,
    $c
);
$c = rtrim($c) . "\n" . $rutas;
file_put_contents($path, $c);
echo "Rutas de sucursales agregadas OK\n";
