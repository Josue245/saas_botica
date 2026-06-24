<?php

namespace App\Jobs;

use App\Models\Suscripcion;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckExpiredSubscriptionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // 1. Suspender tenants con plan vencido
        $tenantsSuspendidos = Tenant::where('estado', 'activo')
            ->whereNotNull('plan_expira_at')
            ->where('plan_expira_at', '<', now())
            ->get();

        foreach ($tenantsSuspendidos as $tenant) {
            $tenant->update(['estado' => 'suspendido']);

            // Marcar suscripción como vencida
            Suscripcion::where('tenant_id', $tenant->id)
                ->where('estado', 'activa')
                ->update(['estado' => 'vencida']);

            Log::info("Tenant suspendido por vencimiento", [
                'tenant_id'   => $tenant->id,
                'slug'        => $tenant->slug,
                'expiro_at'   => $tenant->plan_expira_at,
            ]);

            // TODO: enviar email de notificación
            // Mail::to($tenant->email)->send(new SuscripcionVencida($tenant));
        }

        // 2. Suspender trials vencidos
        $trialsVencidos = Tenant::where('estado', 'trial')
            ->whereNotNull('trial_expira_at')
            ->where('trial_expira_at', '<', now())
            ->get();

        foreach ($trialsVencidos as $tenant) {
            $tenant->update(['estado' => 'suspendido']);

            Log::info("Trial suspendido por vencimiento", [
                'tenant_id' => $tenant->id,
                'slug'      => $tenant->slug,
            ]);
        }

        // 3. Notificar 7 días antes del vencimiento
        $proximos = Tenant::where('estado', 'activo')
            ->whereNotNull('plan_expira_at')
            ->whereBetween('plan_expira_at', [now(), now()->addDays(7)])
            ->get();

        foreach ($proximos as $tenant) {
            Log::info("Tenant próximo a vencer", [
                'tenant_id' => $tenant->id,
                'slug'      => $tenant->slug,
                'dias'      => now()->diffInDays($tenant->plan_expira_at),
            ]);

            // TODO: Mail::to($tenant->email)->send(new SuscripcionProximaVencer($tenant));
        }

        Log::info("CheckExpiredSubscriptionsJob completado", [
            'suspendidos' => $tenantsSuspendidos->count() + $trialsVencidos->count(),
            'proximos'    => $proximos->count(),
        ]);
    }
}
