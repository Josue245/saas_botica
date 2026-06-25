<?php
$path = 'app/Http/Controllers/VentaController.php';
$c = file_get_contents($path);

// Agregar import del modelo
if (strpos($c, 'ComprobanteSunat') === false) {
    $c = str_replace(
        "use App\Models\Venta;",
        "use App\Models\ComprobanteSunat;\nuse App\Models\Venta;",
        $c
    );
}

// Agregar with(comprobanteSunat) al query de ventas
$c = str_replace(
    "->with(['cliente', 'usuario'])",
    "->with(['cliente', 'usuario', 'comprobanteSunat'])",
    $c
);

file_put_contents($path, $c);
echo "FIX 2 OK: VentaController carga comprobanteSunat\n";
