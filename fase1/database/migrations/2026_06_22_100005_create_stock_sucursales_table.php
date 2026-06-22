<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_sucursales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(10);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Un producto tiene un solo registro de stock por sucursal
            $table->unique(['tenant_id', 'sucursal_id', 'producto_id'], 'uq_stock_sucursal');
            $table->index(['tenant_id', 'sucursal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_sucursales');
    }
};
