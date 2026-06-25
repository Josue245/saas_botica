<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComprobanteSunat extends Model
{
    protected $table = 'comprobantes_electronicos';

    protected $fillable = [
        'tenant_id', 'venta_id', 'sucursal_id', 'tipo',
        'serie', 'correlativo', 'numero', 'estado',
        'hash', 'cdr_url', 'xml_url', 'xml_contenido',
        'error_mensaje', 'enviado_at', 'aceptado_at',
    ];

    protected $casts = [
        'enviado_at'  => 'datetime',
        'aceptado_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function estaAceptado(): bool
    {
        return $this->estado === 'aceptado';
    }

    public function puedeReenviar(): bool
    {
        return in_array($this->estado, ['pendiente', 'rechazado', 'observado']);
    }

    /**
     * Número formateado: B001-00000001
     */
    public static function formatearNumero(string $serie, int $correlativo): string
    {
        return $serie . '-' . str_pad((string) $correlativo, 8, '0', STR_PAD_LEFT);
    }
}
