<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tenant extends Model
{
    protected $fillable = [
        'uuid', 'slug', 'razon_social', 'ruc', 'email',
        'telefono', 'plan_id', 'plan_expira_at', 'estado',
        'trial_expira_at', 'metadata',
    ];

    protected $casts = [
        'plan_expira_at'  => 'datetime',
        'trial_expira_at' => 'datetime',
        'metadata'        => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            $tenant->uuid ??= (string) Str::uuid();
        });
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function sucursales(): HasMany
    {
        return $this->hasMany(Sucursal::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function suscripciones(): HasMany
    {
        return $this->hasMany(Suscripcion::class);
    }

    public function correlativos(): HasMany
    {
        return $this->hasMany(Correlativo::class);
    }

    /**
     * ¿El tenant puede seguir operando?
     */
    public function estaActivo(): bool
    {
        return in_array($this->estado, ['activo', 'trial'], true)
            && ($this->plan_expira_at === null || $this->plan_expira_at->isFuture());
    }

    /**
     * ¿El tenant está dentro del límite de un recurso?
     */
    public function dentroLimite(string $recurso): bool
    {
        $plan = $this->plan;

        if ($plan->esIlimitado("max_{$recurso}")) {
            return true;
        }

        return match ($recurso) {
            'usuarios'   => $this->usuarios()->count() < $plan->max_usuarios,
            'sucursales' => $this->sucursales()->count() < $plan->max_sucursales,
            'productos'  => true, // se evalúa con scope en Fase 3
            default      => false,
        };
    }

    /**
     * ¿El tenant puede usar una feature?
     */
    public function puedeUsar(string $feature): bool
    {
        return $this->plan->permite($feature);
    }

    /**
     * Obtener la sucursal principal (la primera creada).
     */
    public function sucursalPrincipal(): ?Sucursal
    {
        return $this->sucursales()->orderBy('id')->first();
    }
}
