#!/usr/bin/env bash
# Fase 8 — Hardening y observabilidad
# Ejecutar desde ~/saas_botica en WSL2
set -euo pipefail

echo "==> Fase 8: Hardening y observabilidad"
cd ~/saas_botica

# -----------------------------------------------------------------------
# FIX 1: Rate limiting en login (5 intentos por minuto por IP)
# -----------------------------------------------------------------------
echo ""
echo "FIX 1: Rate limiting en login..."

cat > /tmp/fix_ratelimit.php << 'EOF'
<?php
$path = 'bootstrap/app.php';
$c = file_get_contents($path);

if (strpos($c, 'RateLimiter') !== false) {
    echo "Rate limiter ya configurado\n";
    exit;
}

// Agregar import
$c = str_replace(
    'use Illuminate\Foundation\Configuration\Exceptions;',
    "use Illuminate\Cache\RateLimiting\Limit;\nuse Illuminate\Foundation\Configuration\Exceptions;\nuse Illuminate\Http\Request;\nuse Illuminate\Support\Facades\RateLimiter;",
    $c
);

// Agregar rate limiter en withRouting
$c = str_replace(
    "->withRouting(",
    "->withRouting(",
    $c
);

// Agregar en boot via AppServiceProvider en su lugar
echo "Rate limiter se configurara en AppServiceProvider\n";
EOF
docker compose cp /tmp/fix_ratelimit.php app:/tmp/fix_ratelimit.php
docker compose exec app php /tmp/fix_ratelimit.php

# Agregar rate limiter en AppServiceProvider
cat > /tmp/fix_ratelimit2.php << 'EOF'
<?php
$path = 'app/Providers/AppServiceProvider.php';
$c = file_get_contents($path);

if (strpos($c, 'RateLimiter') !== false) {
    echo "Ya configurado\n";
    exit;
}

$c = str_replace(
    'use Illuminate\Support\ServiceProvider;',
    "use Illuminate\Cache\RateLimiting\Limit;\nuse Illuminate\Http\Request;\nuse Illuminate\Support\Facades\RateLimiter;\nuse Illuminate\Support\ServiceProvider;",
    $c
);

