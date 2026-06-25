# Fase 7 — Facturación Electrónica SUNAT (PowerShell)
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

New-Item -ItemType Directory -Force -Path "app\Services\Sunat" | Out-Null
New-Item -ItemType Directory -Force -Path "resources\views\facturacion" | Out-Null

Copy-Item "fase7\app\Services\Sunat\XmlGeneratorService.php" "app\Services\Sunat\" -Force
Copy-Item "fase7\app\Services\Sunat\NubefactService.php" "app\Services\Sunat\" -Force
Copy-Item "fase7\app\Jobs\ComprobanteSunatJob.php" "app\Jobs\" -Force
Copy-Item "fase7\app\Http\Controllers\FacturacionController.php" "app\Http\Controllers\" -Force
Copy-Item "fase7\app\Models\ComprobanteSunat.php" "app\Models\" -Force
Copy-Item "fase7\resources\views\facturacion\index.blade.php" "resources\views\facturacion\" -Force
Copy-Item "fase7\database\migrations\2026_06_24_100000_create_comprobantes_electronicos_table.php" "database\migrations\" -Force

Write-Host "   Todos los archivos copiados OK" -ForegroundColor Green

# --- PASO 2: Migración ---
Write-Host ""
Write-Host "PASO 2: Corriendo migración..." -ForegroundColor Cyan
docker compose exec app php artisan migrate --force
docker compose exec app php artisan migrate --env=testing --force

# --- PASO 3: Configurar Nubefact en services.php ---
Write-Host ""
Write-Host "PASO 3: Configurando Nubefact en services.php..." -ForegroundColor Cyan

@'
<?php
$path = 'config/services.php';
$c = file_get_contents($path);

if (strpos($c, 'nubefact') !== false) {
    echo "Nubefact ya configurado\n";
    exit;
}

$c = str_replace(
    "    'culqi' => [",
    "    'nubefact' => [
        'token' => env('NUBEFACT_TOKEN', ''),
        'ruc'   => env('NUBEFACT_RUC', ''),
        'url'   => env('NUBEFACT_URL', 'https://demo-facturacion.nubefact.com/api/v1'),
    ],
    'culqi' => [",
    $c
);
file_put_contents($path, $c);
echo "Nubefact agregado a services.php OK\n";
'@ | Set-Content -Path "fix_services_nubefact.php"
docker compose exec app php fix_services_nubefact.php

# --- PASO 4: Agregar variables al .env ---
Write-Host ""
Write-Host "PASO 4: Agregando variables de Nubefact al .env..." -ForegroundColor Cyan
$envContent = Get-Content ".env" -Raw
if ($envContent -notmatch "NUBEFACT_TOKEN") {
    Add-Content ".env" "`n# Nubefact OSE (obtener en https://nubefact.com)"
    Add-Content ".env" "NUBEFACT_TOKEN=TU_TOKEN_NUBEFACT"
    Add-Content ".env" "NUBEFACT_RUC=TU_RUC"
    Add-Content ".env" "NUBEFACT_URL=https://demo-facturacion.nubefact.com/api/v1"
    Write-Host "   Variables de Nubefact agregadas (reemplaza con tus credenciales reales)" -ForegroundColor Yellow
} else {
    Write-Host "   Nubefact ya configurado en .env" -ForegroundColor Green
}

# --- PASO 5: Agregar rutas ---
Write-Host ""
Write-Host "PASO 5: Agregando rutas de facturación..." -ForegroundColor Cyan

@'
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
'@ | Set-Content -Path "fix_routes_facturacion.php"
docker compose exec app php fix_routes_facturacion.php

# --- PASO 6: Actualizar sidebar (reemplazar el OFF por enlace real) ---
Write-Host ""
Write-Host "PASO 6: Actualizando sidebar..." -ForegroundColor Cyan

@'
<?php
$path = 'resources/views/partials/sidebar.blade.php';
$c = file_get_contents($path);

$old = '<a href="{{ route(\'facturacion.index\') }}" class="{{ $linkBase }} justify-between {{ request()->routeIs(\'facturacion.*\') ? $linkActive : $linkIdle }}">
            <span class="flex items-center gap-3"><x-icon name="receipt" /> Facturación Electrónica</span>
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-700 text-slate-300">OFF</span>
        </a>';

$new = '<a href="{{ route(\'facturacion.index\') }}" class="{{ $linkBase }} justify-between {{ request()->routeIs(\'facturacion.*\') ? $linkActive : $linkIdle }}">
            <span class="flex items-center gap-3"><x-icon name="receipt" /> Facturación Electrónica</span>
            @if(auth()->user()?->tenant?->puedeUsar(\'facturacion_electronica\'))
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-green-600 text-white">PRO</span>
            @else
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-700 text-slate-300">FREE</span>
            @endif
        </a>';

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "Sidebar: Facturación actualizada OK\n";
'@ | Set-Content -Path "fix_sidebar_facturacion.php"
docker compose exec app php fix_sidebar_facturacion.php

# --- PASO 7: Limpiar cache ---
Write-Host ""
Write-Host "PASO 7: Limpiando cache..." -ForegroundColor Cyan
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan cache:clear

# --- PASO 8: Tests de regresion ---
Write-Host ""
Write-Host "PASO 8: Tests de regresion..." -ForegroundColor Cyan
docker compose exec app php artisan test `
    --testsuite=Feature `
    --filter="PosRegressionTest|CompraRegressionTest|CajaRegressionTest"

# --- PASO 9: Commit ---
Write-Host ""
Write-Host "PASO 9: Commiteando..." -ForegroundColor Cyan
git add .
git commit -m "feat(sunat): Fase 7 - facturacion electronica, XML UBL 2.1, Nubefact OSE, job retry"
git push origin feature/multitenant

Write-Host ""
Write-Host "Fase 7 completada." -ForegroundColor Green
Write-Host "   Facturacion: http://localhost:8000/facturacion"
Write-Host ""
Write-Host "Para activar SUNAT real:"
Write-Host "   1. Registrate en https://nubefact.com (gratis)"
Write-Host "   2. Panel -> Empresa -> Token API"
Write-Host "   3. Reemplaza NUBEFACT_TOKEN y NUBEFACT_RUC en .env"
Write-Host "   4. Para produccion: NUBEFACT_URL=https://facturacion.nubefact.com/api/v1"
Write-Host ""
Write-Host "Siguiente: Fase 8 - Hardening y observabilidad (ultima fase)"
