<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ResolveTenant — identifica el tenant activo en cada request.
 *
 * Estrategias (en orden de prioridad):
 *
 * 1. Subdominio (producción):
 *    botica-leon.mibotica.pe → slug = "botica-leon"
 *
 * 2. Header X-Tenant-ID (desarrollo local / API / testing):
 *    X-Tenant-ID: 1  →  tenant con id=1
 *    X-Tenant-Slug: piloto  →  tenant con slug="piloto"
 *
 * 3. Query param ?_tenant=piloto (solo en APP_ENV=local, para pruebas rápidas)
 */
class ResolveTenant
{
    public function __construct(private TenantManager $manager) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolverTenant($request);

        if (!$tenant) {
            // Si es una ruta pública (landing, register) no requerimos tenant
            return $next($request);
        }

        if (!$tenant->estaActivo()) {
            abort(402, 'Suscripción suspendida. Contacta a soporte en soporte@mibotica.pe');
        }

        $this->manager->set($tenant);

        // Resolver sucursal activa del usuario autenticado
        if (auth()->check() && auth()->user()->sucursal_id) {
            $sucursal = auth()->user()->sucursal;
            if ($sucursal && (int) $sucursal->tenant_id === (int) $tenant->id) {
                app()->instance('sucursal', $sucursal);
            }
        }

        return $next($request);
    }

    private function resolverTenant(Request $request): ?Tenant
    {
        // 1. Por subdominio (producción)
        $host = $request->getHost();
        $baseDomain = config('app.base_domain', 'mibotica.pe');

        if (str_ends_with($host, '.' . $baseDomain)) {
            $slug = str_replace('.' . $baseDomain, '', $host);
            if ($slug && $slug !== 'www') {
                return Tenant::where('slug', $slug)->first();
            }
        }

        // 2. Por header X-Tenant-Slug (desarrollo / API)
        if ($slug = $request->header('X-Tenant-Slug')) {
            return Tenant::where('slug', $slug)->first();
        }

        // 3. Por header X-Tenant-ID (desarrollo / API)
        if ($id = $request->header('X-Tenant-ID')) {
            return Tenant::find($id);
        }

        // 4. Por query param (solo local, para pruebas rápidas en el navegador)
        if (config('app.env') === 'local' && $slug = $request->query('_tenant')) {
            return Tenant::where('slug', $slug)->first();
        }

        return null;
    }
}
