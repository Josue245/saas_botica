# Fase 0 — Corre los tests de regresión baseline (PowerShell / Windows)
$ErrorActionPreference = "Stop"

New-Item -ItemType Directory -Force -Path "storage\baseline" | Out-Null

Write-Host "==> Corriendo tests de regresion (POS, Compras, Caja)..." -ForegroundColor Cyan

$timestamp = Get-Date -Format "yyyyMMdd_HHmm"
$outFile = "storage\baseline\baseline_$timestamp.txt"

docker compose exec app php artisan test `
  --testsuite=Feature `
  --filter="PosRegressionTest|CompraRegressionTest|CajaRegressionTest" `
  | Tee-Object -FilePath $outFile

Write-Host ""
Write-Host "Si todos los tests pasaron (verde), tienes tu linea base." -ForegroundColor Green
Write-Host "   Guarda este archivo en git: storage\baseline\" -ForegroundColor Gray
Write-Host ""
Write-Host "==> A partir de ahora, en CADA fase del roadmap corre:" -ForegroundColor Cyan
Write-Host "    docker compose exec app php artisan test --filter=RegressionTest"
Write-Host "    Si algo se pone rojo que antes estaba verde -> rompiste algo. Para y revisa." -ForegroundColor Yellow