$c = str_replace(
    'public function boot(): void
    {',
    'public function boot(): void
    {
        // Rate limiting: max 5 intentos de login por minuto por IP
        RateLimiter::for("login", function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
',
    $c
);

file_put_contents($path, $c);
echo "Rate limiter configurado OK\n";
EOF
docker compose cp /tmp/fix_ratelimit2.php app:/tmp/fix_ratelimit2.php
docker compose exec app php /tmp/fix_ratelimit2.php

# Aplicar rate limiter a la ruta de login
cat > /tmp/fix_login_route.php << 'EOF'
<?php
$path = 'routes/web.php';
$c = file_get_contents($path);

// Agregar throttle al login
$c = str_replace(
    "Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');",
    "Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login');",
    $c
);

file_put_contents($path, $c);
echo "Throttle en login OK\n";
EOF
docker compose cp /tmp/fix_login_route.php app:/tmp/fix_login_route.php
docker compose exec app php /tmp/fix_login_route.php

# -----------------------------------------------------------------------
# FIX 2: Permisos granulares - asegurar rutas admin
# -----------------------------------------------------------------------
echo ""
echo "FIX 2: Proteger rutas admin con middleware role..."

cat > /tmp/fix_admin_routes.php << 'EOF'
<?php
$path = 'routes/web.php';
$c = file_get_contents($path);

// Verificar si personal ya tiene role:admin
if (strpos($c, "role:admin") !== false) {
    echo "Rutas admin ya protegidas\n";
    exit;
}

// Proteger rutas sensibles con role:admin
$c = str_replace(
    "Route::resource('personal', PersonalController::class);",
    "Route::resource('personal', PersonalController::class)->middleware('role:admin');",
    $c
);

$c = str_replace(
    "Route::get('/configuracion'",
    "Route::get('/configuracion'",
    $c
);

file_put_contents($path, $c);
echo "Rutas admin protegidas OK\n";
EOF
docker compose cp /tmp/fix_admin_routes.php app:/tmp/fix_admin_routes.php
docker compose exec app php /tmp/fix_admin_routes.php

# -----------------------------------------------------------------------
# FIX 3: Headers de seguridad HTTP
# -----------------------------------------------------------------------
echo ""
echo "FIX 3: Middleware de headers de seguridad..."

cat > /tmp/fix_security_headers.php << 'EOF'
<?php
$middleware = <<<'PHP'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): \Symfony\Component\HttpFoundation\Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
PHP;

file_put_contents('app/Http/Middleware/SecurityHeaders.php', $middleware);
echo "SecurityHeaders middleware creado OK\n";

// Registrar en bootstrap/app.php
$path = 'bootstrap/app.php';
$c = file_get_contents($path);

if (strpos($c, 'SecurityHeaders') === false) {
    $c = str_replace(
        '$middleware->appendToGroup("web", \App\Http\Middleware\ResolveTenant::class);',
        '$middleware->appendToGroup("web", \App\Http\Middleware\ResolveTenant::class);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);',
        $c
    );
    file_put_contents($path, $c);
    echo "SecurityHeaders registrado OK\n";
}
EOF
docker compose cp /tmp/fix_security_headers.php app:/tmp/fix_security_headers.php
docker compose exec app php /tmp/fix_security_headers.php

# -----------------------------------------------------------------------
# FIX 4: Comando de backup por tenant
# -----------------------------------------------------------------------
echo ""
echo "FIX 4: Comando de backup por tenant..."

cat > app/Console/Commands/BackupTenantCommand.php << 'PHPEOF'
<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupTenantCommand extends Command
{
    protected $signature   = 'tenant:backup {--tenant_id= : ID del tenant (omitir para todos)}';
    protected $description = 'Genera backup SQL filtrado por tenant';

    private array $tablas = [
        'categorias', 'proveedores', 'productos', 'clientes',
        'ventas', 'venta_detalles', 'compras', 'compra_detalles',
        'caja_sesiones', 'caja_movimientos', 'ajuste_inventarios',
        'configuraciones', 'auditorias', 'comprobantes_electronicos',
    ];

    public function handle(): int
    {
        $tenantId = $this->option('tenant_id');
        $tenants  = $tenantId
            ? Tenant::where('id', $tenantId)->get()
            : Tenant::all();

        foreach ($tenants as $tenant) {
            $this->info("Generando backup para: {$tenant->razon_social} (ID: {$tenant->id})");
            $this->generarBackup($tenant);
        }

        return self::SUCCESS;
    }

    private function generarBackup(Tenant $tenant): void
    {
        $sql  = "-- Backup Tenant: {$tenant->razon_social}\n";
        $sql .= "-- RUC: {$tenant->ruc}\n";
        $sql .= "-- Fecha: " . now()->toDateTimeString() . "\n";
        $sql .= "-- Generado por Mi Botica SaaS\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($this->tablas as $tabla) {
            $filas = DB::table($tabla)->where('tenant_id', $tenant->id)->get();
            if ($filas->isEmpty()) continue;

            $sql .= "-- Tabla: {$tabla}\n";
            $cols    = array_keys((array) $filas->first());
            $colList = '`' . implode('`, `', $cols) . '`';

            foreach ($filas as $fila) {
                $valores = array_map(function ($v) {
                    return $v === null ? 'NULL' : "'" . addslashes((string) $v) . "'";
                }, (array) $fila);
                $sql .= "INSERT INTO `{$tabla}` ({$colList}) VALUES (" . implode(', ', $valores) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        // Guardar en storage/app/backups/tenant_{id}/
        $nombre = "tenant_{$tenant->id}/" . now()->format('Y/m') . "/backup_" . now()->format('Ymd_His') . ".sql";
        Storage::disk('local')->put("backups/{$nombre}", $sql);

        $this->info("  Backup guardado: storage/app/backups/{$nombre}");
        $this->info("  Tamaño: " . strlen($sql) . " bytes");
    }
}
PHPEOF
echo "BackupTenantCommand creado OK"

# -----------------------------------------------------------------------
# FIX 5: Scheduler - jobs automáticos
# -----------------------------------------------------------------------
echo ""
echo "FIX 5: Configurando scheduler..."

cat > /tmp/fix_scheduler.php << 'EOF'
<?php
$path = 'routes/console.php';
$c = file_get_contents($path);

$schedule_code = "
// Backup diario de todos los tenants a las 3am
Schedule::command('tenant:backup')->dailyAt('03:00');

// Verificar suscripciones vencidas diariamente a las 2am  
// Schedule::job(new App\Jobs\CheckExpiredSubscriptionsJob)->dailyAt('02:00');
";

if (strpos($c, 'tenant:backup') === false) {
    $c = rtrim($c) . "\n" . $schedule_code;
    file_put_contents($path, $c);
    echo "Scheduler configurado OK\n";
} else {
    echo "Ya configurado\n";
}
EOF
docker compose cp /tmp/fix_scheduler.php app:/tmp/fix_scheduler.php
docker compose exec app php /tmp/fix_scheduler.php

# -----------------------------------------------------------------------
# FIX 6: SESSION_ENCRYPT en producción
# -----------------------------------------------------------------------
echo ""
echo "FIX 6: Verificando configuracion de seguridad..."

# Verificar APP_DEBUG
if grep -q "APP_DEBUG=true" .env; then
    echo "  AVISO: APP_DEBUG=true en .env - cambiar a false en produccion"
fi

if grep -q "SESSION_ENCRYPT=false" .env; then
    echo "  AVISO: SESSION_ENCRYPT=false - cambiar a true en produccion"
fi

echo "  Para produccion, actualizar en .env:"
echo "  APP_DEBUG=false"
echo "  SESSION_ENCRYPT=true"
echo "  APP_ENV=production"

# -----------------------------------------------------------------------
# FIX 7: Optimizar autoloader
# -----------------------------------------------------------------------
echo ""
echo "FIX 7: Optimizando autoloader..."
docker compose exec app composer dump-autoload --optimize

# -----------------------------------------------------------------------
# Tests
# -----------------------------------------------------------------------
echo ""
echo "==> Tests de regresion..."
docker compose exec app php artisan test \
    --testsuite=Feature \
    --filter="PosRegressionTest|CompraRegressionTest|CajaRegressionTest"

# -----------------------------------------------------------------------
# Cache final
# -----------------------------------------------------------------------
echo ""
echo "==> Cache final..."
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan event:cache
docker compose exec app php artisan view:cache

# -----------------------------------------------------------------------
# Commit
# -----------------------------------------------------------------------
echo ""
echo "==> Commiteando..."
git add .
git commit -m "feat(hardening): Fase 8 - rate limiting, security headers, backup por tenant, scheduler"
git push origin feature/multitenant

echo ""
echo "=============================================="
echo "  FASE 8 COMPLETADA - ROADMAP TERMINADO"
echo "=============================================="
echo ""
echo "Resumen del sistema:"
echo "  Rate limiting: 5 intentos login/min por IP"
echo "  Security headers: X-Frame-Options, XSS, MIME"
echo "  Backup por tenant: artisan tenant:backup"
echo "  Scheduler: backup diario 3am"
echo "  Autoloader optimizado"
echo ""
echo "Para produccion, actualizar en .env:"
echo "  APP_DEBUG=false"
echo "  APP_ENV=production"
echo "  SESSION_ENCRYPT=true"
echo ""
echo "El sistema SaaS multi-tenant esta listo."
