# Fase 0 — Levanta el stack Docker (PowerShell / Windows)
# Detecta automáticamente si XAMPP está usando los puertos 80 o 3306.

$ErrorActionPreference = "Stop"

Write-Host "==> Verificando puertos en uso (XAMPP suele ocupar 80 y 3306)..." -ForegroundColor Cyan

function Test-PortInUse($port) {
    $conn = Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue
    return $null -ne $conn
}

$conflictos = @()
if (Test-PortInUse 3306) { $conflictos += "3306 (MySQL — probablemente XAMPP)" }
if (Test-PortInUse 8000) { $conflictos += "8000 (Nginx del contenedor)" }

if ($conflictos.Count -gt 0) {
    Write-Host ""
    Write-Host "ATENCION: puertos en uso detectados:" -ForegroundColor Yellow
    $conflictos | ForEach-Object { Write-Host "  - $_" -ForegroundColor Yellow }
    Write-Host ""
    Write-Host "Si XAMPP esta corriendo MySQL en 3306, el contenedor 'mysql' de Docker" -ForegroundColor Yellow
    Write-Host "no podra arrancar (puerto ocupado)." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Opciones:" -ForegroundColor Cyan
    Write-Host "  A) Apaga MySQL de XAMPP (panel de control de XAMPP -> Stop en MySQL)" -ForegroundColor Gray
    Write-Host "     y vuelve a correr este script. RECOMENDADO para este proyecto." -ForegroundColor Gray
    Write-Host "  B) Cambia el puerto del docker-compose.yml de 3306 a 3310" -ForegroundColor Gray
    Write-Host "     (edita 'docker-compose.yml', linea del servicio mysql: '3310:3306')" -ForegroundColor Gray
    Write-Host ""
    $continuar = Read-Host "Quieres continuar de todas formas? (s/n)"
    if ($continuar -ne "s") {
        Write-Host "Cancelado. Resuelve el conflicto de puertos y vuelve a correr el script." -ForegroundColor Red
        exit 1
    }
}

Write-Host "==> Levantando contenedores (app, nginx, mysql, mysql_testing, redis, mailpit)..." -ForegroundColor Cyan
docker compose up -d --build

Write-Host "==> Esperando a que MySQL este listo..." -ForegroundColor Cyan
$maxRetries = 30
$retries = 0
do {
    Start-Sleep -Seconds 2
    $ready = docker compose exec -T mysql mysqladmin ping -h localhost -uroot -proot_local_only --silent 2>$null
    $retries++
    Write-Host "." -NoNewline
} while ($LASTEXITCODE -ne 0 -and $retries -lt $maxRetries)
Write-Host " OK" -ForegroundColor Green

if ($retries -ge $maxRetries) {
    Write-Host "MySQL no respondio a tiempo. Revisa: docker compose logs mysql" -ForegroundColor Red
    exit 1
}

Write-Host "==> Esperando a que MySQL de testing este listo..." -ForegroundColor Cyan
$retries = 0
do {
    Start-Sleep -Seconds 2
    $ready = docker compose exec -T mysql_testing mysqladmin ping -h localhost -uroot -proot_local_only --silent 2>$null
    $retries++
    Write-Host "." -NoNewline
} while ($LASTEXITCODE -ne 0 -and $retries -lt $maxRetries)
Write-Host " OK" -ForegroundColor Green

Write-Host "==> Instalando dependencias de Composer..." -ForegroundColor Cyan
docker compose exec app composer install

if (-not (Test-Path ".env")) {
    Write-Host "==> Copiando .env.example a .env..." -ForegroundColor Cyan
    Copy-Item ".env.example" ".env"
    docker compose exec app php artisan key:generate
}

Write-Host "==> Verificando .env.testing..." -ForegroundColor Cyan
$envTestingContent = Get-Content ".env.testing" -Raw
if ($envTestingContent -match "GENERAR_CON_php_artisan_key_generate") {
    Write-Host "==> Generando APP_KEY para .env.testing..." -ForegroundColor Cyan
    $testKey = docker compose exec -T app php artisan key:generate --show
    $testKey = $testKey.Trim()
    $newContent = $envTestingContent -replace "APP_KEY=base64:GENERAR_CON_php_artisan_key_generate", "APP_KEY=$testKey"
    Set-Content -Path ".env.testing" -Value $newContent -NoNewline
}

Write-Host "==> Corriendo migraciones en BD principal (saas_botica)..." -ForegroundColor Cyan
docker compose exec app php artisan migrate --force

Write-Host "==> Corriendo migraciones en BD de testing (saas_botica_testing)..." -ForegroundColor Cyan
docker compose exec app php artisan migrate --env=testing --force

Write-Host ""
Write-Host "Stack listo." -ForegroundColor Green
Write-Host "   App:        http://localhost:8000"
Write-Host "   Mailpit:    http://localhost:8025"
Write-Host "   MySQL:      localhost:3306 (saas_botica)"
Write-Host "   MySQL test: localhost:3307 (saas_botica_testing)"
Write-Host ""
Write-Host "==> Siguiente paso: .\scripts\02_run_baseline_tests.ps1" -ForegroundColor Cyan
