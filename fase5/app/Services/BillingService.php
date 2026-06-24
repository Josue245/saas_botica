<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Suscripcion;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BillingService
{
    private string $secretKey;
    private string $baseUrl = 'https://api.culqi.com/v2';

    public function __construct()
    {
        $this->secretKey = config('services.culqi.secret_key', '');
    }

    /**
     * Procesa un pago con Culqi y activa la suscripción del tenant.
     *
     * @param Tenant $tenant
     * @param Plan $plan
     * @param string $culqiToken  Token generado por Culqi.js en el frontend
     * @return Suscripcion
     * @throws \RuntimeException
     */
    public function suscribir(Tenant $tenant, Plan $plan, string $culqiToken): Suscripcion
    {
        // Plan free no requiere pago
        if ($plan->precio_mensual == 0) {
            return $this->activarPlanGratis($tenant, $plan);
        }

        // Crear cargo en Culqi
        $response = Http::withToken($this->secretKey)
            ->timeout(30)
            ->post("{$this->baseUrl}/charges", [
                'amount'        => (int) ($plan->precio_mensual * 100), // en céntimos
                'currency_code' => 'PEN',
                'email'         => $tenant->email,
                'source_id'     => $culqiToken,
                'description'   => "Suscripción {$plan->nombre} - {$tenant->razon_social}",
                'metadata'      => [
                    'tenant_id' => $tenant->id,
                    'plan_id'   => $plan->id,
                    'plan_slug' => $plan->slug,
                ],
            ]);

        Log::info('Culqi charge response', [
            'tenant_id' => $tenant->id,
            'plan'      => $plan->slug,
            'status'    => $response->status(),
        ]);

        if (!$response->successful()) {
            $error = $response->json('merchant_message')
                ?? $response->json('user_message')
                ?? 'Error al procesar el pago';
            throw new \RuntimeException($error);
        }

        $data = $response->json();

        if (($data['object'] ?? '') !== 'charge' || ($data['outcome']['type'] ?? '') !== 'venta_exitosa') {
            throw new \RuntimeException(
                $data['merchant_message'] ?? 'El pago fue rechazado'
            );
        }

        return $this->activarSuscripcion($tenant, $plan, 'culqi', $data['id'], $plan->precio_mensual);
    }

    /**
     * Activa plan free sin cobro.
     */
    public function activarPlanGratis(Tenant $tenant, Plan $plan): Suscripcion
    {
        return $this->activarSuscripcion($tenant, $plan, null, null, 0);
    }

    /**
     * Crea la suscripción y actualiza el tenant.
     */
    private function activarSuscripcion(
        Tenant $tenant,
        Plan $plan,
        ?string $metodoPago,
        ?string $referencia,
        float $precio
    ): Suscripcion {
        // Cancelar suscripción activa anterior
        Suscripcion::where('tenant_id', $tenant->id)
            ->where('estado', 'activa')
            ->update(['estado' => 'cancelada']);

        $suscripcion = Suscripcion::create([
            'tenant_id'       => $tenant->id,
            'plan_id'         => $plan->id,
            'estado'          => 'activa',
            'inicia_at'       => now(),
            'expira_at'       => now()->addMonth(),
            'precio_pagado'   => $precio,
            'moneda'          => 'PEN',
            'metodo_pago'     => $metodoPago,
            'referencia_pago' => $referencia,
        ]);

        $tenant->update([
            'plan_id'        => $plan->id,
            'plan_expira_at' => now()->addMonth(),
            'estado'         => 'activo',
        ]);

        return $suscripcion;
    }

    /**
     * Verifica si un tenant puede usar una feature de su plan.
     */
    public function puedeUsar(Tenant $tenant, string $feature): bool
    {
        return $tenant->plan->permite($feature);
    }

    /**
     * Verifica si un tenant está dentro del límite de un recurso.
     */
    public function dentroLimite(Tenant $tenant, string $recurso): bool
    {
        return $tenant->dentroLimite($recurso);
    }
}
