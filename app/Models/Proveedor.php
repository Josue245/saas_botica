<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    use Auditable;

    protected $table = 'proveedores';
    protected $fillable = ['razon_social', 'ruc', 'contacto', 'telefono', 'email', 'direccion', 'condicion_pago', 'activo'];
    protected $casts = ['activo' => 'boolean'];

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }
}
