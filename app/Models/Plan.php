<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'nombre', 'slug', 'precio_mensual',
        'max_usuarios', 'max_sucursales', 'max_productos',
        'max_ventas_mes', 'features', 'activo',
    ];

    protected $casts = [
        'features'       => 'array',
        'activo'         => 'boolean',
        'precio_mensual' => 'decimal:2',
    ];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function suscripciones(): HasMany
    {
        return $this->hasMany(Suscripcion::class);
    }

    /**
     * ¿El plan permite una feature específica?
     */
    public function permite(string $feature): bool
    {
        return (bool) ($this->features[$feature] ?? false);
    }

    /**
     * ¿El límite es ilimitado? (-1 significa sin límite)
     */
    public function esIlimitado(string $recurso): bool
    {
        return (int) ($this->$recurso ?? 0) === -1;
    }

    public static function free(): static
    {
        return static::where('slug', 'free')->firstOrFail();
    }
}
