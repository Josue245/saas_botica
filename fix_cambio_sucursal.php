<?php
$path = 'app/Http/Controllers/SucursalController.php';
$c = file_get_contents($path);

$old = "        // Actualizar sucursal del usuario
        auth()->user()->update(['sucursal_id' => \$sucursal->id]);
        app()->instance('sucursal', \$sucursal);

        return back()->with('ok', \"Sucursal activa: {\$sucursal->nombre}\");";

$new = "        // Actualizar sucursal del usuario
        auth()->user()->update(['sucursal_id' => \$sucursal->id]);
        app()->instance('sucursal', \$sucursal);

        // Forzar refresh de la sesión para que ResolveTenant lo detecte
        request()->session()->put('sucursal_id', \$sucursal->id);

        return redirect()->route('sucursales.index')
            ->with('ok', \"Sucursal activa cambiada a: {\$sucursal->nombre}\");";

$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "FIX 6 OK: cambio de sucursal hace redirect\n";
