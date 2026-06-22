<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $fillable = [
        'tenant_id', 'nombre', 'direccion', 'ubigeo',
        'telefono', 'activo', 'serie_boleta', 'serie_factura',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function correlativos(): HasMany
    {
        return $this->hasMany(Correlativo::class);
    }

    public function stockProductos(): HasMany
    {
        return $this->hasMany(StockSucursal::class);
    }
}
