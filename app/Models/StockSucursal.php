<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockSucursal extends Model
{
    public $timestamps = false;

    protected $table = 'stock_sucursales';

    protected $fillable = [
        'tenant_id', 'sucursal_id', 'producto_id', 'stock', 'stock_minimo',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function tieneStockBajo(): bool
    {
        return $this->stock <= $this->stock_minimo;
    }
}
