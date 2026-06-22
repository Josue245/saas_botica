<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AjusteInventario extends Model
{
    use HasTenant, Auditable;

    protected $table = 'ajuste_inventarios';
    protected $fillable = [
        'producto_id', 'user_id', 'tipo', 'cantidad',
        'stock_anterior', 'stock_nuevo', 'motivo',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
