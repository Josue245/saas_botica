<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * TenantScope — se aplica automáticamente a TODOS los modelos que usen HasTenant.
 *
 * Comportamiento:
 * - Si hay un tenant activo en el contenedor (app('tenant')), filtra por su ID.
 * - Si NO hay tenant (rutas públicas, artisan commands, superadmin), no filtra.
 * - Para omitir el scope en casos específicos: Modelo::sinTenant()->get()
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->bound('tenant') && app('tenant') !== null) {
            $builder->where(
                $model->getTable() . '.tenant_id',
                app('tenant')->id
            );
        }
    }
}
