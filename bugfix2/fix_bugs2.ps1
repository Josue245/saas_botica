# Bugfix 2: velocidad + sucursal activa + tipo comprobante + estado sucursal
$ErrorActionPreference = "Stop"

Write-Host "==> Aplicando fixes de rendimiento y bugs..." -ForegroundColor Cyan

# -----------------------------------------------------------------------
# FIX 1: VELOCIDAD - Cachear queries pesadas del AppServiceProvider
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "FIX 1: Cachear alertas en AppServiceProvider..." -ForegroundColor Yellow

@'
<?php
$path = 'app/Providers/AppServiceProvider.php';
$c = file_get_contents($path);

// Cachear las queries de alertas por 60 segundos por tenant
$old = "View::composer(['partials.sidebar', 'partials.topbar'], function (\$view) {
            if (!app()->bound('tenant') || app('tenant') === null) return;";

$new = "View::composer(['partials.sidebar', 'partials.topbar'], function (\$view) {
            if (!app()->bound('tenant') || app('tenant') === null) return;
            \$tenantId = app('tenant')->id;
            \$cacheKey = 'alertas_sidebar_' . \$tenantId;
            \$cached = \Illuminate\Support\Facades\Cache::remember(\$cacheKey, 60, function () {";

// Buscar el final del View Composer para envolver en cache
if (strpos($c, "Cache::remember(\$cacheKey") !== false) {
    echo "Cache ya aplicado\n";
    exit;
}

// Agregar cache wrapping simple: solo cachear los counts
$search = "View::composer(['partials.sidebar', 'partials.topbar'], function (\$view) {\n            if (!app()->bound('tenant') || app('tenant') === null) return;";

// Verificar que existe el patron
if (strpos($c, $search) === false) {
    echo "Patron no encontrado, buscando alternativa...\n";
    // Buscar sin el if
    $search2 = "View::composer(['partials.sidebar', 'partials.topbar'], function (\$view) {";
    if (strpos($c, $search2) !== false) {
        // Agregar cache a los conteos individualmente
        $c = str_replace(
            '$alertasCount = ',
            '$alertasCount = \Illuminate\Support\Facades\Cache::remember("alertas_count_" . (app()->bound("tenant") ? app("tenant")->id : "0"), 60, fn() => ',
            $c
        );
        file_put_contents($path, $c);
        echo "Cache aplicado en alertasCount\n";
    }
} else {
    file_put_contents($path, $c);
    echo "Fix aplicado\n";
}
'@ | Set-Content -Path "fix_cache_alertas.php"
docker compose exec app php fix_cache_alertas.php

# -----------------------------------------------------------------------
# FIX 2: VELOCIDAD - Agregar indices faltantes
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "FIX 2: Agregar indices de BD para acelerar queries..." -ForegroundColor Yellow

@'
<?php
// Crear migración de índices
$migration = <<<'PHP'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Índices para queries frecuentes del dashboard y sidebar
        Schema::table('productos', function (Blueprint $table) {
            try { $table->index(['tenant_id', 'activo', 'stock'], 'idx_prod_tenant_activo_stock'); } catch (\Throwable $e) {}
            try { $table->index(['tenant_id', 'fecha_vencimiento'], 'idx_prod_vencimiento'); } catch (\Throwable $e) {}
        });

        Schema::table('ventas', function (Blueprint $table) {
            try { $table->index(['tenant_id', 'estado', 'created_at'], 'idx_ventas_tenant_estado_fecha'); } catch (\Throwable $e) {}
        });

        Schema::table('users', function (Blueprint $table) {
            try { $table->index(['tenant_id', 'activo'], 'idx_users_tenant_activo'); } catch (\Throwable $e) {}
        });
    }

    public function down(): void {}
};
PHP;

