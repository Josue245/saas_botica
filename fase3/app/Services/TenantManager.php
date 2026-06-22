<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\App;

/**
 * TenantManager — singleton que mantiene el contexto del tenant activo.
 *
 * Se registra en AppServiceProvider como singleton.
 * Se setea en el middleware ResolveTenant.
 * Se usa en HasTenant y en cualquier parte que necesite saber el tenant actual.
 */
class TenantManager
{
    private ?Tenant $current = null;

    public function set(Tenant $tenant): void
    {
        $this->current = $tenant;
        // Registrar en el contenedor para que TenantScope pueda accederlo
        App::instance('tenant', $tenant);
    }

    public function get(): ?Tenant
    {
        return $this->current;
    }

    public function id(): ?int
    {
        return $this->current?->id;
    }

    public function slug(): ?string
    {
        return $this->current?->slug;
    }

    public function plan(): ?string
    {
        return $this->current?->plan?->slug;
    }

    public function puedeUsar(string $feature): bool
    {
        return $this->current?->puedeUsar($feature) ?? false;
    }

    public function dentro(): bool
    {
        return $this->current !== null;
    }

    public function limpiar(): void
    {
        $this->current = null;
        App::forgetInstance('tenant');
    }
}
