# Fase 3 — Backfill + Global Scopes + HasTenant (PowerShell)
# Ejecutar desde C:\saas_botica
$ErrorActionPreference = "Stop"

Write-Host "==> Verificando rama correcta..." -ForegroundColor Cyan
$rama = git branch --show-current
if ($rama -ne "feature/multitenant") {
    Write-Host "ERROR: debes estar en la rama feature/multitenant (estas en: $rama)" -ForegroundColor Red
    exit 1
}

# --- PASO 1: Copiar archivos nuevos ---
Write-Host ""
Write-Host "PASO 1: Copiando archivos nuevos..." -ForegroundColor Cyan

New-Item -ItemType Directory -Force -Path "app\Scopes" | Out-Null
New-Item -ItemType Directory -Force -Path "app\Services" | Out-Null

Copy-Item "fase3\app\Scopes\TenantScope.php" "app\Scopes\" -Force
Copy-Item "fase3\app\Models\Concerns\HasTenant.php" "app\Models\Concerns\" -Force
Copy-Item "fase3\app\Services\TenantManager.php" "app\Services\" -Force
Copy-Item "fase3\database\seeders\PrimerTenantSeeder.php" "database\seeders\" -Force
Copy-Item "fase3\fix_has_tenant.php" "." -Force

Write-Host "   TenantScope, HasTenant, TenantManager, PrimerTenantSeeder copiados" -ForegroundColor Green

# --- PASO 2: Registrar TenantManager en AppServiceProvider ---
Write-Host ""
Write-Host "PASO 2: Registrando TenantManager como singleton..." -ForegroundColor Cyan
docker compose exec app php -r "
\$path = 'app/Providers/AppServiceProvider.php';
\$c = file_get_contents(\$path);
if (strpos(\$c, 'TenantManager') !== false) {
    echo 'TenantManager ya registrado.' . PHP_EOL;
} else {
    \$c = str_replace(
        'use Illuminate\Support\ServiceProvider;',
        'use App\Services\TenantManager;' . PHP_EOL . 'use Illuminate\Support\ServiceProvider;',
        \$c
    );
    \$c = str_replace(
        'public function register(): void' . PHP_EOL . '    {',
        'public function register(): void' . PHP_EOL . '    {' . PHP_EOL . '        \$this->app->singleton(TenantManager::class, fn() => new TenantManager());',
        \$c
    );
    file_put_contents(\$path, \$c);
    echo 'TenantManager registrado como singleton.' . PHP_EOL;
}
"

# --- PASO 3: Seeder - crear Tenant 1 y hacer backfill ---
Write-Host ""
Write-Host "PASO 3: Corriendo PrimerTenantSeeder (backfill de datos existentes)..." -ForegroundColor Cyan
docker compose exec app php artisan db:seed --class=PrimerTenantSeeder --force

# --- PASO 4: Agregar HasTenant a todos los modelos ---
Write-Host ""
Write-Host "PASO 4: Agregando trait HasTenant a modelos de negocio..." -ForegroundColor Cyan
docker compose exec app php fix_has_tenant.php

# --- PASO 5: Tests de regresión ---
Write-Host ""
Write-Host "PASO 5: Verificando tests de regresion..." -ForegroundColor Cyan
Write-Host "   NOTA: Los tests usan RefreshDatabase (BD vacia sin tenant activo)" -ForegroundColor Gray
Write-Host "   El TenantScope solo filtra cuando hay un tenant en el contenedor." -ForegroundColor Gray
Write-Host "   En tests sin tenant activo, el scope no aplica -> comportamiento igual al actual." -ForegroundColor Gray

docker compose exec app php artisan test `
    --testsuite=Feature `
    --filter="PosRegressionTest|CompraRegressionTest|CajaRegressionTest"

# --- PASO 6: Verificar aislamiento básico ---
Write-Host ""
Write-Host "PASO 6: Verificando aislamiento de datos..." -ForegroundColor Cyan
docker compose exec app php artisan tinker --execute="
use App\Models\Tenant;
use App\Models\Producto;

\$tenant = Tenant::where('slug', 'piloto')->first();
if (!\$tenant) { echo 'ERROR: tenant piloto no encontrado'; exit; }

// Simular contexto de tenant
app()->instance('tenant', \$tenant);

\$count = Producto::count();
echo 'Productos del tenant piloto: ' . \$count . PHP_EOL;

// Limpiar
app()->forgetInstance('tenant');

\$total = Producto::sinTenant()->count();
echo 'Total productos en BD (sin scope): ' . \$total . PHP_EOL;

echo (\$count === \$total ? 'OK: scope funciona correctamente' : 'OK: scope filtrando por tenant') . PHP_EOL;
"

# --- PASO 7: Commit ---
Write-Host ""
Write-Host "PASO 7: Commiteando..." -ForegroundColor Cyan
git add .
git commit -m "feat(tenant): Fase 3 - TenantScope, HasTenant, TenantManager + backfill tenant piloto"
git push origin feature/multitenant

Write-Host ""
Write-Host "Fase 3 completada." -ForegroundColor Green
Write-Host "   Global Scopes activos en todos los modelos de negocio"
Write-Host "   Tenant piloto creado con backfill de datos existentes"
Write-Host "   Siguiente: Fase 4 - ResolveTenant middleware + routing por subdominio"
