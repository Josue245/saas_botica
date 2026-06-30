<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupTenantCommand extends Command
{
    protected $signature   = 'tenant:backup {--tenant_id= : ID del tenant (omitir para todos)}';
    protected $description = 'Genera backup SQL filtrado por tenant';

    private array $tablas = [
        'categorias', 'proveedores', 'productos', 'clientes',
        'ventas', 'venta_detalles', 'compras', 'compra_detalles',
        'caja_sesiones', 'caja_movimientos', 'ajuste_inventarios',
        'configuraciones', 'auditorias', 'comprobantes_electronicos',
    ];

    public function handle(): int
    {
        $tenantId = $this->option('tenant_id');
        $tenants  = $tenantId
            ? Tenant::where('id', $tenantId)->get()
            : Tenant::all();

        foreach ($tenants as $tenant) {
            $this->info("Generando backup para: {$tenant->razon_social} (ID: {$tenant->id})");
            $this->generarBackup($tenant);
        }

        return self::SUCCESS;
    }

    private function generarBackup(Tenant $tenant): void
    {
        $sql  = "-- Backup Tenant: {$tenant->razon_social}\n";
        $sql .= "-- RUC: {$tenant->ruc}\n";
        $sql .= "-- Fecha: " . now()->toDateTimeString() . "\n";
        $sql .= "-- Generado por Mi Botica SaaS\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($this->tablas as $tabla) {
            $filas = DB::table($tabla)->where('tenant_id', $tenant->id)->get();
            if ($filas->isEmpty()) continue;

            $sql .= "-- Tabla: {$tabla}\n";
            $cols    = array_keys((array) $filas->first());
            $colList = '`' . implode('`, `', $cols) . '`';

            foreach ($filas as $fila) {
                $valores = array_map(function ($v) {
                    return $v === null ? 'NULL' : "'" . addslashes((string) $v) . "'";
                }, (array) $fila);
                $sql .= "INSERT INTO `{$tabla}` ({$colList}) VALUES (" . implode(', ', $valores) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        // Guardar en storage/app/backups/tenant_{id}/
        $nombre = "tenant_{$tenant->id}/" . now()->format('Y/m') . "/backup_" . now()->format('Ymd_His') . ".sql";
        Storage::disk('local')->put("backups/{$nombre}", $sql);

        $this->info("  Backup guardado: storage/app/backups/{$nombre}");
        $this->info("  Tamaño: " . strlen($sql) . " bytes");
    }
}
