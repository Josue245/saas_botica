<?php

namespace App\Http\Middleware;

use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;

/**
 * CheckPlanLimits — verifica límites del plan antes de crear recursos.
 *
 * Uso en rutas:
 *   ->middleware('plan.limit:usuarios')
 *   ->middleware('plan.limit:productos')
 *   ->middleware('plan.limit:sucursales')
 *
 * Uso en controllers:
 *   $this->middleware('plan.limit:productos')->only('store');
 */
class CheckPlanLimits
{
    public function __construct(private TenantManager $manager) {}

    public function handle(Request $request, Closure $next, string $recurso): \Symfony\Component\HttpFoundation\Response
    {
        $tenant = $this->manager->get();

        if (!$tenant) {
            return $next($request);
        }

        if (!$tenant->dentroLimite($recurso)) {
            $plan = $tenant->plan;
            $limite = match ($recurso) {
                'usuarios'   => $plan->max_usuarios,
                'sucursales' => $plan->max_sucursales,
                'productos'  => $plan->max_productos,
                default      => '?',
            };

            $mensaje = "Has alcanzado el límite de {$recurso} de tu plan {$plan->nombre} ({$limite} máximo).";

            if ($request->expectsJson()) {
                return response()->json(['error' => $mensaje], 402);
            }

            return back()->with('error',
                $mensaje . ' <a href="' . route('billing.index') . '" class="underline font-medium">Actualizar plan →</a>'
            );
        }

        return $next($request);
    }
}