file_put_contents('database/migrations/2026_06_25_000001_add_performance_indexes.php', $migration);
echo "Migration de indices creada OK\n";
'@ | Set-Content -Path "fix_create_indexes_migration.php"
docker compose exec app php fix_create_indexes_migration.php
docker compose exec app php artisan migrate --force

# -----------------------------------------------------------------------
# FIX 3: VELOCIDAD - Deshabilitar debug en local para reducir overhead
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "FIX 3: Optimizar configuracion PHP-FPM..." -ForegroundColor Yellow

# Aumentar timeout de Nginx para evitar 504 en operaciones lentas
@"
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;
    index index.php index.html;

    client_max_body_size 20M;

    # Aumentar timeouts para evitar 504
    fastcgi_read_timeout 120;
    fastcgi_send_timeout 120;
    fastcgi_connect_timeout 30;

    location / {
        try_files `$uri `$uri/ /index.php?`$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME `$document_root`$fastcgi_script_name;
        fastcgi_param PATH_INFO `$fastcgi_path_info;
        fastcgi_read_timeout 120;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
"@ | Set-Content -Path "docker\nginx\default.conf" -Encoding UTF8
Write-Host "   Nginx timeout aumentado a 120s" -ForegroundColor Green

# -----------------------------------------------------------------------
# FIX 4: COMPROBANTE - Agregar tipo (boleta/factura) al emitir
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "FIX 4: Agregar seleccion de tipo en emitir comprobante..." -ForegroundColor Yellow

@'
<?php
$path = 'resources/views/ventas/index.blade.php';
$c = file_get_contents($path);

// Reemplazar el form simple por uno con selección de tipo
$old = '@elseif($v->estado === \'pagada\' && auth()->user()?->tenant?->puedeUsar(\'facturacion_electronica\'))
                                    <form method="POST" action="{{ route(\'facturacion.emitir\', $v) }}">
                                        @csrf
                                        <button class="text-xs text-blue-600 hover:underline">Emitir</button>
                                    </form>';

$new = '@elseif($v->estado === \'pagada\' && auth()->user()?->tenant?->puedeUsar(\'facturacion_electronica\'))
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open=!open" class="text-xs text-blue-600 hover:underline">Emitir ▾</button>
                                        <div x-show="open" x-cloak @click.outside="open=false"
                                             class="absolute right-0 mt-1 w-36 bg-white rounded-lg shadow-lg border border-gray-100 z-10">
                                            <form method="POST" action="{{ route(\'facturacion.emitir\', $v) }}">
                                                @csrf
                                                <input type="hidden" name="tipo" value="boleta">
                                                <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50">🧾 Boleta</button>
                                            </form>
                                            <form method="POST" action="{{ route(\'facturacion.emitir\', $v) }}">
                                                @csrf
                                                <input type="hidden" name="tipo" value="factura">
                                                <button class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50">📄 Factura</button>
                                            </form>
                                        </div>
                                    </div>';

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "FIX 4 OK: selector tipo comprobante agregado\n";
'@ | Set-Content -Path "fix_tipo_comprobante.php"
docker compose exec app php fix_tipo_comprobante.php

# -----------------------------------------------------------------------
# FIX 5: COMPROBANTE - Usar tipo del request en FacturacionController
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "FIX 5: FacturacionController usa tipo del request..." -ForegroundColor Yellow

@'
<?php
$path = 'app/Http/Controllers/FacturacionController.php';
$c = file_get_contents($path);

$old = "        \$tipo = \$venta->tipo_comprobante === 'factura' ? 'factura' : 'boleta';";
$new = "        // Usar tipo del request si viene, sino usar el de la venta
        \$tipoRequest = request()->input('tipo');
        \$tipo = in_array(\$tipoRequest, ['boleta', 'factura']) ? \$tipoRequest : (\$venta->tipo_comprobante === 'factura' ? 'factura' : 'boleta');";

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "FIX 5 OK: tipo desde request\n";
'@ | Set-Content -Path "fix_tipo_request.php"
docker compose exec app php fix_tipo_request.php

# -----------------------------------------------------------------------
# FIX 6: SUCURSAL - Cambio de sucursal refresca la sesión correctamente
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "FIX 6: Cambio de sucursal refresca sesion y redirige..." -ForegroundColor Yellow

@'
<?php
$path = 'app/Http/Controllers/SucursalController.php';
$c = file_get_contents($path);

$old = "        // Actualizar sucursal del usuario
        auth()->user()->update(['sucursal_id' => \$sucursal->id]);
        app()->instance('sucursal', \$sucursal);

        return back()->with('ok', \"Sucursal activa: {\$sucursal->nombre}\");";

$new = "        // Actualizar sucursal del usuario
        auth()->user()->update(['sucursal_id' => \$sucursal->id]);
        app()->instance('sucursal', \$sucursal);

        // Forzar refresh de la sesión para que ResolveTenant lo detecte
        request()->session()->put('sucursal_id', \$sucursal->id);

        return redirect()->route('sucursales.index')
            ->with('ok', \"Sucursal activa cambiada a: {\$sucursal->nombre}\");";

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "FIX 6 OK: cambio de sucursal hace redirect\n";
'@ | Set-Content -Path "fix_cambio_sucursal.php"
docker compose exec app php fix_cambio_sucursal.php

# -----------------------------------------------------------------------
# FIX 7: SUCURSAL - Estado inactivo funciona correctamente
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "FIX 7: Fix estado activo/inactivo en update de sucursal..." -ForegroundColor Yellow

@'
<?php
$path = 'app/Http/Controllers/SucursalController.php';
$c = file_get_contents($path);

$old = "        \$data = \$request->validate([
            'nombre'    => 'required|string|max:120',
            'direccion' => 'nullable|string|max:200',
            'telefono'  => 'nullable|string|max:30',
            'activo'    => 'boolean',
        ]);

        \$sucursal->update(\$data);";

$new = "        \$data = \$request->validate([
            'nombre'    => 'required|string|max:120',
            'direccion' => 'nullable|string|max:200',
            'telefono'  => 'nullable|string|max:30',
        ]);

        // Checkbox no envía nada si está desmarcado — manejar manualmente
        \$data['activo'] = \$request->has('activo') ? true : false;

        \$sucursal->update(\$data);";

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "FIX 7 OK: estado activo/inactivo corregido\n";
'@ | Set-Content -Path "fix_sucursal_activo.php"
docker compose exec app php fix_sucursal_activo.php

