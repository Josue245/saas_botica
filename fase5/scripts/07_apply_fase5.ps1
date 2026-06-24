# Fase 5 — Billing con Culqi + límites por plan (PowerShell)
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

New-Item -ItemType Directory -Force -Path "app\Jobs" | Out-Null

Copy-Item "fase5\app\Services\BillingService.php" "app\Services\" -Force
Copy-Item "fase5\app\Http\Controllers\BillingController.php" "app\Http\Controllers\" -Force
Copy-Item "fase5\app\Http\Middleware\CheckPlanLimits.php" "app\Http\Middleware\" -Force
Copy-Item "fase5\app\Jobs\CheckExpiredSubscriptionsJob.php" "app\Jobs\" -Force
Copy-Item "fase5\resources\views\billing\index.blade.php" "resources\views\billing\" -Force
Copy-Item "fase5\resources\views\billing\checkout.blade.php" "resources\views\billing\" -Force
Copy-Item "fase5\resources\views\billing\historial.blade.php" "resources\views\billing\" -Force

Write-Host "   BillingService, BillingController, CheckPlanLimits, vistas copiados" -ForegroundColor Green

# --- PASO 2: Registrar middleware CheckPlanLimits ---
Write-Host ""
Write-Host "PASO 2: Registrando CheckPlanLimits..." -ForegroundColor Cyan

@'
<?php
$path = 'bootstrap/app.php';
$c = file_get_contents($path);

if (strpos($c, 'plan.limit') !== false) {
    echo "CheckPlanLimits ya registrado.\n";
} else {
    $c = str_replace(
        "'tenant.active' => \App\Http\Middleware\EnsureTenantActive::class,",
        "'tenant.active' => \App\Http\Middleware\EnsureTenantActive::class,
            'plan.limit'   => \App\Http\Middleware\CheckPlanLimits::class,",
        $c
    );
    file_put_contents($path, $c);
    echo "CheckPlanLimits registrado OK\n";
}
'@ | Set-Content -Path "fix_middleware_billing.php"
docker compose exec app php fix_middleware_billing.php

# --- PASO 3: Agregar rutas de billing ---
Write-Host ""
Write-Host "PASO 3: Agregando rutas de billing..." -ForegroundColor Cyan

@'
<?php
$path = 'routes/web.php';
$c = file_get_contents($path);

if (strpos($c, 'billing') !== false) {
    echo "Rutas de billing ya existen.\n";
} else {
    $import = "use App\Http\Controllers\BillingController;";
    $rutas = "
// === BILLING ===
Route::middleware(['auth', 'tenant.active'])->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/checkout/{plan}', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('/billing/pagar/{plan}', [BillingController::class, 'pagar'])->name('billing.pagar');
    Route::get('/billing/historial', [BillingController::class, 'historial'])->name('billing.historial');
});
// Webhook de Culqi (sin CSRF)
Route::post('/webhook/culqi', [BillingController::class, 'webhook'])->name('billing.webhook');
";
    $c = str_replace("use App\Http\Controllers\Auth\TenantRegistrationController;",
        "use App\Http\Controllers\Auth\TenantRegistrationController;\n" . $import, $c);
    $c = rtrim($c) . "\n" . $rutas;
    file_put_contents($path, $c);
    echo "Rutas de billing agregadas OK\n";
}
'@ | Set-Content -Path "fix_routes_billing.php"
docker compose exec app php fix_routes_billing.php

# --- PASO 4: Agregar credenciales de Culqi al .env ---
Write-Host ""
Write-Host "PASO 4: Configurando Culqi en .env..." -ForegroundColor Cyan
$envContent = Get-Content ".env" -Raw
if ($envContent -notmatch "CULQI_PUBLIC_KEY") {
    Add-Content ".env" "`n# Culqi (obtener en https://culqi.com)"
    Add-Content ".env" "CULQI_PUBLIC_KEY=pk_test_TU_LLAVE_PUBLICA"
    Add-Content ".env" "CULQI_SECRET_KEY=sk_test_TU_LLAVE_SECRETA"
    Add-Content ".env" "CULQI_WEBHOOK_SECRET=TU_WEBHOOK_SECRET"
    Write-Host "   Credenciales de Culqi agregadas al .env (reemplaza con tus llaves reales)" -ForegroundColor Yellow
} else {
    Write-Host "   Culqi ya configurado" -ForegroundColor Green
}

