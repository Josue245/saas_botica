<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60);
            $table->string('slug', 30)->unique();
            $table->decimal('precio_mensual', 8, 2)->default(0);
            $table->smallInteger('max_usuarios')->default(1);        // -1 = ilimitado
            $table->smallInteger('max_sucursales')->default(1);
            $table->integer('max_productos')->default(100);
            $table->integer('max_ventas_mes')->default(500);
            $table->json('features');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Seed inicial de planes
        DB::table('planes')->insert([
            [
                'nombre'         => 'Free',
                'slug'           => 'free',
                'precio_mensual' => 0,
                'max_usuarios'   => 1,
                'max_sucursales' => 1,
                'max_productos'  => 100,
                'max_ventas_mes' => 500,
                'features'       => json_encode([
                    'exportar_csv'             => false,
                    'facturacion_electronica'  => false,
                    'reportes_avanzados'       => false,
                    'multi_sucursal'           => false,
                    'api_access'               => false,
                ]),
                'activo'         => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'nombre'         => 'Básico',
                'slug'           => 'basico',
                'precio_mensual' => 89,
                'max_usuarios'   => 3,
                'max_sucursales' => 1,
                'max_productos'  => 1000,
                'max_ventas_mes' => 5000,
                'features'       => json_encode([
                    'exportar_csv'             => true,
                    'facturacion_electronica'  => false,
                    'reportes_avanzados'       => false,
                    'multi_sucursal'           => false,
                    'api_access'               => false,
                ]),
                'activo'         => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'nombre'         => 'Pro',
                'slug'           => 'pro',
                'precio_mensual' => 189,
                'max_usuarios'   => 10,
                'max_sucursales' => 3,
                'max_productos'  => 10000,
                'max_ventas_mes' => 50000,
                'features'       => json_encode([
                    'exportar_csv'             => true,
                    'facturacion_electronica'  => true,
                    'reportes_avanzados'       => true,
                    'multi_sucursal'           => true,
                    'api_access'               => false,
                ]),
                'activo'         => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'nombre'         => 'Enterprise',
                'slug'           => 'enterprise',
                'precio_mensual' => 450,
                'max_usuarios'   => -1,
                'max_sucursales' => -1,
                'max_productos'  => -1,
                'max_ventas_mes' => -1,
                'features'       => json_encode([
                    'exportar_csv'             => true,
                    'facturacion_electronica'  => true,
                    'reportes_avanzados'       => true,
                    'multi_sucursal'           => true,
                    'api_access'               => true,
                ]),
                'activo'         => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