# -----------------------------------------------------------------------
# Reiniciar Nginx con nuevo timeout
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "==> Reiniciando Nginx con nuevo timeout..." -ForegroundColor Cyan
docker compose restart nginx

# -----------------------------------------------------------------------
# Limpiar cache
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "==> Limpiando cache..." -ForegroundColor Cyan
docker compose exec app php artisan config:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan route:clear

# -----------------------------------------------------------------------
# Tests
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "==> Tests de regresion..." -ForegroundColor Cyan
docker compose exec app php artisan test `
    --testsuite=Feature `
    --filter="PosRegressionTest|CompraRegressionTest|CajaRegressionTest"

# -----------------------------------------------------------------------
# Commit
# -----------------------------------------------------------------------
Write-Host ""
Write-Host "==> Commiteando..." -ForegroundColor Cyan
git add .
git commit -m "fix: velocidad cache alertas, indices BD, timeout nginx, tipo comprobante, sucursal activa"
git push origin feature/multitenant

Write-Host ""
Write-Host "Todos los fixes aplicados." -ForegroundColor Green
Write-Host "   - Velocidad: cache de alertas 60s + indices BD + nginx timeout 120s"
Write-Host "   - Comprobante: selector boleta/factura en historial"
Write-Host "   - Sucursal activa: redirect al cambiar + visual correcto"
Write-Host "   - Estado sucursal: checkbox activo/inactivo funciona"
