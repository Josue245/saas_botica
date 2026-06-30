<?php
$path = 'app/Http/Controllers/SucursalController.php';
$c = file_get_contents($path);

$old = "        \$data = \$request->validate([
            'nombre'    => 'required|string|max:120',
            'direccion' => 'nullable|string|max:200',
            'telefono'  => 'nullable|string|max:30',
            'activo'    => 'boolean',
        ]);

        \$sucursal->update(\$data);";

$new = "        \$data = \$request->validate([
            'nombre'    => 'required|string|max:120',
            'direccion' => 'nullable|string|max:200',
            'telefono'  => 'nullable|string|max:30',
        ]);

        // Checkbox no envía nada si está desmarcado — manejar manualmente
        \$data['activo'] = \$request->has('activo') ? true : false;

        \$sucursal->update(\$data);";

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "FIX 7 OK: estado activo/inactivo corregido\n";
