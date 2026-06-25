<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprobantes_electronicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->enum('tipo', ['boleta', 'factura', 'nota_credito', 'nota_debito']);
            $table->string('serie', 10);
            $table->unsignedInteger('correlativo');
            $table->string('numero', 30);              // B001-00000001
            $table->enum('estado', [
                'pendiente', 'enviando', 'aceptado', 'rechazado', 'observado'
            ])->default('pendiente');
            $table->string('hash', 100)->nullable();
            $table->string('cdr_url', 255)->nullable();
            $table->string('xml_url', 255)->nullable();
            $table->text('xml_contenido')->nullable();  // XML generado (para re-envío)
            $table->text('error_mensaje')->nullable();
            $table->timestamp('enviado_at')->nullable();
            $table->timestamp('aceptado_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'numero'], 'uq_comprobante_numero');
            $table->index(['tenant_id', 'estado'], 'idx_comprobante_estado');
            $table->index(['tenant_id', 'tipo', 'serie'], 'idx_comprobante_serie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobantes_electronicos');
    }
};
