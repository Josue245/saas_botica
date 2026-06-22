<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CajaSesion extends Model
{
    use Auditable;

    protected $table = 'caja_sesiones';
    protected $fillable = [
        'user_id', 'cerrado_por', 'monto_inicial', 'monto_esperado',
        'monto_final', 'diferencia', 'estado', 'observacion', 'abierta_at', 'cerrada_at',
    ];
    protected $casts = [
        'monto_inicial' => 'decimal:2', 'monto_esperado' => 'decimal:2',
        'monto_final' => 'decimal:2', 'diferencia' => 'decimal:2',
        'abierta_at' => 'datetime', 'cerrada_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(CajaMovimiento::class);
    }
}
