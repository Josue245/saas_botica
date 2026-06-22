<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('planes');
            $table->enum('estado', ['activa', 'cancelada', 'vencida', 'trial'])
                  ->default('trial');
            $table->timestamp('inicia_at');
            $table->timestamp('expira_at')->nullable();
            $table->decimal('precio_pagado', 8, 2)->default(0);
            $table->char('moneda', 3)->default('PEN');
            $table->string('metodo_pago', 30)->nullable();   // culqi, stripe, transferencia
            $table->string('referencia_pago', 120)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripciones');
    }
};
