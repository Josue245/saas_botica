<?php

namespace App\Models\Concerns;

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Registra automáticamente en la bitácora de auditoría las operaciones
 * de creación, actualización y eliminación del modelo.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn ($modelo) => $modelo->auditar('creó'));
        static::updated(fn ($modelo) => $modelo->auditar('actualizó'));
        static::deleted(fn ($modelo) => $modelo->auditar('eliminó'));
    }

    protected function auditar(string $accion): void
    {
        // Solo registra cuando hay un usuario autenticado (evita ruido de seeders/migraciones)
        if (! Auth::check() || ! Schema::hasTable('auditorias')) {
            return;
        }

        Auditoria::registrar(
            $accion,
            class_basename($this),
            (int) $this->getKey(),
            $this->descripcionAuditoria()
        );
    }

    protected function descripcionAuditoria(): string
    {
        $etiqueta = $this->nombre
            ?? $this->razon_social
            ?? $this->numero_comprobante
            ?? $this->numero_documento
            ?? $this->name
            ?? ('#' . $this->getKey());

        return class_basename($this) . ': ' . $etiqueta;
    }
}
