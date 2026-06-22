<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 2 — Agregar tenant_id (nullable) a todas las tablas de negocio.
 *
 * DECISIÓN: Las columnas se agregan como NULLABLE intencionalmente.
 * Esto garantiza cero downtime y que el sistema actual siga funcionando
 * sin cambios mientras completamos la migración.
 *
 * En Fase 3 (Global Scopes) estas columnas se poblarán con backfill
 * y luego se convertirán a NOT NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        // users: también recibe sucursal_id para saber a qué sucursal pertenece
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('tenants')
                  ->nullOnDelete();
            $table->foreignId('sucursal_id')
                  ->nullable()
                  ->after('tenant_id')
                  ->constrained('sucursales')
                  ->nullOnDelete();
            $table->index(['tenant_id', 'email'], 'idx_tenant_user_email');
        });

        Schema::table('categorias', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('tenants')
                  ->nullOnDelete();
            $table->index('tenant_id', 'idx_categorias_tenant');
        });

        Schema::table('proveedores', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('tenants')
                  ->nullOnDelete();
            $table->index('tenant_id', 'idx_proveedores_tenant');
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('tenants')
                  ->nullOnDelete();
            $table->index(['tenant_id', 'nombre'], 'idx_productos_tenant_nombre');
            $table->index(['tenant_id', 'codigo_barras'], 'idx_productos_tenant_barcode');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('tenants')
                  ->nullOnDelete();
            $table->index(['tenant_id', 'numero_documento'], 'idx_clientes_tenant_doc');
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('tenants')
                  ->nullOnDelete();
            $table->foreignId('sucursal_id')
                  ->nullable()
                  ->after('tenant_id')
                  ->constrained('sucursales')
                  ->nullOnDelete();
            $table->index(['tenant_id', 'created_at'], 'idx_ventas_tenant_fecha');
            $table->index(['tenant_id', 'estado', 'created_at'], 'idx_ventas_tenant_estado');
        });

        Schema::table('venta_detalles', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('tenants')
                  ->nullOnDelete();
            $table->index('tenant_id', 'idx_venta_detalles_tenant');
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('tenants')
                  ->nullOnDelete();
            $table->foreignId('sucursal_id')
                  ->nullable()
                  ->after('tenant_id')
                  ->constrained('sucursales')
                  ->nullOnDelete();
            $table->index('tenant_id', 'idx_compras_tenant');
        });

        Schema::table('compra_detalles', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('tenants')
                  ->nullOnDelete();
            $table->index('tenant_id', 'idx_compra_detalles_tenant');
        });

        Schema::table('ajuste_inventarios', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('tenants')
                  ->nullOnDelete();
            $table->foreignId('sucursal_id')
                  ->nullable()
                  ->after('tenant_id')
                  ->constrained('sucursales')
                  ->nullOnDelete();
            $table->index('tenant_id', 'idx_ajustes_tenant');
        });

        Schema::table('configuraciones', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('tenants')
                  ->nullOnDelete();
            // La clave era unique globalmente; ahora debe ser unique por tenant
            $table->dropUnique(['clave']);
            $table->unique(['tenant_id', 'clave'], 'uq_config_tenant_clave');
        });

        Schema::table('caja_sesiones', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('tenants')
                  ->nullOnDelete();
            $table->foreignId('sucursal_id')
                  ->nullable()
                  ->after('tenant_id')
                  ->constrained('sucursales')
                  ->nullOnDelete();
            $table->index('tenant_id', 'idx_caja_sesiones_tenant');
        });

        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('tenants')
                  ->nullOnDelete();
            $table->index('tenant_id', 'idx_caja_movimientos_tenant');
        });

        Schema::table('auditorias', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('tenants')
                  ->nullOnDelete();
            $table->index(['tenant_id', 'created_at'], 'idx_auditorias_tenant_fecha');
        });
    }

    public function down(): void
    {
        // Revertir en orden inverso para respetar foreign keys
        $tablas = [
            'auditorias'        => ['tenant_id'],
            'caja_movimientos'  => ['tenant_id'],
            'caja_sesiones'     => ['tenant_id', 'sucursal_id'],
            'configuraciones'   => ['tenant_id'],
            'ajuste_inventarios'=> ['tenant_id', 'sucursal_id'],
            'compra_detalles'   => ['tenant_id'],
            'compras'           => ['tenant_id', 'sucursal_id'],
            'venta_detalles'    => ['tenant_id'],
            'ventas'            => ['tenant_id', 'sucursal_id'],
            'clientes'          => ['tenant_id'],
            'productos'         => ['tenant_id'],
            'proveedores'       => ['tenant_id'],
            'categorias'        => ['tenant_id'],
            'users'             => ['tenant_id', 'sucursal_id'],
        ];

        foreach ($tablas as $tabla => $columnas) {
            Schema::table($tabla, function (Blueprint $table) use ($columnas) {
                $table->dropColumn($columnas);
            });
        }

        // Restaurar unique original de configuraciones
        Schema::table('configuraciones', function (Blueprint $table) {
            $table->unique('clave');
        });
    }
};
