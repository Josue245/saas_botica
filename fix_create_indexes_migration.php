<?php
// Crear migración de índices
$migration = <<<'PHP'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Índices para queries frecuentes del dashboard y sidebar
        Schema::table('productos', function (Blueprint $table) {
            try { $table->index(['tenant_id', 'activo', 'stock'], 'idx_prod_tenant_activo_stock'); } catch (\Throwable $e) {}
            try { $table->index(['tenant_id', 'fecha_vencimiento'], 'idx_prod_vencimiento'); } catch (\Throwable $e) {}
        });

        Schema::table('ventas', function (Blueprint $table) {
            try { $table->index(['tenant_id', 'estado', 'created_at'], 'idx_ventas_tenant_estado_fecha'); } catch (\Throwable $e) {}
        });

        Schema::table('users', function (Blueprint $table) {
            try { $table->index(['tenant_id', 'activo'], 'idx_users_tenant_activo'); } catch (\Throwable $e) {}
        });
    }

    public function down(): void {}
};
PHP;

file_put_contents('database/migrations/2026_06_25_000001_add_performance_indexes.php', $migration);
echo "Migration de indices creada OK\n";
