<?php

namespace App\Http\Middleware;

use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureTenantActive — bloquea el acceso si el tenant no tiene suscripción vigente.
 * Se aplica DESPUÉS de ResolveTenant en rutas que requieren tenant activo.
 */
class EnsureTenantActive
{
    public function __construct(private TenantManager $manager) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->manager->dentro()) {
            abort(404, 'Empresa no encontrada.');
        }

        $tenant = $this->manager->get();

        if (!$tenant->estaActivo()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Suscripción suspendida.',
                    'code'  => 'SUBSCRIPTION_INACTIVE',
                ], 402);
            }

            return redirect()->route('billing.suspendido')
                ->with('error', 'Tu suscripción está suspendida. Renueva para continuar.');
        }

        return $next($request);
    }
}
