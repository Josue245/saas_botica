# Fase 4 — ResolveTenant middleware + rutas de registro (PowerShell)
# Ejecutar desde C:\saas_botica
$ErrorActionPreference = "Stop"

Write-Host "==> Verificando rama correcta..." -ForegroundColor Cyan
$rama = git branch --show-current
if ($rama -ne "feature/multitenant") {
    Write-Host "ERROR: debes estar en feature/multitenant (estas en: $rama)" -ForegroundColor Red
    exit 1
}

# --- PASO 1: Copiar archivos ---
Write-Host ""
Write-Host "PASO 1: Copiando archivos..." -ForegroundColor Cyan

New-Item -ItemType Directory -Force -Path "app\Http\Middleware" | Out-Null
New-Item -ItemType Directory -Force -Path "app\Http\Controllers\Auth" | Out-Null
New-Item -ItemType Directory -Force -Path "resources\views\auth" | Out-Null
New-Item -ItemType Directory -Force -Path "resources\views\billing" | Out-Null

Copy-Item "fase4\app\Http\Middleware\ResolveTenant.php" "app\Http\Middleware\" -Force
Copy-Item "fase4\app\Http\Middleware\EnsureTenantActive.php" "app\Http\Middleware\" -Force
Copy-Item "fase4\app\Http\Controllers\Auth\TenantRegistrationController.php" "app\Http\Controllers\Auth\" -Force
Copy-Item "fase4\resources\views\auth\register.blade.php" "resources\views\auth\" -Force
Copy-Item "fase4\resources\views\billing\suspendido.blade.php" "resources\views\billing\" -Force

Write-Host "   Middleware, Controller y vistas copiados" -ForegroundColor Green

# --- PASO 2: Registrar middleware en bootstrap/app.php ---
Write-Host ""
Write-Host "PASO 2: Registrando middlewares en bootstrap/app.php..." -ForegroundColor Cyan

@'
<?php
$path = 'bootstrap/app.php';
$c = file_get_contents($path);

// Agregar ResolveTenant y EnsureTenantActive al middleware stack
$middlewareAlias = "->withMiddleware(function (Middleware \$middleware) {";
$middlewareReplace = "->withMiddleware(function (Middleware \$middleware) {
        \$middleware->append(\App\Http\Middleware\ResolveTenant::class);
        \$middleware->alias([
            'tenant.active' => \App\Http\Middleware\EnsureTenantActive::class,
        ]);";

if (strpos($c, 'ResolveTenant') !== false) {
    echo "Middlewares ya registrados.\n";
} else {
    $c = str_replace($middlewareAlias, $middlewareReplace, $c);
    file_put_contents($path, $c);
    echo "Middlewares registrados OK\n";
}
'@ | Set-Content -Path "fix_bootstrap.php"

docker compose exec app php fix_bootstrap.php

# --- PASO 3: Agregar rutas de registro de tenant a web.php ---
Write-Host ""
Write-Host "PASO 3: Agregando rutas de registro a web.php..." -ForegroundColor Cyan

@'
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
'@ | Set-Content -Path "fix_routes.php"

docker compose exec app php fix_routes.php

# --- PASO 4: Agregar base_domain a config/app.php ---
Write-Host ""
Write-Host "PASO 4: Agregando base_domain a config/app.php..." -ForegroundColor Cyan

@'
<?php
$path = 'config/app.php';
$c = file_get_contents($path);

if (strpos($c, 'base_domain') !== false) {
    echo "base_domain ya existe.\n";
} else {
    $c = str_replace(
        "'name' => env('APP_NAME', 'Laravel'),",
        "'name' => env('APP_NAME', 'Laravel'),\n    'base_domain' => env('APP_BASE_DOMAIN', 'mibotica.pe'),",
        $c
    );
    file_put_contents($path, $c);
    echo "base_domain agregado OK\n";
}
'@ | Set-Content -Path "fix_config.php"

docker compose exec app php fix_config.php

# --- PASO 5: Agregar APP_BASE_DOMAIN al .env ---
Write-Host ""
Write-Host "PASO 5: Agregando APP_BASE_DOMAIN al .env..." -ForegroundColor Cyan
$envContent = Get-Content ".env" -Raw
if ($envContent -notmatch "APP_BASE_DOMAIN") {
    Add-Content ".env" "`nAPP_BASE_DOMAIN=mibotica.pe"
    Write-Host "   APP_BASE_DOMAIN=mibotica.pe agregado" -ForegroundColor Green
} else {
    Write-Host "   APP_BASE_DOMAIN ya existe" -ForegroundColor Yellow
}

# --- PASO 6: Limpiar cache de rutas y config ---
Write-Host ""
Write-Host "PASO 6: Limpiando cache..." -ForegroundColor Cyan
docker compose exec app php artisan route:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear

# --- PASO 7: Tests de regresion ---
Write-Host ""
Write-Host "PASO 7: Verificando tests de regresion..." -ForegroundColor Cyan
docker compose exec app php artisan test `
    --testsuite=Feature `
    --filter="PosRegressionTest|CompraRegressionTest|CajaRegressionTest"

# --- PASO 8: Probar registro de tenant ---
Write-Host ""
Write-Host "PASO 8: Verificando rutas nuevas..." -ForegroundColor Cyan
docker compose exec app php artisan route:list --name=register --columns=method,uri,name

# --- PASO 9: Probar ResolveTenant con query param ---
Write-Host ""
Write-Host "PASO 9: Probando ResolveTenant..." -ForegroundColor Cyan
Write-Host "   Abre en tu navegador:" -ForegroundColor Gray
Write-Host "   http://localhost:8000/dashboard?_tenant=piloto" -ForegroundColor White
Write-Host "   Deberias ver el dashboard del tenant 'piloto' (o redirect a login)" -ForegroundColor Gray

# --- PASO 10: Commit ---
Write-Host ""
Write-Host "PASO 10: Commiteando..." -ForegroundColor Cyan
git add .
git commit -m "feat(tenant): Fase 4 - ResolveTenant middleware + TenantRegistrationController + rutas onboarding"
git push origin feature/multitenant

Write-Host ""
Write-Host "Fase 4 completada." -ForegroundColor Green
Write-Host "   ResolveTenant activo (subdominio / header / query param)"
Write-Host "   Registro de nuevas empresas: http://localhost:8000/register"
Write-Host "   Dashboard con tenant: http://localhost:8000/dashboard?_tenant=piloto"
Write-Host ""
Write-Host "Siguiente: Fase 5 - Billing con Culqi + planes + suscripciones"
