<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Suscripcion;
use App\Services\BillingService;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(
        private BillingService $billing,
        private TenantManager $tenantManager
    ) {}

    /**
     * Página de planes y suscripción actual.
     */
    public function index(): View
    {
        $tenant = $this->tenantManager->get() ?? auth()->user()?->tenant;
        \Illuminate\Support\Facades\Log::info("BillingController tenant", [
            "manager" => $this->tenantManager->get()?->id,
            "auth_tenant" => auth()->user()?->tenant_id,
            "tenant" => $tenant?->id,
        ]);
        if (!$tenant) return redirect()->route('login');
        $planes = Plan::where('activo', true)->orderBy('precio_mensual')->get();
        $suscripcionActual = Suscripcion::where('tenant_id', $tenant->id)
            ->where('estado', 'activa')
            ->with('plan')
            ->latest()
            ->first();

        // DEBUG: dump datos
        if (request()->has('debug')) {
            dd(compact('tenant', 'planes', 'suscripcionActual'));
        }
        return view('billing.index', compact('tenant', 'planes', 'suscripcionActual'));
    }

    /**
     * Página de checkout para un plan específico.
     */
    public function checkout(Plan $plan): View
    {
        $tenant = $this->tenantManager->get() ?? auth()->user()?->tenant;
        if (!$tenant) return redirect()->route('login');

        // Si es plan free, activar directo sin pago
        if ($plan->precio_mensual == 0) {
            $this->billing->activarPlanGratis($tenant, $plan);
            return redirect()->route('billing.index')
                ->with('ok', 'Plan Free activado correctamente.');
        }

        return view('billing.checkout', [
            'tenant'       => $tenant,
            'plan'         => $plan,
            'culqiPublicKey' => config('services.culqi.public_key'),
        ]);
    }

    /**
     * Procesa el pago con el token de Culqi.
     */
    public function pagar(Request $request, Plan $plan): RedirectResponse
    {
        $request->validate([
            'culqi_token' => 'required|string',
        ]);

        $tenant = $this->tenantManager->get() ?? auth()->user()?->tenant;
        if (!$tenant) return redirect()->route('login');

        try {
            $suscripcion = $this->billing->suscribir($tenant, $plan, $request->culqi_token);

            return redirect()->route('billing.index')
                ->with('ok', "¡Plan {$plan->nombre} activado! Válido hasta " .
                    $suscripcion->expira_at->format('d/m/Y'));
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Error en el pago: ' . $e->getMessage());
        }
    }

    /**
     * Historial de suscripciones del tenant.
     */
    public function historial(): View
    {
        $tenant = $this->tenantManager->get() ?? auth()->user()?->tenant;
        if (!$tenant) return redirect()->route('login');
        $suscripciones = Suscripcion::where('tenant_id', $tenant->id)
            ->with('plan')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('billing.historial', compact('suscripciones'));
    }

    /**
     * Webhook de Culqi para eventos de pago (pagos recurrentes, etc).
     * Esta ruta debe estar excluida de CSRF.
     */
    public function webhook(Request $request): \Illuminate\Http\JsonResponse
    {
        $payload = $request->all();

        \Illuminate\Support\Facades\Log::info('Culqi webhook recibido', $payload);

        // Verificar firma del webhook
        $hmac = hash_hmac('sha256', $request->getContent(), config('services.culqi.webhook_secret', ''));
        if ($request->header('x-culqi-hmac-sha256') !== $hmac) {
            return response()->json(['error' => 'Firma inválida'], 401);
        }

        $tipo = $payload['type'] ?? '';

        match ($tipo) {
            'charge.succeeded' => $this->handleChargeSucceeded($payload),
            'charge.failed'    => $this->handleChargeFailed($payload),
            default            => null,
        };

        return response()->json(['ok' => true]);
    }

    private function handleChargeSucceeded(array $payload): void
    {
        $metadata = $payload['data']['object']['metadata'] ?? [];
        $tenantId = $metadata['tenant_id'] ?? null;
        $planId   = $metadata['plan_id'] ?? null;

        if (!$tenantId || !$planId) return;

        $tenant = \App\Models\Tenant::find($tenantId);
        $plan   = Plan::find($planId);

        if ($tenant && $plan) {
            $this->billing->activarSuscripcion($tenant, $plan, 'culqi',
                $payload['data']['object']['id'] ?? null,
                $plan->precio_mensual
            );
        }
    }

    private function handleChargeFailed(array $payload): void
    {
        \Illuminate\Support\Facades\Log::warning('Culqi charge failed', $payload);
    }
}
