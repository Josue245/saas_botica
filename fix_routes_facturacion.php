<?php
$path = 'routes/web.php';
$c = file_get_contents($path);

if (strpos($c, 'FacturacionController') !== false) {
    echo "Rutas ya existen\n";
    exit;
}

$import = "use App\Http\Controllers\FacturacionController;";
$rutas = "
// === FACTURACIÓN ELECTRÓNICA ===
Route::middleware(['auth'])->group(function () {
    Route::get('/facturacion', [FacturacionController::class, 'index'])->name('facturacion.index');
    Route::post('/facturacion/emitir/{venta}', [FacturacionController::class, 'emitir'])->name('facturacion.emitir');
    Route::patch('/facturacion/reenviar/{comprobante}', [FacturacionController::class, 'reenviar'])->name('facturacion.reenviar');
    Route::get('/facturacion/xml/{comprobante}', [FacturacionController::class, 'verXml'])->name('facturacion.xml');
});
";

$c = str_replace(
    "use App\Http\Controllers\BillingController;",
    "use App\Http\Controllers\BillingController;\n" . $import,
    $c
);
$c = rtrim($c) . "\n" . $rutas;
file_put_contents($path, $c);
echo "Rutas de facturacion agregadas OK\n";
