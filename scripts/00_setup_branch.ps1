# Fase 0 — Setup de rama y snapshot de seguridad (PowerShell / Windows)
# Ejecutar desde la raíz del proyecto: C:\xampp\htdocs\saas_botica

$ErrorActionPreference = "Stop"

Write-Host "==> Verificando estado del repositorio..." -ForegroundColor Cyan
$status = git status --porcelain
if ($status) {
    Write-Host "Tienes cambios sin commitear. Haz commit o stash antes de continuar." -ForegroundColor Red
    git status --short
    exit 1
}

$currentBranch = git branch --show-current
Write-Host "==> Rama actual: $currentBranch" -ForegroundColor Cyan

# 1. Detectar rama base (main o master)
try {
    $baseBranch = (git symbolic-ref refs/remotes/origin/HEAD 2>$null) -replace '^refs/remotes/origin/', ''
    if (-not $baseBranch) { $baseBranch = "main" }
} catch {
    $baseBranch = "main"
}
Write-Host "==> Rama base detectada: $baseBranch" -ForegroundColor Cyan
git checkout $baseBranch
git pull origin $baseBranch

# 2. Tag de snapshot pre-migración (rollback instantáneo)
$timestamp = Get-Date -Format "yyyyMMdd-HHmm"
$snapshotTag = "pre-multitenant-$timestamp"
git tag -a $snapshotTag -m "Snapshot antes de iniciar migracion multi-tenant"
git push origin $snapshotTag
Write-Host "Tag de snapshot creado: $snapshotTag" -ForegroundColor Green
Write-Host "   Rollback de emergencia: git checkout $snapshotTag" -ForegroundColor Gray

# 3. Crear rama de trabajo
$branchName = "feature/multitenant"
$branchExists = git show-ref --verify --quiet "refs/heads/$branchName"
if ($LASTEXITCODE -eq 0) {
    Write-Host "La rama $branchName ya existe localmente. Cambiando a ella." -ForegroundColor Yellow
    git checkout $branchName
} else {
    git checkout -b $branchName
    git push -u origin $branchName
    Write-Host "Rama creada: $branchName" -ForegroundColor Green
}

Write-Host ""
Write-Host "==> Convencion de commits para esta migracion:" -ForegroundColor Cyan
Write-Host ""
Write-Host "  feat(tenant): ...      -> nueva funcionalidad multi-tenant"
Write-Host "  fix(tenant): ...       -> correccion de bug relacionado a tenant"
Write-Host "  test(tenant): ...      -> tests nuevos o actualizados"
Write-Host "  db(tenant): ...        -> migraciones de base de datos"
Write-Host "  refactor(tenant): ...  -> refactor sin cambio de comportamiento"
Write-Host ""
Write-Host "  Ejemplo:"
Write-Host "    git commit -m 'db(tenant): crear tabla tenants y planes'"
Write-Host ""
Write-Host "==> Listo. Estas en la rama $branchName, basada en $snapshotTag." -ForegroundColor Green
Write-Host "==> Siguiente paso: .\scripts\01_docker_up.ps1" -ForegroundColor Cyan
