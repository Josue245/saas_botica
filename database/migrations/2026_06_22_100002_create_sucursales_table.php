<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sucursales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('nombre', 120);
            $table->string('direccion', 200)->nullable();
            $table->char('ubigeo', 6)->nullable();          // código INEI Peru
            $table->string('telefono', 30)->nullable();
            $table->boolean('activo')->default(true);
            $table->string('serie_boleta', 10)->default('B001');
            $table->string('serie_factura', 10)->default('F001');
            $table->timestamps();

            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sucursales');
    }
};
