<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Correlativo extends Model
{
    protected $fillable = [
        'tenant_id', 'sucursal_id', 'tipo', 'serie', 'ultimo',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Genera el siguiente número de comprobante de forma atómica.
     * Usa lockForUpdate() para evitar race conditions bajo concurrencia.
     *
     * @return string  Ej: "B001-00000001"
     */
    public static function siguiente(int $tenantId, int $sucursalId, string $tipo, string $serie): string
    {
        return DB::transaction(function () use ($tenantId, $sucursalId, $tipo, $serie) {
            $row = static::lockForUpdate()
                ->where('tenant_id', $tenantId)
                ->where('sucursal_id', $sucursalId)
                ->where('tipo', $tipo)
                ->where('serie', $serie)
                ->firstOrFail();

            $row->increment('ultimo');
            $row->refresh();

            return $serie . '-' . str_pad((string) $row->ultimo, 8, '0', STR_PAD_LEFT);
        });
    }
}
