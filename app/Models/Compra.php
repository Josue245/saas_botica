<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    use Auditable;

    protected $table = 'compras';
    protected $fillable = [
        'numero_documento', 'proveedor_id', 'user_id', 'fecha',
        'subtotal', 'igv', 'total', 'estado', 'estado_pago', 'observacion',
    ];
    protected $casts = [
        'fecha' => 'date',
        'subtotal' => 'decimal:2', 'igv' => 'decimal:2', 'total' => 'decimal:2',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(CompraDetalle::class);
    }
}
