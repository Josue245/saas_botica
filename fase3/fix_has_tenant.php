<?php

/**
 * fix_has_tenant.php — agrega el trait HasTenant a todos los modelos de negocio.
 * Ejecutar dentro del contenedor: php fix_has_tenant.php
 */

$modelos = [
    'Producto', 'Categoria', 'Proveedor', 'Cliente',
    'Venta', 'VentaDetalle', 'Compra', 'CompraDetalle',
    'AjusteInventario', 'Configuracion', 'CajaSesion',
    'CajaMovimiento', 'Auditoria',
];

foreach ($modelos as $modelo) {
    $path = "app/Models/{$modelo}.php";

    if (!file_exists($path)) {
        echo "SKIP (no existe): {$path}\n";
        continue;
    }

    $contenido = file_get_contents($path);

    if (strpos($contenido, 'HasTenant') !== false) {
        echo "YA TIENE: {$modelo}\n";
        continue;
    }

    // 1. Agregar el import del trait
    $contenido = str_replace(
        "use Illuminate\\Database\\Eloquent\\Model;",
        "use App\\Models\\Concerns\\HasTenant;\nuse Illuminate\\Database\\Eloquent\\Model;",
        $contenido
    );

    // 2. Agregar uso del trait dentro de la clase
    // Detectar si ya usa otros traits (Auditable, HasFactory)
    if (preg_match('/use\s+(Auditable|HasFactory)[^;]*;/', $contenido)) {
        // Agregar HasTenant a la línea de use existente
        $contenido = preg_replace(
            '/(use\s+)(Auditable|HasFactory)/',
            '$1HasTenant, $2',
            $contenido,
            1
        );
    } else {
        // No hay traits aún, agregar después de la apertura de clase
        $contenido = preg_replace(
            '/(class\s+\w+\s+extends\s+Model\s*\{)/',
            "$1\n    use HasTenant;",
            $contenido,
            1
        );
    }

    file_put_contents($path, $contenido);
    echo "OK: {$modelo}\n";
}

echo "\nListo. Todos los modelos tienen HasTenant.\n";
