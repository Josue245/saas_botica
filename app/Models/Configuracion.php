<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Configuracion extends Model
{
    protected $table = 'configuraciones';
    protected $fillable = ['clave', 'valor'];

    protected static ?array $cache = null;

    public static function defaults(): array
    {
        return [
            'empresa_nombre' => 'Mi Botica',
            'empresa_ruc' => '20512345678',
            'empresa_direccion' => 'Av. Principal 123, Lima',
            'empresa_telefono' => '(01) 456-7890',
            'empresa_email' => 'contacto@mibotica.test',
            'igv_porcentaje' => '18',
            'moneda_simbolo' => 'S/',
            'serie_boleta' => 'B001',
            'serie_factura' => 'F001',
            'mensaje_ticket' => '¡Gracias por su compra! Conserve este comprobante.',
        ];
    }

    /**
     * Devuelve el valor de una clave (con caché por request y fallback a defaults).
     */
    public static function valor(string $clave, mixed $default = null): mixed
    {
        if (! Schema::hasTable('configuraciones')) {
            return $default ?? (static::defaults()[$clave] ?? null);
        }

        if (static::$cache === null) {
            static::$cache = static::pluck('valor', 'clave')->all();
        }

        return static::$cache[$clave]
            ?? $default
            ?? (static::defaults()[$clave] ?? null);
    }

    /**
     * Todas las claves combinando defaults + guardadas.
     */
    public static function todas(): array
    {
        $guardadas = Schema::hasTable('configuraciones')
            ? static::pluck('valor', 'clave')->all()
            : [];

        return array_merge(static::defaults(), array_filter($guardadas, fn ($v) => $v !== null));
    }

    public static function guardar(array $datos): void
    {
        foreach ($datos as $clave => $valor) {
            static::updateOrCreate(['clave' => $clave], ['valor' => $valor]);
        }
        static::$cache = null;
    }
}
