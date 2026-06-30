<?php
$path = 'app/Http/Controllers/BillingController.php';
$c = file_get_contents($path);
$old = "return view('billing.index', compact('tenant', 'planes', 'suscripcionActual'));";
$new = "// DEBUG: dump datos
        if (request()->has('debug')) {
            dd(compact('tenant', 'planes', 'suscripcionActual'));
        }
        return view('billing.index', compact('tenant', 'planes', 'suscripcionActual'));";
$c = str_replace($old, $new, $c);
file_put_contents($path, $c);
echo "Debug agregado OK\n";
