<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correlativos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->string('tipo', 20);     // boleta, factura, orden_compra, nota_credito
            $table->string('serie', 10);    // B001, F001, OC-
            $table->unsignedInteger('ultimo')->default(0);
            $table->timestamps();

            // Garantiza unicidad por tenant + sucursal + tipo + serie
            $table->unique(['tenant_id', 'sucursal_id', 'tipo', 'serie'], 'uq_correlativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correlativos');
    }
};
