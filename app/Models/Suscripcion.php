<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Suscripcion extends Model
{
    protected $fillable = [
        'tenant_id', 'plan_id', 'estado', 'inicia_at',
        'expira_at', 'precio_pagado', 'moneda',
        'metodo_pago', 'referencia_pago',
    ];

    protected $casts = [
        'inicia_at'    => 'datetime',
        'expira_at'    => 'datetime',
        'precio_pagado' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function estaVigente(): bool
    {
        return $this->estado === 'activa'
            && ($this->expira_at === null || $this->expira_at->isFuture());
    }
}
