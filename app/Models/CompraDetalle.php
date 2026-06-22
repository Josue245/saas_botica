<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompraDetalle extends Model
{
    protected $table = 'compra_detalles';
    protected $fillable = [
        'compra_id', 'producto_id', 'descripcion', 'cantidad',
        'precio_compra', 'subtotal', 'lote', 'fecha_vencimiento',
    ];
    protected $casts = [
        'fecha_vencimiento' => 'date',
        'precio_compra' => 'decimal:2', 'subtotal' => 'decimal:2',
    ];

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
