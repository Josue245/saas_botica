<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * HasTenant — trait que convierte cualquier modelo en tenant-aware.
 *
 * Al agregarlo a un modelo:
 * 1. Aplica TenantScope globalmente (todas las queries filtran por tenant_id)
 * 2. Auto-asigna tenant_id al crear registros si hay un tenant activo
 * 3. Expone el método sinTenant() para queries cross-tenant (superadmin)
 */
trait HasTenant
{
    public static function bootHasTenant(): void
    {
        // 1. Aplicar el scope global en cada query
        static::addGlobalScope(new TenantScope);

        // 2. Auto-asignar tenant_id al crear
        static::creating(function ($model) {
            if (
                app()->bound('tenant')
                && app('tenant') !== null
                && empty($model->tenant_id)
            ) {
                $model->tenant_id = app('tenant')->id;
            }

            // Auto-asignar sucursal_id si el modelo la tiene y hay sucursal activa
            if (
                app()->bound('sucursal')
                && app('sucursal') !== null
                && in_array('sucursal_id', $model->getFillable(), true)
                && empty($model->sucursal_id)
            ) {
                $model->sucursal_id = app('sucursal')->id;
            }
        });
    }

    /**
     * Relación al tenant propietario.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Query sin el filtro de tenant.
     * Usar SOLO en contextos de superadmin o comandos de consola.
     *
     * Uso: Producto::sinTenant()->where('activo', true)->get()
     */
    public static function sinTenant(): \Illuminate\Database\Eloquent\Builder
    {
        return static::withoutGlobalScope(TenantScope::class);
    }
}
