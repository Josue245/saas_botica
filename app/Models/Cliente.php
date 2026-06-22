<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use Auditable;

    protected $table = 'clientes';
    protected $fillable = ['nombre', 'tipo_documento', 'numero_documento', 'telefono', 'email', 'direccion', 'puntos', 'activo'];
    protected $casts = ['activo' => 'boolean'];

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }
}
