<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto extends Model
{
    use HasFactory;
    use Auditable;

    protected $table = 'productos';
    protected $fillable = [
        'codigo_barras', 'nombre', 'principio_activo', 'presentacion', 'concentracion',
        'categoria_id', 'proveedor_id', 'laboratorio', 'precio_compra', 'precio_venta',
        'stock', 'stock_minimo', 'lote', 'fecha_vencimiento', 'requiere_receta', 'controlado', 'activo',
    ];
    protected $casts = [
        'fecha_vencimiento' => 'date',
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'requiere_receta' => 'boolean',
        'controlado' => 'boolean',
        'activo' => 'boolean',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function getStockBajoAttribute(): bool
    {
        return $this->stock <= $this->stock_minimo;
    }
}
