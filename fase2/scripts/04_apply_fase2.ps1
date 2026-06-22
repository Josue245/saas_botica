# Fase 2 — Agregar tenant_id nullable a tablas existentes (PowerShell)
# Ejecutar desde C:\saas_botica
$ErrorActionPreference = "Stop"

Write-Host "==> Verificando rama correcta..." -ForegroundColor Cyan
$rama = git branch --show-current
if ($rama -ne "feature/multitenant") {
    Write-Host "ERROR: debes estar en la rama feature/multitenant (estas en: $rama)" -ForegroundColor Red
    exit 1
}

Write-Host "==> Copiando migración..." -ForegroundColor Cyan
Copy-Item "fase2\database\migrations\*.php" "database\migrations\" -Force
Write-Host "   OK" -ForegroundColor Green

Write-Host "==> Corriendo migración en BD principal..." -ForegroundColor Cyan
docker compose exec app php artisan migrate --force

Write-Host "==> Corriendo migración en BD de testing..." -ForegroundColor Cyan
docker compose exec app php artisan migrate --env=testing --force

Write-Host ""
Write-Host "==> Verificando tests de regresión (CRÍTICO: no deben romperse)..." -ForegroundColor Cyan
docker compose exec app php artisan test `
    --testsuite=Feature `
    --filter="PosRegressionTest|CompraRegressionTest|CajaRegressionTest"

Write-Host ""
Write-Host "==> Verificando columnas en BD principal..." -ForegroundColor Cyan
docker compose exec app php artisan tinker --execute="echo Schema::hasColumn('ventas','tenant_id') ? 'ventas.tenant_id OK' : 'FALTA';"
docker compose exec app php artisan tinker --execute="echo Schema::hasColumn('productos','tenant_id') ? 'productos.tenant_id OK' : 'FALTA';"
docker compose exec app php artisan tinker --execute="echo Schema::hasColumn('users','sucursal_id') ? 'users.sucursal_id OK' : 'FALTA';"

Write-Host ""
Write-Host "==> Commiteando..." -ForegroundColor Cyan
git add .
git commit -m "db(tenant): Fase 2 - tenant_id nullable en todas las tablas de negocio"
git push origin feature/multitenant

Write-Host ""
Write-Host "Fase 2 completada." -ForegroundColor Green
Write-Host "   Las columnas tenant_id son nullable — el sistema actual sigue funcionando."
Write-Host "   Siguiente: Fase 3 - backfill + Global Scopes + trait HasTenant"
