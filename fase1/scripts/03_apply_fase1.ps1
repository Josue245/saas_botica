# Fase 1 — Instalar migraciones y modelos nuevos (PowerShell)
# Ejecutar desde C:\saas_botica
$ErrorActionPreference = "Stop"

Write-Host "==> Verificando rama correcta..." -ForegroundColor Cyan
$rama = git branch --show-current
if ($rama -ne "feature/multitenant") {
    Write-Host "ERROR: debes estar en la rama feature/multitenant (estas en: $rama)" -ForegroundColor Red
    exit 1
}
Write-Host "   Rama: $rama OK" -ForegroundColor Green

Write-Host "==> Copiando migraciones..." -ForegroundColor Cyan
Copy-Item "fase1\database\migrations\*.php" "database\migrations\" -Force
Write-Host "   6 migraciones copiadas" -ForegroundColor Green

Write-Host "==> Copiando modelos..." -ForegroundColor Cyan
Copy-Item "fase1\app\Models\*.php" "app\Models\" -Force
Write-Host "   6 modelos copiados (Plan, Tenant, Sucursal, Suscripcion, Correlativo, StockSucursal)" -ForegroundColor Green

Write-Host "==> Corriendo migraciones en BD principal..." -ForegroundColor Cyan
docker compose exec app php artisan migrate --force

Write-Host "==> Corriendo migraciones en BD de testing..." -ForegroundColor Cyan
docker compose exec app php artisan migrate --env=testing --force

Write-Host "==> Verificando tests de regresion (no deben romperse)..." -ForegroundColor Cyan
docker compose exec app php artisan test `
    --testsuite=Feature `
    --filter="PosRegressionTest|CompraRegressionTest|CajaRegressionTest"

Write-Host ""
Write-Host "==> Fase 1 completa. Haciendo commit..." -ForegroundColor Cyan
git add .
git commit -m "db(tenant): Fase 1 - tablas planes, tenants, sucursales, suscripciones, correlativos, stock_sucursales"
git push origin feature/multitenant

Write-Host ""
Write-Host "Fase 1 completada." -ForegroundColor Green
Write-Host "   Tablas creadas: planes, tenants, sucursales, suscripciones, correlativos, stock_sucursales"
Write-Host "   Tests de regresion: deben seguir en verde"
Write-Host "   Siguiente: Fase 2 - agregar tenant_id nullable a tablas existentes"
