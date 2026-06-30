<?php
$path = 'app/Http/Controllers/FacturacionController.php';
$c = file_get_contents($path);

$old = "        \$tipo = \$venta->tipo_comprobante === 'factura' ? 'factura' : 'boleta';";
$new = "        // Usar tipo del request si viene, sino usar el de la venta
        \$tipoRequest = request()->input('tipo');
        \$tipo = in_array(\$tipoRequest, ['boleta', 'factura']) ? \$tipoRequest : (\$venta->tipo_comprobante === 'factura' ? 'factura' : 'boleta');";

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "FIX 5 OK: tipo desde request\n";
