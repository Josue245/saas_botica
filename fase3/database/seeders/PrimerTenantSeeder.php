<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * PrimerTenantSeeder — Fase 3 del roadmap multi-tenant.
 *
 * Crea el Tenant 1 (el cliente actual del sistema mono-tenant) y
 * hace backfill de TODOS los registros existentes asignándoles tenant_id=1.
 *
 * Es idempotente: si el tenant ya existe, solo hace el backfill.
 */
class PrimerTenantSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('==> Creando Tenant 1 (cliente piloto)...');

        // 1. Obtener plan free
        $planId = DB::table('planes')->where('slug', 'free')->value('id');
        if (!$planId) {
            $this->command->error('No se encontró el plan free. Asegúrate de haber corrido las migraciones de Fase 1.');
            return;
        }

        // 2. Crear tenant si no existe
        $tenantId = DB::table('tenants')->where('slug', 'piloto')->value('id');
        if (!$tenantId) {
            $tenantId = DB::table('tenants')->insertGetId([
                'uuid'            => (string) Str::uuid(),
                'slug'            => 'piloto',
                'razon_social'    => 'Mi Botica (Piloto)',
                'ruc'             => null,
                'email'           => 'admin@mibotica.pe',
                'telefono'        => null,
                'plan_id'         => $planId,
                'estado'          => 'activo',
                'plan_expira_at'  => now()->addYear(),
                'trial_expira_at' => null,
                'metadata'        => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
            $this->command->info("   Tenant creado con ID: {$tenantId}");
        } else {
            $this->command->warn("   Tenant 'piloto' ya existe con ID: {$tenantId}. Solo se hará backfill.");
        }

        // 3. Crear sucursal principal si no existe
        $sucursalId = DB::table('sucursales')->where('tenant_id', $tenantId)->value('id');
        if (!$sucursalId) {
            $sucursalId = DB::table('sucursales')->insertGetId([
                'tenant_id'     => $tenantId,
                'nombre'        => 'Sucursal Principal',
                'direccion'     => null,
                'ubigeo'        => null,
                'telefono'      => null,
                'activo'        => true,
                'serie_boleta'  => 'B001',
                'serie_factura' => 'F001',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
            $this->command->info("   Sucursal principal creada con ID: {$sucursalId}");
        }

        // 4. Crear correlativos iniciales
        $tiposCorrelativos = [
            ['tipo' => 'boleta',        'serie' => 'B001'],
            ['tipo' => 'factura',       'serie' => 'F001'],
            ['tipo' => 'orden_compra',  'serie' => 'OC-'],
        ];

        foreach ($tiposCorrelativos as $corr) {
            $existe = DB::table('correlativos')
                ->where('tenant_id', $tenantId)
                ->where('sucursal_id', $sucursalId)
                ->where('tipo', $corr['tipo'])
                ->exists();

            if (!$existe) {
                // Detectar el último correlativo ya usado en ventas/compras existentes
                $ultimo = 0;
                if ($corr['tipo'] === 'boleta') {
                    $ultimo = DB::table('ventas')
                        ->where('tipo_comprobante', 'boleta')
                        ->whereNotNull('numero_comprobante')
                        ->count();
                } elseif ($corr['tipo'] === 'factura') {
                    $ultimo = DB::table('ventas')
                        ->where('tipo_comprobante', 'factura')
                        ->whereNotNull('numero_comprobante')
                        ->count();
                } elseif ($corr['tipo'] === 'orden_compra') {
                    $ultimo = DB::table('compras')
                        ->whereNotNull('numero_documento')
                        ->count();
                }

                DB::table('correlativos')->insert([
                    'tenant_id'   => $tenantId,
                    'sucursal_id' => $sucursalId,
                    'tipo'        => $corr['tipo'],
                    'serie'       => $corr['serie'],
                    'ultimo'      => $ultimo,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $this->command->info("   Correlativo {$corr['tipo']} inicializado en: {$ultimo}");
            }
        }

        // 5. BACKFILL — asignar tenant_id a todos los registros existentes
        $this->command->info('==> Iniciando backfill (tenant_id y sucursal_id)...');

        $tablasSolo = [
            'categorias', 'proveedores', 'productos',
            'clientes', 'venta_detalles', 'compra_detalles',
            'caja_movimientos', 'auditorias',
        ];

        foreach ($tablasSolo as $tabla) {
            $afectados = DB::table($tabla)
                ->whereNull('tenant_id')
                ->update(['tenant_id' => $tenantId]);
            $this->command->line("   {$tabla}: {$afectados} registros actualizados");
        }

        // Tablas que también tienen sucursal_id
        $tablasSucursal = [
            'ventas', 'compras', 'ajuste_inventarios', 'caja_sesiones',
        ];

        foreach ($tablasSucursal as $tabla) {
            $afectados = DB::table($tabla)
                ->whereNull('tenant_id')
                ->update([
                    'tenant_id'   => $tenantId,
                    'sucursal_id' => $sucursalId,
                ]);
            $this->command->line("   {$tabla}: {$afectados} registros actualizados");
        }

        // Users: asignar tenant_id y sucursal_id
        $afectados = DB::table('users')
            ->whereNull('tenant_id')
            ->update([
                'tenant_id'   => $tenantId,
                'sucursal_id' => $sucursalId,
            ]);
        $this->command->line("   users: {$afectados} registros actualizados");

        // Configuraciones: asignar tenant_id
        $afectados = DB::table('configuraciones')
            ->whereNull('tenant_id')
            ->update(['tenant_id' => $tenantId]);
        $this->command->line("   configuraciones: {$afectados} registros actualizados");

        // 6. Verificación final
        $sinTenant = collect($tablasSolo)->mapWithKeys(fn($t) => [
            $t => DB::table($t)->whereNull('tenant_id')->count()
        ])->filter(fn($c) => $c > 0);

        if ($sinTenant->isEmpty()) {
            $this->command->info('');
            $this->command->info('Backfill completado. Todos los registros tienen tenant_id.');
        } else {
            $this->command->warn('ATENCION: Quedan registros sin tenant_id:');
            $sinTenant->each(fn($c, $t) => $this->command->warn("   {$t}: {$c} registros"));
        }

        $this->command->info('');
        $this->command->info("Tenant piloto listo:");
        $this->command->info("   ID:       {$tenantId}");
        $this->command->info("   Slug:     piloto");
        $this->command->info("   Sucursal: {$sucursalId}");
    }
}
