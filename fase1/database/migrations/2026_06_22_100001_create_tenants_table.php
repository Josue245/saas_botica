<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('slug', 63)->unique();          // subdominio: mibotica.saas.pe
            $table->string('razon_social', 160);
            $table->string('ruc', 11)->nullable()->unique();
            $table->string('email', 120);
            $table->string('telefono', 30)->nullable();
            $table->foreignId('plan_id')->constrained('planes');
            $table->timestamp('plan_expira_at')->nullable();
            $table->enum('estado', ['trial', 'activo', 'suspendido', 'cancelado'])
                  ->default('trial');
            $table->timestamp('trial_expira_at')->nullable();
            $table->json('metadata')->nullable();           // configuraciones extendidas
            $table->timestamps();

            $table->index('slug');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
