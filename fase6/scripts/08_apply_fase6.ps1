# Fase 6 — Multi-sucursal (PowerShell)
# Ejecutar desde C:\saas_botica
$ErrorActionPreference = "Stop"

Write-Host "==> Verificando rama correcta..." -ForegroundColor Cyan
$rama = git branch --show-current
if ($rama -ne "feature/multitenant") {
    Write-Host "ERROR: debes estar en feature/multitenant" -ForegroundColor Red
    exit 1
}

# --- PASO 1: Copiar archivos ---
Write-Host ""
Write-Host "PASO 1: Copiando archivos..." -ForegroundColor Cyan

New-Item -ItemType Directory -Force -Path "resources\views\sucursales" | Out-Null

Copy-Item "fase6\app\Http\Controllers\SucursalController.php" "app\Http\Controllers\" -Force
Copy-Item "fase6\app\Http\Controllers\StockSucursalController.php" "app\Http\Controllers\" -Force
Copy-Item "fase6\resources\views\sucursales\index.blade.php" "resources\views\sucursales\" -Force

Write-Host "   SucursalController, StockSucursalController, vistas copiados" -ForegroundColor Green

# --- PASO 2: Agregar relaciones a Sucursal ---
Write-Host ""
Write-Host "PASO 2: Agregando relaciones ventas/usuarios a Sucursal..." -ForegroundColor Cyan

@'
<?php
$path = 'app/Models/Sucursal.php';
$c = file_get_contents($path);

if (strpos($c, 'ventas()') !== false) {
    echo "Relaciones ya existen\n";
    exit;
}

$c = str_replace(
    "    public function stockProductos(): HasMany
    {
        return \$this->hasMany(StockSucursal::class);
    }",
    "    public function stockProductos(): HasMany
    {
        return \$this->hasMany(StockSucursal::class);
    }

    public function ventas(): HasMany
    {
        return \$this->hasMany(Venta::class);
    }

    public function compras(): HasMany
    {
        return \$this->hasMany(Compra::class);
    }",
    $c
);

// Agregar imports necesarios
if (strpos($c, 'use App\Models\Venta') === false) {
    $c = str_replace(
        "use Illuminate\Database\Eloquent\Model;",
        "use App\Models\Compra;\nuse App\Models\Venta;\nuse Illuminate\Database\Eloquent\Model;",
        $c
    );
}

file_put_contents($path, $c);
echo "Sucursal: relaciones ventas/compras OK\n";
'@ | Set-Content -Path "fix_sucursal_relations.php"
docker compose exec app php fix_sucursal_relations.php

# --- PASO 3: Agregar rutas ---
Write-Host ""
Write-Host "PASO 3: Agregando rutas de sucursales..." -ForegroundColor Cyan

@'
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
'@ | Set-Content -Path "fix_routes_sucursales.php"
docker compose exec app php fix_routes_sucursales.php

# --- PASO 4: Agregar enlace en sidebar ---
Write-Host ""
Write-Host "PASO 4: Agregando sucursales al sidebar..." -ForegroundColor Cyan

@'
<?php
$path = 'resources/views/partials/sidebar.blade.php';
$c = file_get_contents($path);

if (strpos($c, 'sucursales.index') !== false) {
    echo "Sidebar ya tiene sucursales\n";
    exit;
}

$old = '        <a href="{{ route(\'billing.index\') }}" class="{{ $linkBase }} {{ request()->routeIs(\'billing.*\') ? $linkActive : $linkIdle }}">
            <x-icon name="credit-card" /> Mi Suscripción
        </a>';

$new = '        <a href="{{ route(\'billing.index\') }}" class="{{ $linkBase }} {{ request()->routeIs(\'billing.*\') ? $linkActive : $linkIdle }}">
            <x-icon name="credit-card" /> Mi Suscripción
        </a>
        <a href="{{ route(\'sucursales.index\') }}" class="{{ $linkBase }} {{ request()->routeIs(\'sucursales.*\') ? $linkActive : $linkIdle }}">
            <x-icon name="building" /> Sucursales
        </a>';

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "Sidebar: enlace sucursales OK\n";
'@ | Set-Content -Path "fix_sidebar_sucursales.php"
docker compose exec app php fix_sidebar_sucursales.php

# --- PASO 5: Limpiar cache ---
Write-Host ""
Write-Host "PASO 5: Limpiando cache..." -ForegroundColor Cyan
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan cache:clear

# --- PASO 6: Tests de regresion ---
Write-Host ""
Write-Host "PASO 6: Tests de regresion..." -ForegroundColor Cyan
docker compose exec app php artisan test `
    --testsuite=Feature `
    --filter="PosRegressionTest|CompraRegressionTest|CajaRegressionTest"

# --- PASO 7: Commit ---
Write-Host ""
Write-Host "PASO 7: Commiteando..." -ForegroundColor Cyan
git add .
git commit -m "feat(sucursales): Fase 6 - multi-sucursal, stock por sucursal, transferencias"
git push origin feature/multitenant

Write-Host ""
Write-Host "Fase 6 completada." -ForegroundColor Green
Write-Host "   Sucursales: http://localhost:8000/sucursales"
Write-Host "   Stock por sucursal: http://localhost:8000/stock-sucursales"
Write-Host ""
Write-Host "Siguiente: Fase 7 - Facturacion electronica SUNAT"