# --- PASO 5: Agregar Culqi a config/services.php ---
Write-Host ""
Write-Host "PASO 5: Configurando services.php..." -ForegroundColor Cyan

@'
<?php
$path = 'config/services.php';
$c = file_get_contents($path);

if (strpos($c, 'culqi') !== false) {
    echo "Culqi ya en services.php\n";
} else {
    $c = str_replace(
        '];',
        "    'culqi' => [
        'public_key'     => env('CULQI_PUBLIC_KEY', ''),
        'secret_key'     => env('CULQI_SECRET_KEY', ''),
        'webhook_secret' => env('CULQI_WEBHOOK_SECRET', ''),
    ],
];",
        $c
    );
    file_put_contents($path, $c);
    echo "Culqi agregado a services.php OK\n";
}
'@ | Set-Content -Path "fix_services.php"
docker compose exec app php fix_services.php

# --- PASO 6: Excluir webhook de CSRF ---
Write-Host ""
Write-Host "PASO 6: Excluyendo webhook de CSRF..." -ForegroundColor Cyan

@'
<?php
$path = 'bootstrap/app.php';
$c = file_get_contents($path);

if (strpos($c, 'webhook/culqi') !== false) {
    echo "Webhook ya excluido de CSRF\n";
} else {
    $c = str_replace(
        '$middleware->appendToGroup("web", \App\Http\Middleware\ResolveTenant::class);',
        '$middleware->appendToGroup("web", \App\Http\Middleware\ResolveTenant::class);
        $middleware->validateCsrfTokens(except: ["webhook/culqi"]);',
        $c
    );
    file_put_contents($path, $c);
    echo "Webhook excluido de CSRF OK\n";
}
'@ | Set-Content -Path "fix_csrf_webhook.php"
docker compose exec app php fix_csrf_webhook.php

# --- PASO 7: Agregar job al scheduler ---
Write-Host ""
Write-Host "PASO 7: Registrando job en scheduler..." -ForegroundColor Cyan

@'
<?php
$path = 'routes/console.php';
$c = file_get_contents($path);

if (strpos($c, 'CheckExpiredSubscriptionsJob') !== false) {
    echo "Job ya registrado\n";
} else {
    $c = str_replace(
        "<?php",
        "<?php\nuse App\Jobs\CheckExpiredSubscriptionsJob;",
        $c
    );
    $c = rtrim($c) . "\n\nSchedule::job(new CheckExpiredSubscriptionsJob)->dailyAt('02:00');\n";
    file_put_contents($path, $c);
    echo "Job registrado en scheduler OK\n";
}
'@ | Set-Content -Path "fix_scheduler.php"
docker compose exec app php fix_scheduler.php

# --- PASO 8: Limpiar cache ---
Write-Host ""
Write-Host "PASO 8: Limpiando cache..." -ForegroundColor Cyan
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan cache:clear

# --- PASO 9: Tests de regresion ---
Write-Host ""
Write-Host "PASO 9: Tests de regresion..." -ForegroundColor Cyan
docker compose exec app php artisan test `
    --testsuite=Feature `
    --filter="PosRegressionTest|CompraRegressionTest|CajaRegressionTest"

# --- PASO 10: Commit ---
Write-Host ""
Write-Host "PASO 10: Commiteando..." -ForegroundColor Cyan
git add .
git commit -m "feat(billing): Fase 5 - BillingService, Culqi, CheckPlanLimits, scheduler"
git push origin feature/multitenant

Write-Host ""
Write-Host "Fase 5 completada." -ForegroundColor Green
Write-Host "   Billing: http://localhost:8000/billing"
Write-Host "   IMPORTANTE: reemplaza las llaves de Culqi en .env con las tuyas reales"
Write-Host "   Obtener llaves en: https://culqi.com (registro gratuito)"
Write-Host ""
Write-Host "Siguiente: Fase 6 - Multi-sucursal"
