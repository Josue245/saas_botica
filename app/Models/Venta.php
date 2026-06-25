<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasTenant;
use App\Models\ComprobanteSunat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    use HasTenant, Auditable;

    protected $table = 'ventas';
    protected $fillable = [
        'numero_comprobante', 'tipo_comprobante', 'cliente_id', 'user_id',
        'subtotal', 'igv', 'descuento', 'total', 'metodo_pago', 'estado', 'con_receta',
    ];
    protected $casts = [
        'subtotal' => 'decimal:2', 'igv' => 'decimal:2',
        'descuento' => 'decimal:2', 'total' => 'decimal:2',
        'con_receta' => 'boolean',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class);
    }
    public function comprobanteSunat(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ComprobanteSunat::class, 'venta_id');
    }
}
