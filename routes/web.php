<?php
use App\Http\Controllers\Auth\TenantRegistrationController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\StockSucursalController;

use App\Http\Controllers\AlertaController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RespaldoController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Web — Mi Botica (SaaS)
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('dashboard'));

// --- Autenticación ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')->name('logout');

// --- Aplicación (requiere sesión) ---
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Comercio & Ventas
    Route::get('/caja', [CajaController::class, 'index'])->name('caja.index');
    Route::post('/caja/abrir', [CajaController::class, 'abrir'])->name('caja.abrir');
    Route::patch('/caja/cerrar', [CajaController::class, 'cerrar'])->name('caja.cerrar');
    Route::get('/caja/movimientos', [CajaController::class, 'movimientos'])->name('caja.movimientos');
    Route::post('/caja/movimientos', [CajaController::class, 'guardarMovimiento'])->name('caja.movimientos.store');
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
    Route::get('/pos/ticket/{venta}', [PosController::class, 'ticket'])->name('pos.ticket');
    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::patch('/ventas/{venta}/anular', [VentaController::class, 'anular'])->name('ventas.anular');
    Route::resource('clientes', ClienteController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    // Logística & Inventario
    Route::resource('productos', ProductoController::class)->except(['show']);
    Route::resource('categorias', CategoriaController::class)->except(['show', 'create', 'edit']);
    Route::resource('compras', CompraController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('proveedores', ProveedorController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['proveedores' => 'proveedor']);
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
    Route::get('/inventario/lotes', [InventarioController::class, 'lotes'])->name('inventario.lotes');
    Route::get('/inventario/ajustes', [InventarioController::class, 'ajustes'])->name('inventario.ajustes');
    Route::post('/inventario/ajustes', [InventarioController::class, 'guardarAjuste'])->name('inventario.ajustes.store');

    // Gerencia & Control
    Route::get('/alertas', [AlertaController::class, 'index'])->name('alertas.index');
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/export', [ReporteController::class, 'exportar'])->name('reportes.export');

    // Ajustes & Sistema
    Route::resource('personal', PersonalController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['personal' => 'usuario'])
        ->middleware('role:admin');
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');
    Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
    Route::middleware('role:admin')->group(function () {
        Route::get('/respaldos', [RespaldoController::class, 'index'])->name('respaldos.index');
        Route::post('/respaldos', [RespaldoController::class, 'generar'])->name('respaldos.generar');
        Route::get('/respaldos/{archivo}/descargar', [RespaldoController::class, 'descargar'])->name('respaldos.descargar');
    });
    Route::get('/facturacion', ModuloController::class)->name('facturacion.index');
});

// === REGISTRO DE NUEVO TENANT (onboarding) ===
Route::get('/register', [TenantRegistrationController::class, 'create'])->name('register.tenant.form');
Route::post('/register', [TenantRegistrationController::class, 'store'])->name('register.tenant');
Route::get('/suspendido', fn() => view('billing.suspendido'))->name('billing.suspendido');

// === BILLING ===
Route::middleware(['auth'])->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/checkout/{plan}', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('/billing/pagar/{plan}', [BillingController::class, 'pagar'])->name('billing.pagar');
    Route::get('/billing/historial', [BillingController::class, 'historial'])->name('billing.historial');
});
Route::post('/webhook/culqi', [BillingController::class, 'webhook'])->name('billing.webhook');

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
